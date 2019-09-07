<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AlertsTest extends TestCase
{
    public function testExample()
    {
        $data = [
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
        ];

        $response = $this->withHeaders([
            'x-api-key' => 'ec533d6f-a536-45dc-9cae-e72fcddfd59e',
        ])->json('POST', '/api/snapshot', $data);
        
        $response
            ->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);

        /* New position in same company */
        $data['fullExperience'][0]['jobTitle'] = 'Technical leader';

        $response1 = $this->withHeaders([
            'x-api-key' => 'ec533d6f-a536-45dc-9cae-e72fcddfd59e',
        ])->json('POST', '/api/snapshot', $data);

        $response1
            ->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);

        /* New job */
        $data['fullExperience'][0]['to'] = 'Aug 2018';

        $data['fullExperience'][] = [
            'id' => '23455',
            'jobTitle' => 'Ministro',
            'companyName' => 'Ministerio de Modernización de la República Argentina',
            'companyLink' => 'https://www.linkedin.com/company/ministerio-de-modernizaci%C3%B3n-de-la-rep%C3%BAblica-argentina/',
            'from' => 'Sep 2018',
            'to' => null,
        ];

        $response2 = $this->withHeaders([
            'x-api-key' => 'ec533d6f-a536-45dc-9cae-e72fcddfd59e',
        ])->json('POST', '/api/snapshot', $data);
        
        $response2
            ->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
                
    }
}
