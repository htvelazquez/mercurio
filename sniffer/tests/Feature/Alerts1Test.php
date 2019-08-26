<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Alerts1Test extends TestCase
{
    public function testExample()
    {
        $data = [
            'contactId' => '3',
            'priority' => '2',
            'lId' => 'ACoAABAYVZwBi0NQTaj2TJFgFPKU4XcutjEpxkI',
            //'salesforceId' => 'asdf876ajs',
            'title' => 'Javascript Developer',
            'name' => 'Juan Corneta',
            'firstName' => 'Juan',
            'lastName' => 'Corneta',
            'fullExperience' => [
              0 => [
                'id' => null,
                'jobTitle' => 'Frontend Engineer',
                'companyName' => 'Quantic Mind',
                'companyLink' => null,
                'from' => 'Feb 2018',
                'to' => null,
              ],
              1 => [
                'id' => '2228932',
                'jobTitle' => 'Software Engineer',
                'companyName' => 'Quantic Mind',
                'companyLink' => 'https://www.linkedin.com/sales/company/2228932/people',
                'from' => 'Mar 2015',
                'to' => 'Feb 2018',
              ]
            ],
            'location' => 'Argentina',
            'email' => 'juancor@gmail.com',
            'twitter' => 'juancor',
            'phone' => '1555056543',
            'skills' => [
              0 => [
                'id' => 'programming',
                'endorsements' => NULL,
              ]
            ],
            'languages' => [
              0 => 'inglés',
              1 => 'español',
            ],
            'education' => [
              0 => [
                'fieldsOfStudy' => [0 => 'Informática'],
                'school' => 'urn:li:fs_salesSchool:10161',
                'schoolName' => 'Universidad de Buenos Aires / UBA',
                'startedOn' => ['year' => 2004],
                'endedOn' => ['year' => 2013],
                'eduId' => 338240,
              ]
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



        $data['fullExperience'][0] = [
            'id' => '2228932',
            'jobTitle' => 'Frontend Engineer',
            'companyName' => 'Quantic Mind',
            'companyLink' => 'https://www.linkedin.com/sales/company/2228932/people',
            'from' => 'Feb 2018',
            'to' => null,
        ];

        $response1 = $this->withHeaders([
            'x-api-key' => 'ec533d6f-a536-45dc-9cae-e72fcddfd59e',
        ])->json('POST', '/api/snapshot', $data);

        $response1
            ->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
    }
}
