<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScrapingTest extends TestCase
{
    private $token = '';

    public function testAuth()
    {
        // Get Token
        $data = [
            'email' => 'brunopaglialunga@gmail.com'
        ];

        $xApiKey = env('API_KEY');

        $responseToken = $this->withHeaders([
            'Content-Type' => 'application/json',
            'x-api-key' => $xApiKey
        ])->json('POST', '/api/extension/auth', $data);

        $responseToken->assertStatus(200);

        $responseToken->assertJson([
            'success' => true,
        ]);

        $this->token = $responseToken->baseResponse->original['token'];
        
        // GET Job
        $responseGetJob = $this->withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ])->json('GET', '/api/extension/job');

        $responseGetJob->assertJson([
            'success' => true,
        ]);

        $responseGetJob->assertStatus(200);
        
        $url = $responseGetJob->baseResponse->original['jobs'][0]->url;
        $jobs_contacts_id = $responseGetJob->baseResponse->original['jobs'][0]->jobs_contacts_id;

        $dataPost = [
            'status' => 'done',
            'result' => [
                'contactId' => '2',
                'priority' => '1',
                'lId' => '81198919',
                //'salesforceId' => 'asdf876asd',
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

        // POST Job
        $responsePostJob = $this->withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ])->json('POST', '/api/extension/job/' . $jobs_contacts_id, $dataPost);
        
        $responsePostJob->assertJson([
            'success' => true,
        ]);

        $responsePostJob->assertStatus(200);

        // POST New Job
        $dataPost['result']['fullExperience'][0]['jobTitle'] = 'Junior Software Architect';

        $responsePostNewJob = $this->withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ])->json('POST', '/api/extension/job/' . $jobs_contacts_id, $dataPost);
        
        $responsePostNewJob->assertJson([
            'success' => true,
        ]);

        $responsePostNewJob->assertStatus(200);
    }
}
