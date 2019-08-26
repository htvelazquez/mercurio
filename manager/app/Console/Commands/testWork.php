<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use function GuzzleHttp\json_encode;
use App\Client as ClientModel;
use App\JobsContact;

class testWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Takes a job for the first user active and process it';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Client();
        $xApiKey = env('API_KEY');
        $baseUrl = "http://172.17.0.2";

        // Get Token
        $token = '';

        $url = $baseUrl . '/api/extension/auth';

        // Get random email from client active
        $Client = new ClientModel();

        $candidate = $Client->where('active', 1)->inRandomOrder()->first();

        if ($candidate != null) {
            $this->info('email: ' . $candidate->email);

            $data = [
                'email' => $candidate->email
            ];

            $response = $client->post($url, [
                RequestOptions::JSON => $data,
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'x-api-key'     => $xApiKey
            ]]);

            if ($response->getStatusCode() == 200) {
                $responseJson = json_decode($response->getBody()->getContents());
                $token = $responseJson->token;
                $this->info('Token: ' . $token);

                // GET Job
                $url = $baseUrl . '/api/extension/job';
                $responseGetJob = $client->get($url, [
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $token
                ]]);

                if ($responseGetJob->getStatusCode() == 200) {
                    $responseGetJobJson = json_decode($responseGetJob->getBody()->getContents());
                    $job = $responseGetJobJson->jobs[0];
                    $url = $job->url;
                    $jobs_contacts_id = $job->jobs_contacts_id;
                    $this->info('Job url: ' . $url);
                    $this->info('JobsContactsId: ' . $jobs_contacts_id);

                    $JobsContactModel = new JobsContact();
                    $JobsContactModel = $JobsContactModel->where('id', $jobs_contacts_id)->first();

                    $this->info('Contact id: ' . $JobsContactModel->contact_id);

                    $dataPost = [
                        'status' => 'done',
                        'result' => [
                            'contactId' => $JobsContactModel->contact_id,
                            'priority' => '1',
                            'lId' => '8119891' . $JobsContactModel->contact_id,
                            'title' => 'Senior Software Developer',
                            'name' => 'Bruno Paglialunga',
                            'firstName' => 'Bruno',
                            'lastName' => 'Paglialunga',
                            'fullExperience' => [
                                0 => [
                                    'id' => null,
                                    'name' => 'Braintly | Software Boutique',
                                    'jobTitle' => 'Senior Software Developer',
                                    'companyName' => 'Braintly | Software Boutique',
                                    'companyLink' => null,
                                    'from' => 'Feb 2017',
                                    'to' => null,
                                ],
                                1 => [
                                    'id' => '3174252',
                                    'name' => 'SONTRA',
                                    'jobTitle' => 'PHP Senior Developer',
                                    'companyName' => 'SONTRA',
                                    'companyLink' => 'https://www.linkedin.com/sales/company/3174252',
                                    'from' => 'Aug 2015',
                                    'to' => 'Dec 2016',
                                ]
                            ],
                            'location' => 'Argentina',
                            'email' => 'bruno@braintly.com',
                            'twitter' => 'coione',
                            'phone' => '1555058234',
                            'skills' => [
                                0 => [
                                    'id' => 'programming',
                                    'endorsements' => null,
                                ]
                            ],
                            'languages' => [
                                0 => 'inglés',
                                1 => 'español',
                                2 => 'papei',
                            ],
                            'education' => [
                                0 => [
                                    'endedOn' => ['year' => 2014],
                                    'fieldsOfStudy' => [0 => 'Informática'],
                                    'school' => 'urn:li:fs_salesSchool:10161',
                                    'schoolName' => 'ESCUNS',
                                    'startedOn' => ['year' => 2006],
                                    'eduId' => 283274290,
                                ],
                                1 => [
                                    'endedOn' => ['year' => 2015],
                                    'fieldsOfStudy' => [0 => 'eletrónica'],
                                    'school' => 'urn:li:fs_salesSchool:10161',
                                    'degree' => 'Robótica',
                                    'schoolName' => 'Universidad Nacional del Sur',
                                    'startedOn' => ['year' => null],
                                    'eduId' => 283274290,
                                ],
                            ],
                            'totalConnections' => 341,
                        ]
                    ];

                    $rand = rand(0,1);
                    $experience = [
                        0 => [
                            'id' => null,
                            'name' => 'Braintly | Software Boutique',
                            'jobTitle' => 'Senior Software Developer',
                            'companyName' => 'Braintly | Software Boutique',
                            'companyLink' => null,
                            'from' => 'Feb 2017',
                            'to' => null,
                        ],
                        1 => [
                            'id' => null,
                            'name' => 'Braintly | Software Boutique',
                            'jobTitle' => 'Junior Software Architect',
                            'companyName' => 'Braintly | Software Boutique',
                            'companyLink' => null,
                            'from' => 'Feb 2017',
                            'to' => null,
                        ]
                    ];

                    $dataPost['result']['fullExperience'][0] = $experience[$rand];


                    // POST Job
                    $url = $baseUrl . '/api/extension/job/' . $jobs_contacts_id;

                    $responsePostJob = $client->post($url, [
                        RequestOptions::JSON => $dataPost,
                        'headers' => [
                            'Content-Type'  => 'application/json',
                            'Authorization' => 'Bearer ' . $token
                    ]]);

                    if ($responsePostJob->getStatusCode() == 200) {
                        $responsePostJobJson = json_decode($responsePostJob->getBody()->getContents());
                        $this->info('Done!!!');
                    }
                }
            }
        }
    }
}
