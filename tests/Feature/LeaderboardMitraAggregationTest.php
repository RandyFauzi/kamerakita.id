<?php

namespace Tests\Feature;

use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\Partner;
use App\Models\User;
use App\Services\LeaderboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LeaderboardMitraAggregationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_leaderboard_aggregates_workers_under_mitra_to_parent_mitra_account(): void
    {
        // 1. Create a Mitra Partner
        $mitraUser = User::factory()->create(['name' => 'Mitra Leader']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => Partner::ROLE_MITRA,
            'full_name' => 'Vendor Berkah Perkasa',
            'mitra_id' => 'KMK-V01',
        ]);

        // 2. Create 2 Workers under that Mitra
        $worker1User = User::factory()->create(['email' => 'worker1@example.com']);
        $worker1 = Partner::factory()->create([
            'user_id' => $worker1User->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mitra->id,
            'full_name' => 'Worker Tim A',
            'email' => 'worker1@example.com',
        ]);
        $atlasWorker1 = AtlasWorker::create([
            'user_id' => $worker1User->id,
            'atlas_email' => 'worker1@example.com',
        ]);

        $worker2User = User::factory()->create(['email' => 'worker2@example.com']);
        $worker2 = Partner::factory()->create([
            'user_id' => $worker2User->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mitra->id,
            'full_name' => 'Worker Tim B',
            'email' => 'worker2@example.com',
        ]);
        $atlasWorker2 = AtlasWorker::create([
            'user_id' => $worker2User->id,
            'atlas_email' => 'worker2@example.com',
        ]);

        // 3. Create an Independent (Solo) Worker
        $soloUser = User::factory()->create(['email' => 'solo@example.com']);
        $soloWorker = Partner::factory()->create([
            'user_id' => $soloUser->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => null,
            'full_name' => 'Rudi Solo Fighter',
            'email' => 'solo@example.com',
        ]);
        $atlasWorkerSolo = AtlasWorker::create([
            'user_id' => $soloUser->id,
            'atlas_email' => 'solo@example.com',
        ]);

        // Record tasks within this week (Monday to Sunday)
        $today = Carbon::now()->startOfWeek()->addDays(1)->toDateString();

        // Worker 1 works 300 minutes, approved 280
        AtlasTask::create([
            'atlas_worker_id' => $atlasWorker1->id,
            'atlas_task_id' => 'TASK-W1-01',
            'task_date' => $today,
            'task_name' => 'Task W1',
            'worked_minutes' => 300,
            'approved_minutes' => 280,
            'status' => 'APPROVED',
        ]);

        // Worker 2 works 400 minutes, approved 390
        // (Combined for Vendor Berkah Perkasa = 700 worked, 670 approved, 2 workers)
        AtlasTask::create([
            'atlas_worker_id' => $atlasWorker2->id,
            'atlas_task_id' => 'TASK-W2-01',
            'task_date' => $today,
            'task_name' => 'Task W2',
            'worked_minutes' => 400,
            'approved_minutes' => 390,
            'status' => 'APPROVED',
        ]);

        // Solo worker works 500 minutes, approved 490
        AtlasTask::create([
            'atlas_worker_id' => $atlasWorkerSolo->id,
            'atlas_task_id' => 'TASK-SOLO-01',
            'task_date' => $today,
            'task_name' => 'Task Solo',
            'worked_minutes' => 500,
            'approved_minutes' => 490,
            'status' => 'APPROVED',
        ]);

        $service = app(LeaderboardService::class);
        $weeklyScores = $service->getWeeklyLeaderboard(10);

        // Assertions:
        // 1. Only 2 entries should appear (Vendor Berkah Perkasa and Rudi Solo Fighter)
        // Individual workers "Worker Tim A" and "Worker Tim B" MUST NOT appear individually.
        $this->assertCount(2, $weeklyScores);

        // 2. Rank 1 is the Mitra (Vendor Berkah Perkasa) with 700 minutes
        $rank1 = $weeklyScores[0];
        $this->assertEquals('Vendor Berkah Perkasa', $rank1['name']);
        $this->assertEquals(700.0, $rank1['worked_minutes']);
        $this->assertEquals(670.0, $rank1['approved_minutes']);
        $this->assertTrue($rank1['is_team']);
        $this->assertEquals(2, $rank1['members_count']);
        $this->assertEquals($mitra->id, $rank1['entity_id']);

        // 3. Rank 2 is the Solo Worker with 500 minutes
        $rank2 = $weeklyScores[1];
        $this->assertEquals('Rudi Solo Fighter', $rank2['name']);
        $this->assertEquals(500.0, $rank2['worked_minutes']);
        $this->assertEquals(490.0, $rank2['approved_minutes']);
        $this->assertFalse($rank2['is_team']);
        $this->assertEquals(1, $rank2['members_count']);
        $this->assertEquals($soloWorker->id, $rank2['entity_id']);

        // 4. Test rank check for both Mitra and worker under Mitra
        $this->assertEquals(1, $service->getPartnerWeeklyRank($mitra->id, 3));
        $this->assertEquals(1, $service->getPartnerWeeklyRank($worker1->id, 3));
        $this->assertEquals(1, $service->getPartnerWeeklyRank($worker2->id, 3));
        $this->assertEquals(2, $service->getPartnerWeeklyRank($soloWorker->id, 3));
    }

    public function test_leaderboard_page_renders_with_team_and_solo_badges(): void
    {
        $mitraUser = User::factory()->create(['name' => 'Mitra User']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => Partner::ROLE_MITRA,
            'full_name' => 'Agensi Juara',
        ]);

        $workerUser = User::factory()->create(['email' => 'timworker@example.com']);
        $worker = Partner::factory()->create([
            'user_id' => $workerUser->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mitra->id,
            'full_name' => 'Worker Under Mitra',
            'email' => 'timworker@example.com',
        ]);
        $atlasWorker = AtlasWorker::create([
            'user_id' => $workerUser->id,
            'atlas_email' => 'timworker@example.com',
        ]);

        $today = Carbon::now()->startOfWeek()->addDays(2)->toDateString();
        AtlasTask::create([
            'atlas_worker_id' => $atlasWorker->id,
            'atlas_task_id' => 'TASK-VIEW-01',
            'task_date' => $today,
            'task_name' => 'Task View',
            'worked_minutes' => 600,
            'approved_minutes' => 600,
            'status' => 'APPROVED',
        ]);

        $this->actingAs($mitraUser);

        $response = $this->get(route('leaderboard.index'));

        $response->assertStatus(200);
        $response->assertSee('Agensi Juara');
        $response->assertSee('Tim Mitra');
        $response->assertDontSee('Worker Under Mitra'); // Worker under mitra must NOT be displayed on its own
    }
}
