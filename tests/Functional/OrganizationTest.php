<?php

namespace Tests\Functional;

class OrganizationTest extends BaseTestCase
{
    /**
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testPostOrganizationWithoutData()
    {
        // Task Sample Json String
        $response = $this->runApp("POST", '/organizations');

        $responseBody = (string)$response->getBody();
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($responseBody);
        $res_array = (array)json_decode($responseBody);
        $this->assertArrayHasKey('status', $res_array);
    }

    /**
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testPostOrganizationWithIncorrectData()
    {
        // Data First Key is or_name not org_name !!

        $testJsonPost = '{"or_name":"Paradise Island","daughters":[{"org_name":"Banana tree","daughters":[{"org_name":"Yellow Banana"},{"org_name":"Brown Banana"},{"org_name":"Black Banana"}]},{"org_name":"Big banana tree","daughters":[{"org_name":"Yellow Banana"},{"org_name":"Brown Banana"},{"org_name":"Green Banana"},{"org_name":"Black Banana","daughters":[{"org_name":"Phoneutria Spider"}]}]}]}';
        $requestData = json_decode($testJsonPost);
        // Task Sample Json String
        $response = $this->runApp("POST", '/organizations', $requestData);

        $responseBody = (string)$response->getBody();
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($responseBody);
        $res_array = (array)json_decode($responseBody);
        $this->assertArrayHasKey('status', $res_array);
    }

    /**
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testPostOrganizationWithCorrectData()
    {
        // Task Sample Json String
        $testJsonPost = '{"org_name":"Paradise Island","daughters":[{"org_name":"Banana tree","daughters":[{"org_name":"Yellow Banana"},{"org_name":"Brown Banana"},{"org_name":"Black Banana"}]},{"org_name":"Big banana tree","daughters":[{"org_name":"Yellow Banana"},{"org_name":"Brown Banana"},{"org_name":"Green Banana"},{"org_name":"Black Banana","daughters":[{"org_name":"Phoneutria Spider"}]}]}]}';
        $requestData = json_decode($testJsonPost,true);
        $response = $this->runApp("POST", '/organizations', $requestData);

        $responseBody = (string)$response->getBody();
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJson($responseBody);

        $res_array = (array)json_decode($responseBody);
        $this->assertArrayHasKey('createdOrganizations', $res_array);
        $this->assertArrayHasKey('createdRelations', $res_array);
    }

    /**
     * Test that the organizations route won't accept a get request without search parameter
     *
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testGetOrganizationWithSearchParameter()
    {
        $response = $this->runApp('GET', '/organizations/Black%20Banana');
        $this->assertEquals(200, $response->getStatusCode());
        $responseBody = (string)$response->getBody();

        $this->assertJson($responseBody);

        $res_array = (array)json_decode($responseBody);
        $this->assertArrayHasKey('data', $res_array);
        $this->assertArrayHasKey('links', $res_array);
    }

    /**
     * Test that the organizations route won't accept a get request without search parameter
     *
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function testGetOrganizationNotAllowed()
    {
        $response = $this->runApp('GET', '/organizations');
        $this->assertEquals(405, $response->getStatusCode());
        $this->assertContains('Method not allowed', (string)$response->getBody());
    }


}