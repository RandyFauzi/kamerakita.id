<?php

namespace Tests\Feature;

use App\Models\{CapturedEmail, MailboxSyncState, MailboxUnmatchedEmail, User};
use App\Services\ProcessCatchAllEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Webklex\IMAP\Facades\Client;
use Webklex\PHPIMAP\Attribute;

class MailboxSpamSyncTest extends TestCase
{
    use RefreshDatabase;

    private function message(int $uid, string $email, ?string $id = null, bool $catchallHeader = false): object
    {
        $m = Mockery::mock();
        $m->shouldReceive('getUid')->andReturn($uid);
        $m->shouldReceive('getSubject')->andReturn('OTP fixture');
        $m->shouldReceive('getMessageId')->andReturn($id);
        $m->shouldReceive('getDate')->andReturn(null);
        $m->shouldReceive('getAttributes')->andReturn([
            'delivered_to' => new Attribute('delivered_to', [(object) ['mail' => $catchallHeader ? 'sistem@example.com' : $email]]),
            'to' => new Attribute('to', [(object) ['mail' => $email]]),
        ]);
        $m->shouldReceive('getFrom')->andReturn([(object) ['mail' => 'sender@example.com']]);
        $m->shouldReceive('getHTMLBody')->andReturn('<p>OTP fixture</p>');
        $m->shouldReceive('parseBody')->andReturnSelf();
        return $m;
    }

    private function client(array $inbox, array $spam, bool $failInbox = false): void
    {
        $c = Mockery::mock(\Webklex\PHPIMAP\Client::class);
        Client::shouldReceive('account')->with('default')->andReturn($c);
        $c->shouldReceive('connect')->andReturnSelf();
        $c->shouldReceive('disconnect')->andReturnSelf();
        $c->shouldReceive('getFolders')->with(false)->andReturn(new \Webklex\PHPIMAP\Support\FolderCollection([
            (object) ['name' => 'INBOX.Junk', 'path' => 'INBOX.Junk', 'no_select' => false],
            (object) ['name' => 'Trash', 'path' => 'INBOX.Trash', 'no_select' => false],
        ]));
        foreach (['INBOX' => $inbox, 'INBOX.Junk' => $spam] as $path => $messages) {
            $folder = Mockery::mock(\Webklex\PHPIMAP\Folder::class);
            $c->shouldReceive('getFolderByPath')->with($path)->andReturn($folder);
            $folder->shouldReceive('getStatus')->andReturn(['uidvalidity' => 123]);
            if ($failInbox && $path === 'INBOX') {
                $folder->shouldReceive('query')->andThrow(new \RuntimeException('Inbox temporarily unavailable'));
            } else {
                $query = Mockery::mock(\Webklex\PHPIMAP\Query\WhereQuery::class);
                $folder->shouldReceive('query')->andReturn($query);
                $query->shouldReceive('whereUid')->andReturnSelf();
                $query->shouldReceive('setFetchBody')->with(false)->andReturnSelf();
                $query->shouldReceive('setFetchOrderAsc')->andReturnSelf();
                $query->shouldReceive('limit')->with(100)->andReturnSelf();
                $query->shouldReceive('get')->andReturn(new \Webklex\PHPIMAP\Support\MessageCollection($messages));
            }
        }
    }

    public function test_inbox_and_junk_have_independent_identity_and_cursors(): void
    {
        $user = User::factory()->create();
        $this->client([$this->message(7, $user->email)], [$this->message(7, $user->email)]);
        $service = app(ProcessCatchAllEmailService::class);
        $this->assertSame(2, $service->processEmails()['SAVED_COUNT']);
        $this->assertSame(2, CapturedEmail::count());
        foreach (['INBOX', 'INBOX.Junk'] as $folder) {
            $this->assertDatabaseHas('mailbox_sync_states', ['email_account' => 'default', 'folder_name' => $folder, 'last_uid' => 7]);
            $this->assertDatabaseHas('captured_emails', ['user_id' => $user->id, 'imap_folder' => $folder, 'imap_uid' => 7]);
        }
        $this->assertSame(0, $service->processEmails()['SAVED_COUNT']);
        $this->assertSame(2, CapturedEmail::count());
    }

    public function test_message_moved_between_folders_is_not_imported_twice(): void
    {
        $user = User::factory()->create();
        $this->client([$this->message(1, $user->email, 'same-message@example.com')], [$this->message(9, $user->email, 'same-message@example.com')]);
        $stats = app(ProcessCatchAllEmailService::class)->processEmails();
        $this->assertSame(1, $stats['SAVED_COUNT']);
        $this->assertSame(1, $stats['DUPLICATE_COUNT']);
        $this->assertDatabaseHas('mailbox_sync_states', ['folder_name' => 'INBOX.Junk', 'last_uid' => 9]);
        $this->assertSame(1, CapturedEmail::count());
    }

    public function test_spam_continues_when_inbox_fails_and_quarantine_is_folder_scoped(): void
    {
        $this->client([], [$this->message(7, 'unknown@example.com')], true);
        $stats = app(ProcessCatchAllEmailService::class)->processEmails();
        $this->assertSame(1, $stats['FAILED_COUNT']);
        $this->assertSame(1, $stats['UNMATCHED_COUNT']);
        $this->assertDatabaseHas('mailbox_unmatched_emails', ['folder_name' => 'INBOX.Junk', 'recipient' => 'unknown@example.com', 'imap_uid' => 7]);
        $this->assertDatabaseHas('mailbox_sync_states', ['folder_name' => 'INBOX', 'last_uid' => 0]);
        $this->assertSame(0, CapturedEmail::count());
    }

    public function test_failed_message_does_not_advance_junk_cursor_past_it(): void
    {
        $user = User::factory()->create();
        $bad = Mockery::mock();
        $bad->shouldReceive('getUid')->andReturn(2);
        $bad->shouldReceive('getSubject')->andThrow(new \RuntimeException('Malformed message'));
        $this->client([], [$this->message(1, $user->email), $bad, $this->message(3, $user->email)]);
        $stats = app(ProcessCatchAllEmailService::class)->processEmails();
        $this->assertSame(1, $stats['FAILED_COUNT']);
        $this->assertSame(1, CapturedEmail::count());
        $this->assertDatabaseHas('mailbox_sync_states', ['folder_name' => 'INBOX.Junk', 'last_uid' => 1]);
    }

    public function test_hostinger_catchall_transport_header_does_not_hide_worker_recipient(): void
    {
        config(['imap.accounts.default.username' => 'sistem@example.com']);
        $user = User::factory()->create();
        $this->client([], [$this->message(5, $user->email, 'otp@example.com', true)]);
        $stats = app(ProcessCatchAllEmailService::class)->processEmails();
        $this->assertSame(1, $stats['SAVED_COUNT']);
        $this->assertSame(0, $stats['UNMATCHED_COUNT']);
        $this->assertDatabaseHas('captured_emails', ['user_id' => $user->id, 'imap_folder' => 'INBOX.Junk']);
    }
}
