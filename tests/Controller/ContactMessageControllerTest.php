<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactMessageControllerTest extends WebTestCase
{
    public function testCreateContactMessageSuccess(): void
    {
        $client = static::createClient();

        $payload = [
            'fullName' => 'Jan Kowalski',
            'email' => 'jan.kowalski@example.com',
            'message' => 'To jest testowa wiadomość',
            'consent' => true,
        ];

        $client->request('POST', '/contact-messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($payload['fullName'], $data['fullName']);
        $this->assertSame($payload['email'], $data['email']);
        $this->assertSame($payload['message'], $data['message']);
        $this->assertTrue($data['consent']);
        $this->assertArrayHasKey('createdAt', $data);
    }

    public function testCreateContactMessageValidationErrors(): void
    {
        $client = static::createClient();

        // Missing required fields, invalid email, consent false
        $payload = [
            'fullName' => '',
            'email' => 'not-an-email',
            'message' => '',
            'consent' => false,
        ];

        $client->request('POST', '/contact-messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('fullName', $data['errors']);
        $this->assertArrayHasKey('email', $data['errors']);
        $this->assertArrayHasKey('message', $data['errors']);
        $this->assertArrayHasKey('consent', $data['errors']);
    }

    public function testListContactMessages(): void
    {
        $client = static::createClient();

        // Create one message first
        $client->request('POST', '/contact-messages', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'fullName' => 'Anna Nowak',
            'email' => 'anna.nowak@example.com',
            'message' => 'Wiadomość',
            'consent' => true,
        ]));
        $this->assertResponseStatusCodeSame(201);

        // List
        $client->request('GET', '/contact-messages');
        $this->assertResponseIsSuccessful();
        $list = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($list);
        $this->assertNotEmpty($list);
        $first = $list[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('fullName', $first);
        $this->assertArrayHasKey('email', $first);
        $this->assertArrayHasKey('message', $first);
        $this->assertArrayHasKey('createdAt', $first);
    }
}
