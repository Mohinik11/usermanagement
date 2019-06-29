<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UsersControllerTest extends WebTestCase 
{
    /**
     * Create a client with a default Authorization header.
     *
     * @param string $username
     * @param string $password
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function createAuthenticatedClient($username = 'superadmin', $password = 'password')
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(
                [
                    'username' => $username,
                    'password' => $password,
                ]
            )
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $client = static::createClient();
        $client->setServerParameter(
            'HTTP_Authorization', 
            sprintf('Bearer %s', $data['token'])
        );

        return $client;
    }
    public function testPostUsersFailure()
    {
        $client = $this->createAuthenticatedClient();
        $data = '{
            "wrongFieldName": "User 001",
            "city": "Test City"
        }';
        $client->request(
            'POST', 
            '/users', 
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $data
        );
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testPostUsersSuccess()
    {
        $client = $this->createAuthenticatedClient();
        $uniqueString = strtotime(date("Y-m-d H:i:s"));
        $data = [
            "name" => "user 001 $uniqueString",
            "city" => "test city"
        ];
        $client->request(
            'POST', 
            '/users', 
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        return json_decode($client->getResponse()->getContent());
    }

    public function testGetUsersSuccess()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/users');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @depends testPostUsersSuccess
     */
    public function testGetUserByIdSuccess($user)
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', "/users/$user->id");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @depends testPostUsersSuccess
     */
    public function testPutUsersSuccess($user)
    {
        $client = $this->createAuthenticatedClient();
        $uniqueString = strtotime(date("Y-m-d H:i:s"));
        $data = [
            "name" => "user 001 $uniqueString",
            "city" => "test city"
        ];
        $client->request(
            'PUT', 
            "/users/$user->id", 
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @depends testPostUsersSuccess
     */
    public function testDeleteUsersSuccess($user)
    {
        $client = $this->createAuthenticatedClient();
        $data = '{
            "name": "User 00111",
            "city": "test city"
        }';
        $client->request(
            'DELETE', 
            "/users/$user->id", 
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}

