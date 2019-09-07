<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSnapshot;
use App\Contact;
use App\Snapshot;
use App\Company;
use App\SnapshotExperience;
use App\Location;
use App\SnapshotMetadata;
use App\SnapshotPhone;
use App\SnapshotEmail;
use App\School;
use App\StudyField;
use App\SnapshotEducation;
use App\Skill;
use App\SnapshotSkill;
use App\Language;
use App\SnapshotLanguage;
use App\Jobs\ProcessSnapshot;

class SnapshotsController extends Controller
{
    public function store(StoreSnapshot $request) {
        $contactData = [
            'linkedin_id'   => $request->id
        ];

        $contact = Contact::where('linkedin_id', $request->id)->first();

        if (empty($contact)) {
            $contact = Contact::create($contactData);
        }

        $snapshotData = [
            'contact_id'=> $contact->id,
            // 'priority'  => $request->priority,
            'priority'  => 1,
            'status'    => 0
        ];

        $snapshot = Snapshot::create($snapshotData);

        if (!empty($request->fullExperience)) {
            foreach($request->fullExperience as $experience) {
                $companyName = $this->normalizeString($experience['companyName']);
                $companyData = [
                    'name'  => $companyName,
                    'linkedin_id' => empty($experience['id'])? null : $experience['id'],
                    'link'  => empty($experience['id'])? null : $experience['companyLink'],
                    'label' => $experience['companyName']
                ];

                if (!empty($experience['id'])) {
                    $company = Company::where('linkedin_id', $experience['id'])->first();

                    if (empty($company)) {
                        $company = Company::create($companyData);
                    }
                } else {
                    $company = Company::where('name', '=', $companyName)->first();

                    if (empty($company)) {
                        $company = Company::create($companyData);
                    }
                }

                if (preg_match('/^\d{4}$/', $experience['from'])) $experience['from'] = "Jan {$experience['from']}";
                if (preg_match('/^\d{4}$/', $experience['to'])) $experience['to'] = "Jan {$experience['to']}";

                $experienceData = [
                    'snapshot_id'   => $snapshot->id,
                    'jobTitle'      => $experience['jobTitle'],
                    'from'          => date('Y-m-d', strtotime($experience['from'])),
                    'to'            => !empty($experience['to']) ? date('Y-m-d', strtotime($experience['to'])) : null,
                    'company_id'    => $company->id,
                ];

                $snapshotExperience = SnapshotExperience::create($experienceData);
            }
        }

        if (!empty($request->location)) {
            $locationName = $request->location;
            if (!empty($request->fullExperience) && empty($request->fullExperience[0]['to']) && !empty($request->fullExperience[0]['location'])) {
                $locationName = $request->fullExperience[0]['location'];
            }

            $name = $this->normalizeString($locationName);
            $locationData = [
                'name'  => $name,
                'label' => $locationName
            ];

            $location = Location::firstOrCreate($locationData);

            $snapshotMetadataData = [
                'snapshot_id'   => $snapshot->id,
                'name'          => $request->name,
                'firstName'     => $request->firstName,
                'lastName'      => $request->lastName,
                'location_id'   => $location->id,
                'summary'       => strlen($request->summary) > 255 ? substr($request->summary,0,252)."..." : $request->summary,
                'totalConnections' => (int) $request->totalConnections,
                'twitter'       => $request->twitter,
                'birthday'      => $request->birthday
            ];

            $snapshotMetadata = SnapshotMetadata::create($snapshotMetadataData);
        }

        if (!empty($request->phone)) {
            $snapshotPhoneData = [
                'snapshot_id' => $snapshot->id,
                'phone' => $request->phone,
            ];

            $snapshotPhone = SnapshotPhone::create($snapshotPhoneData);
        }

        if (!empty($request->email)) {
            $snapshotEmailData = [
                'snapshot_id' => $snapshot->id,
                'email' => $request->email,
            ];

            $snapshotEmail = SnapshotEmail::create($snapshotEmailData);
        }

        if (!empty($request->education)) {
            foreach($request->education as $education) {
                if (!empty($education['fieldsOfStudy'])) {
                    $fieldLabel = '';
                    foreach($education['fieldsOfStudy'] as $field) {
                        $fieldLabel .= $field;
                    }

                    $fieldName = $this->normalizeString($fieldLabel);

                    $studyFieldData = [
                        'name'  => $fieldName,
                        'label' => $fieldLabel
                    ];

                    $studyField = StudyField::firstOrCreate($studyFieldData);
                }

                if (!empty($education['schoolName'])) {
                    $name = $this->normalizeString($education['schoolName']);
                    $schoolData = [
                        'name'  => $name,
                        'label' => $education['schoolName'],
                        'linkedin_id' => $education['eduId'],
                    ];

                    $school = School::firstOrCreate($schoolData);
                }

                $snapshotEducationData = [
                    'snapshot_id'   => $snapshot->id,
                    'school_id'     => !empty($school) ? $school->id : null,
                    'study_field_id'=> !empty($studyField) ? $studyField->id : null,
                    'degree'        => !empty($education['degree']) ? $education['degree'] : null,
                    'from'          => (!empty($education['startedOn']) && !empty($education['startedOn']['year'])) ? $education['startedOn']['year'] . '-01-01 00:00:00' : null,
                    'to'            => (!empty($education['endedOn']) && !empty($education['endedOn']['year'])) ? $education['endedOn']['year'] . '-01-01 00:00:00' : null,
                ];
                $snapshotEducation = SnapshotEducation::create($snapshotEducationData);
            }
        }

        if (!empty($request->skills)) {
            foreach($request->skills as $skillItem) {
                $skillData = [
                    'name' => $skillItem['id']
                ];

                $skill = Skill::firstOrCreate($skillData);

                $snapshotSkillData = [
                    'snapshot_id' => $snapshot->id,
                    'skill_id' => $skill->id,
                ];

                $snapshotSkill = SnapshotSkill::create($snapshotSkillData);
            }
        }

        if (!empty($request->languages)) {
            foreach($request->languages as $languageItem) {
                $languageData = [
                    'name' => $languageItem
                ];

                $language = Language::firstOrCreate($languageData);

                $snapshotLanguageData = [
                    'snapshot_id' => $snapshot->id,
                    'language_id' => $language->id,
                ];

                $snapshotLanguage = SnapshotLanguage::create($snapshotLanguageData);
            }
        }

        // ProcessSnapshot::dispatch($contact);

        return response()->json([
            'success' => true,
            Snapshot::with(['contact','email','phone'])->find($snapshot->id),
            SnapshotEducation::with(['school','studyField'])->where('snapshot_id', $snapshot->id)->get(),
            SnapshotExperience::with(['company'])->where('snapshot_id', $snapshot->id)->get(),
            SnapshotLanguage::with(['language'])->where('snapshot_id', $snapshot->id)->get(),
            SnapshotMetadata::with(['location'])->where('snapshot_id', $snapshot->id)->get(),
            SnapshotSkill::with(['skill'])->where('snapshot_id', $snapshot->id)->get(),
        ], 201);
    }

    protected function normalizeString(string $string) {
        $string = strtolower($string);

        $string = preg_replace("/[^a-z0-9_\s-+]/", "", $string);

        $string = preg_replace("/[\s-]+/", " ", $string);

        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }

}
