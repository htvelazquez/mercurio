<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatusAlert;
use Illuminate\Http\Request;
use App\Alert;
use Illuminate\Support\Facades\Storage;
use App\Services\ServiceInventoryService;
use App\Services\SalesforceService;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse as StreamedResponse;

class AlertsController extends Controller
{
    public function ping() {
        $sql = "SELECT
            (
                SELECT count(*)
                FROM snapshots
                WHERE DATEDIFF(CURDATE(), Date(updated_at)) < 1
            ) as 'snapshotsToday',
            (
                SELECT count(*)
                FROM snapshots
                WHERE DATEDIFF(CURDATE(), Date(updated_at)) < 2
            ) as 'snapshotsLast24H',
            (
                SELECT count(*)
                FROM snapshots
                WHERE DATEDIFF(CURDATE(), Date(updated_at)) < 8
            ) as 'snapshotsLast7D',
            (
                SELECT count(*)
                FROM alerts
                WHERE checked_by_solver = 1
                AND DATEDIFF(CURDATE(), Date(updated_at)) < 1
                AND `case` not in (5,11,12,13)
            ) as 'alertsTodayAuto',
            (
                SELECT count(*)
                FROM alerts
                WHERE checked_by_solver = 1
                AND DATEDIFF(CURDATE(), Date(updated_at)) < 2
                AND `case` not in (5,11,12,13)
            ) as 'alertsLast24HAuto',
            (
                SELECT count(*)
                FROM alerts
                WHERE checked_by_solver = 1
                AND DATEDIFF(CURDATE(), Date(updated_at)) < 8
                AND `case` not in (5,11,12,13)
            ) as 'alertsLast7DAuto',
            (
                SELECT count(*)
                FROM alerts
                WHERE checked_by_solver = 1
                AND DATEDIFF(CURDATE(), Date(updated_at)) < 1
                AND `case` in (5,11,12,13)
            ) as 'alertsTodayManual',
            (
                SELECT count(*)
                FROM alerts
                WHERE checked_by_solver = 1
                AND DATEDIFF(CURDATE(), Date(updated_at)) < 2
                AND `case` in (5,11,12,13)
            ) as 'alertsLast24HManual',
            (
                SELECT count(*)
                FROM alerts
                WHERE checked_by_solver = 1
                AND DATEDIFF(CURDATE(), Date(updated_at)) < 8
                AND `case` in (5,11,12,13)
            ) as 'alertsLast7DManual';";

        $data = DB::select($sql);

        $sqlUnsolved = "SELECT snapshots.priority,
        count(snapshots.priority) as count
        FROM alerts
        INNER JOIN snapshots
        ON (alerts.after_snapshot_id = snapshots.id)
        WHERE checked_by_solver = 1
        AND alerts.status = 0
        GROUP BY snapshots.priority
        ORDER BY snapshots.priority DESC;";

        $dataUnsolved = DB::select($sqlUnsolved);

        $responseData = [
            'snapshotsToday' => $data[0]->snapshotsToday,
            'snapshotsLast24H' => $data[0]->snapshotsLast24H,
            'snapshotsLast7Days' => $data[0]->snapshotsLast7D,
            'alertsTodayAuto' => $data[0]->alertsTodayAuto,
            'alertsLast24HAuto' => $data[0]->alertsLast24HAuto,
            'alertsLast7DaysAuto' => $data[0]->alertsLast7DAuto,
            'alertsTodayManual' => $data[0]->alertsTodayManual,
            'alertsLast24HManual' => $data[0]->alertsLast24HManual,
            'alertsLast7DaysManual' => $data[0]->alertsLast7DManual,
            'alertsUnsolved' => $dataUnsolved,
        ];

        return response()->json([
            'success' => true,
            'data' => $responseData
        ], 200);
    }

    public function listGet(Request $request) {
        $status = $request->input('status');
        $checked_by_solver = $request->input('checked_by_solver');
        $priority = $request->input('priority');
        $start = $request->input('start');
        $end = $request->input('end');
        $page = $request->input('page');

        $temp = Alert::with(['after_metadata', 'email', 'before_experience', 'after_experience', 'snapshot' => function ($query) use ($priority) {
            if (isset($priority)) {
                $query->where('priority', $priority);
            }
        }]);

        if (isset($status)) {
            $temp->where('status', $status);
        }

        if (isset($checked_by_solver)) {
            $temp->where('checked_by_solver', $checked_by_solver);
        }

        if (!empty($start)) {
            $temp->where('created_at', '>=', $start . ' 00:00:00');
        }

        if (!empty($end)) {
            $temp->where('created_at', '<=', $end . ' 23:59:59');
        }

        $temp->orderBy('created_at', 'asc');

        $data = $temp->paginate(20);

        $data->appends([
            'status'            => $status,
            'priority'          => $priority,
            'start'             => $start,
            'end'               => $end,
            'page'              => $page,
            'checked_by_solver' => $checked_by_solver
        ]);

        return response()->json([
            'success' => true,
            'status' => $status,
            'data' => $data
        ], 200);
    }

