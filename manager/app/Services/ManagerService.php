<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\Request;
use GuzzleHttp\RequestOptions;
use \Illuminate\Support\Facades\DB;
use \Omniphx\Forrest\Providers\Laravel\Facades\Forrest;

class ManagerService extends Service
{
    protected $_ApiURL = null;
    protected $updateSalesforce;

    protected $_Api_Sniffer_URL = null;
    protected $_Api_Sniffer_KEY = null;

    protected function __construct()
    {
        parent::__construct();
        $this->updateManager = env('UPDATE_MANAGER');
        $this->updateSalesforce = env('UPDATE_SALESFORCE');

        $this->_ApiURL = env('API_MANAGER_URL');

        $this->_ApiSnifferURL = env('API_SNIFFER_URL');
        $this->_ApiSnifferKEY = env('API_SNIFFER_KEY');
    }

    /**
     * @return ManagerService
     */
    public static function getInstance()
    {
        return parent::build(self::class);
    }

    public function sendJob($externalId, $priority, $data)
    {
        if (!empty($externalId) && !empty($data)) {
            $client = new Client();

            if ($this->updateManager) {
                // Old Manager project
                $uri = '/scraping/newjob';
                $url = $this->_ApiURL . $uri;

                $response = $client->post($url, [
                    RequestOptions::JSON => $data,
                    'headers' => [
                        'Content-Type' => 'application/json',
                ]]);
            }

            // Sniffer
            $data['salesforceId'] = $externalId;
            $data['priority'] = $priority;
            $responseSniffer = $client->post($this->_ApiSnifferURL . '/api/snapshot', [
                RequestOptions::JSON => $data,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->_ApiSnifferKEY,
                ]
            ]);

            return $responseSniffer->getBody()->getContents();
        }
    }
}
