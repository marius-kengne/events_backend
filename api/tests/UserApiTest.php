<?php

namespace App\Tests;

use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserApiTest extends WebTestCase
{
    private $faker;
    private $testEmail;
    private $testPassword;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->faker = Factory::create();
        $this->testEmail = $this->faker->unique()->safeEmail();
        $this->testPassword = 'Test@123';
    }

    public function testRegisterUser(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => $this->testEmail,
            'password' => $this->testPassword,
            'roles' => ['ROLE_USER']
        ]));

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testLoginUser(): void
    {
        $client = static::createClient();

        // On enregistre d'abord un utilisateur généré
        $client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => $this->testEmail,
            'password' => $this->testPassword,
            'roles' => ['ROLE_USER']
        ]));

        $this->assertResponseStatusCodeSame(201);

        // Ensuite on le connecte
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'username' => $this->testEmail,
            'password' => $this->testPassword
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
    }
}
