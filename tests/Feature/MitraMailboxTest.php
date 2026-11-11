<?php

namespace Tests\Feature;

use App\Models\CapturedEmail;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraMailboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_can_read_only_own_team_and_access_is_revoked_after_transfer(): void
    {
        $mitra = Partner::factory()->create(['partner_role' => 'mitra']);
        $other = Partner::factory()->create(['partner_role' => 'mitra']);
        $worker = Partner::factory()->create(['partner_role' => 'worker', 'mitra_parent_id' => $mitra->id]);
        $outside = Partner::factory()->create(['partner_role' => 'worker', 'mitra_parent_id' => $other->id]);
        $nonWorker = Partner::factory()->create(['partner_role' => 'rekruter', 'mitra_parent_id' => $mitra->id]);
        $emails = collect([$mitra, $worker, $outside, $nonWorker])->map(fn ($p) => CapturedEmail::create([
            'user_id' => $p->user_id, 'sender_address' => 'sender@example.com',
            'subject' => 'Shared keyword', 'message_content' => 'Hello', 'received_at' => now(),
        ]));

        $this->actingAs($mitra->user)->getJson('/mailbox/api/emails?search=Shared')
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('total', 2);
        $this->getJson('/mailbox/api/emails/'.$emails[1]->id)->assertOk()
            ->assertJsonPath('can_update', false)->assertJsonPath('can_delete', false);
        foreach ([$emails[2], $emails[3]] as $email) {
            $this->getJson('/mailbox/api/emails/'.$email->id)->assertForbidden();
        }
        $this->patchJson('/mailbox/'.$emails[1]->id.'/read', ['is_read' => true])->assertForbidden();
        $this->patchJson('/mailbox/'.$emails[1]->id.'/star', ['is_starred' => true])->assertForbidden();
        $this->deleteJson('/mailbox/'.$emails[1]->id)->assertForbidden();

        $worker->update(['mitra_parent_id' => $other->id]);
        $this->getJson('/mailbox/api/emails/'.$emails[1]->id)->assertForbidden();
        $this->getJson('/mailbox/api/emails')->assertJsonCount(1, 'data');
        $this->actingAs($other->user)->getJson('/mailbox/api/emails/'.$emails[1]->id)->assertOk();
        $this->actingAs($worker->user)->getJson('/mailbox/api/emails')->assertJsonCount(1, 'data');
        $this->getJson('/mailbox/api/emails/'.$emails[0]->id)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->getJson('/mailbox/api/emails')
            ->assertJsonCount(4, 'data');
    }
}
