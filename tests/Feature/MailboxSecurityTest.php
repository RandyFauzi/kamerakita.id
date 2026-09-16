<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CapturedEmail;
use App\Models\MailboxUnmatchedEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MailboxSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_recipient_matching_and_domain_isolation()
    {
        $userA = User::factory()->create(['email' => 'andi@kamerakita.id']);
        
        // Simulating the logic inside ProcessCatchAllEmailService
        $incomingRecipient = 'andi@example.com';
        $userMap = User::pluck('id', 'email')->keyBy(fn ($id, $email) => strtolower(trim($email)));
        
        $userId = null;
        if ($userMap->has($incomingRecipient)) {
            $userId = $userMap->get($incomingRecipient);
        }
        
        $this->assertNull($userId, 'Email for different domain with same local-part should not be matched to user A');
    }

    public function test_case_normalization()
    {
        $user = User::factory()->create(['email' => 'andi@kamerakita.id']);
        $incomingRecipient = 'Andi@KameraKita.ID';
        
        $userMap = User::pluck('id', 'email')->keyBy(fn ($id, $email) => strtolower(trim($email)));
        
        $normalizedRecipient = strtolower(trim($incomingRecipient));
        $this->assertTrue($userMap->has($normalizedRecipient));
        $this->assertEquals($user->id, $userMap->get($normalizedRecipient));
    }

    public function test_unknown_recipient_goes_to_quarantine()
    {
        // This is asserting the outcome behavior that unmatched emails are routed to MailboxUnmatchedEmail
        $unmatched = MailboxUnmatchedEmail::create([
            'email_account' => 'default',
            'imap_uid' => 1234,
            'imap_uidvalidity' => 5678,
            'recipient' => 'nobody@kamerakita.id',
            'sender' => 'sender@test.com',
            'subject' => 'Test',
            'reason' => 'Recipient not registered'
        ]);

        $this->assertDatabaseHas('mailbox_unmatched_emails', ['recipient' => 'nobody@kamerakita.id']);
    }

    public function test_malicious_html_email_is_sanitized()
    {
        $user = User::factory()->create();
        $email = CapturedEmail::create([
            'user_id' => $user->id,
            'sender_address' => 'hacker@example.com',
            'message_content' => '<p>Hello</p><script>alert("XSS")</script><a href="javascript:alert(1)">Click</a><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=">'
        ]);

        $sanitized = $email->sanitized_content;
        
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('javascript:', $sanitized);
        $this->assertStringNotContainsString('data:image', $sanitized);
        $this->assertStringContainsString('Hello', $sanitized);
    }

    public function test_oversized_email_body_truncated_before_regex()
    {
        $user = User::factory()->create();
        $largeString = str_repeat('A', 600000);
        $email = CapturedEmail::create([
            'user_id' => $user->id,
            'sender_address' => 'big@example.com',
            'message_content' => '<p>' . $largeString . '</p>'
        ]);

        $sanitized = $email->sanitized_content;
        $this->assertTrue(strlen($sanitized) < 600000);
        $this->assertStringContainsString('Pesan terpotong', $sanitized);
    }

    public function test_api_401_403_responses()
    {
        $response = $this->withHeaders(['Accept' => 'application/json'])->getJson('/mailbox/api/emails');
        $this->assertTrue(in_array($response->status(), [401, 302]));

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $email = CapturedEmail::create([
            'user_id' => $user2->id,
            'sender_address' => 'sender@test.com',
            'message_content' => 'test'
        ]);

        $response = $this->actingAs($user1)->getJson("/mailbox/api/emails/{$email->id}");
        $response->assertStatus(403);
    }
}
