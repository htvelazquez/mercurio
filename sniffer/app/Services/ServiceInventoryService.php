<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\Request;
use \Illuminate\Support\Facades\DB;
use App\Contact;

class ServiceInventoryService extends Service
{
    protected $_ApiKey = null;
    protected $_ApiURL = null;

    protected function __construct()
    {
        parent::__construct();
        $this->_ApiKey = env('API_SERVICESINVENTORY_KEY');
        $this->_ApiURL = env('API_SERVICESINVENTORY_URL');
    }

    /**
     * @return ServiceInventoryService
     */
    public static function getInstance()
    {
        return parent::build(self::class);
    }

    public function getLinkedInCompanyPage( $lid )
    {
        if (empty($lid)) return false;

        try {
            $client = new Client();

            $response = $client->get($this->_ApiURL . '/salesforce/linkedinCompanyPages/' .$lid, [
                'headers' => ['x-api-key' => $this->_ApiKey],
            ]);

            $response = json_decode($response->getBody());

            if (!empty($response) && count($response->linkedinCompanyPages) > 0) {
                return $response->linkedinCompanyPages[0];
            }

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $code =$e->getCode();
            if ($code == 404)
                return false;

        } catch (\Exception $e) {
            throw new \Exception("Services Inventory failed", 1);
        }
    }

    public function getContactFromLinkedInID( $lid )
    {
        return $this->getContact($lid, 'linkedin');
    }

    public function getContactFromSafeID( $safeid )
    {
        return $this->getContact($safeid, 'salesforce');
    }

    protected function getContact( $id, $type )
    {
        if (empty($id)) return false;

       try {
            $client = new Client();

            $response = $client->get($this->_ApiURL . '/salesforce/contacts/' .$id.'?type='.$type, [
                'headers' => ['x-api-key' => $this->_ApiKey],
            ]);

            $response = json_decode($response->getBody());

            if (!empty($response) && count($response->contacts) > 0) {
                return $response->contacts[0];
            }

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $code =$e->getCode();
            if ($code == 404)
                return false;

        } catch (\Exception $e) {
            throw new \Exception("Services Inventory failed", 1);
        }
    }

    public function getAccount( $safeId )
    {
        if (empty($safeId)) return false;

        try {
            $client = new Client();

            $response = $client->get($this->_ApiURL . '/salesforce/accounts/' .$safeId, [
                'headers' => ['x-api-key' => $this->_ApiKey],
            ]);

            $response = json_decode($response->getBody());

            if (!empty($response) && count($response->accounts) > 0)
                return $response->accounts[0];


        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $code =$e->getCode();
            if ($code == 404)
                return false;

        } catch (\Exception $e) {
            throw new \Exception("Services Inventory failed", 1);
        }
    }

    public function getAccountByName( $safeId )
    {
        if (empty($safeId)) return false;

        try {
            $client = new Client();

            $response = $client->get($this->_ApiURL . '/salesforce/accounts/' .$safeId, [
                'headers' => ['x-api-key' => $this->_ApiKey],
            ]);

            $response = json_decode($response->getBody());

            if (!empty($response) && count($response->accounts) > 0)
                return $response->accounts[0];


        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $code =$e->getCode();
            if ($code == 404)
                return false;

        } catch (\Exception $e) {
            throw new \Exception("Services Inventory failed", 1);
        }
    }

    protected function normalizeString(string $string) {
        $string = strtolower($string);

        $string = preg_replace("/[^a-z0-9_\s-+]/", "", $string);

        $string = preg_replace("/[\s-]+/", " ", $string);

        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }

}