    public function listDownload(Request $request) {
        ini_set('max_execution_time', 300);

        $status = $request->input('status');
        $checked_by_solver = $request->input('checked_by_solver');
        $priority = $request->input('priority');
        $start = $request->input('start');
        $end = $request->input('end');

        $conditions = array();

        if (isset($status)) {
            $conditions[] = ['status', $status];
        }

        if (isset($checked_by_solver)) {
            $conditions[] = ['checked_by_solver', $checked_by_solver];
        }

        if (!empty($start)) {
            $conditions[] = ['created_at', '>=', $start . ' 00:00:00'];
        }

        if (!empty($end)) {
            $conditions[] = ['created_at', '<=', $end . ' 23:59:59'];
        }

        $headers = array(
            'Content-Type'        => 'text/csv',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Disposition' => 'attachment; filename=sniffer.csv',
            'Expires'             => '0',
            'Pragma'              => 'public',
        );

        $response = new StreamedResponse(function() use($priority,$conditions){ // use($data)

            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'AlertID','Done?','Contact SafeId','Linkedin #','Name','Location','Priority','Generated'
            ]);

            Alert::with(['after_metadata', 'email', 'snapshot' => function ($query) use ($priority) {
                if (isset($priority)) {
                    $query->where('priority', $priority);
                }
            }])->where($conditions)->orderBy('created_at', 'asc')->chunk(500, function($data) use($handle) {

                foreach ($data as $line) {

                    $dataCsv = [
                        $line->id,
                        $line->status,
                        $line->snapshot->contact->salesforce_id,
                        $line->snapshot->contact->external_id,
                        $line->after_metadata->name,
                        $line->after_metadata->location->label,
                        $line->snapshot->priority,
                        $line->created_at
                    ];

                    fputcsv($handle, $dataCsv);
                }

            });

            fclose($handle);
        }, 200, $headers);

        return $response->send();
    }

    public function update(StatusAlert $request, $id) {
        $Alert = Alert::find($id);
        $status = $request->input('status');

        if (empty($Alert)){
            return response()->json([
                'success' => false,
                'error' => 'invalid alert id'
            ], 422);
        }

        $Alert->update(['status' => $status]);

        if ($status === 1 && !empty($Alert->snapshot->contact->salesforce_id)){
            $contact = new \stdclass();
            $contact->Grabber_detected__c = 0;
            $contact->Last_Grabber_update__c = gmdate("Y-m-d\TH:i:s\Z",date(time()));

            SalesforceService::getInstance()->pushContact($contact, $Alert->snapshot->contact->salesforce_id);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $id,
                'status' => $Alert->status
            ]
        ], 200);
    }

    public function bulkSolve(Request $request) {
        try {
            $alertIds = explode(',',json_decode($request->getContent(), true)['ids']);
            if (empty($alertIds[0])) throw new \Exception("Error Processing Request", 1);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'invalid alerts id'
            ], 422);
        }

        Alert::whereIn('id',$alertIds)->update(['status' => 1]);

        $alerts = Alert::find($alertIds);

        if (!empty($alerts)) {
            foreach ($alerts as $alert) {
                if (!empty($alert->snapshot->contact->salesforce_id)){
                    $contact = new \stdclass();
                    $contact->Grabber_detected__c = 0;
                    $contact->Last_Grabber_update__c = gmdate("Y-m-d\TH:i:s\Z",date(time()));

                    SalesforceService::getInstance()->pushContact($contact, $alert->snapshot->contact->salesforce_id);
                }
            }
        }

        return response()->json([ 'success' => true ], 200);
    }

    public function getCompanyPage($linkedinId) {
        $companyData = ServiceInventoryService::getInstance()->getLinkedInCompanyPage( $linkedinId );

        return response()->json([
            'success' => true,
            'data' => $companyData
        ], 200);
    }
}
