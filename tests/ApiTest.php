<?php

use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase{
    private string $baseUrl = 'http://localhost/AI-GENERATED-CODE-REVIEWER-TECH-ASSIGNMENT4/api/review.php';

    public function test_valid_schema_returns(): void{
        $payload = [
            'code' => 'print("Hello World")',
            'filename' => 'test.py',
            'language' => 'python'
        ];

        $ch = curl_init($this->baseUrl);
        curl_setopt_array($ch,[
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);

        $this-> assertNotFalse($response, "API call failed");

        $decoded = json_decode($response, true);
        $this-> assertIsArray($decoded, "Response is not valid JSON");

        foreach ($decoded as $item) {
            $this->assertArrayHasKey('severity', $item);
            $this->assertArrayHasKey('file', $item);
            $this->assertArrayHasKey('issue', $item);
            $this->assertArrayHasKey('suggestion', $item);
        }


    }
}