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

class LeaderboardCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_leaderboard_separates_worker_and_mitra_categories_accurately(): void
    {
        $today = Carbon::now()->startOfWeek()->addDays(1)->toDateString();

        // 1. Mitra Studio + Worker under Mitra (worked 900m total -> Category: mitra)
        $mitraUser = User::factory()->create(['name' => 'Studio Alpha Owner']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => Partner::ROLE_MITRA,
            'full_name' => 'Studio Alpha Team',
            'mitra_id' => 'KMK-M01',
        ]);

        $mitraWorkerUser = User::factory()->create(['email' => 'mitra_w1@example.com']);
        $mitraWorker = Partner::factory()->create([
            'user_id' => $mitraWorkerUser->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mitra->id,
            'full_name' => 'Anggota Studio Alpha',
            'email' => 'mitra_w1@example.com',
        ]);
        $atlasMitraW1 = AtlasWorker::create([
            'user_id' => $mitraWorkerUser->id,
            'atlas_email' => 'mitra_w1@example.com',
        ]);
        AtlasTask::create([
            'atlas_worker_id' => $atlasMitraW1->id,
            'atlas_task_id' => 'T-MITRA-1',
            'task_name' => 'Task Mitra 1',
            'task_date' => $today,
            'worked_minutes' => 900,
            'approved_minutes' => 850,
            'status' => 'APPROVED',
        ]);

        // 2. Main Account + 2 Sub-accounts (worked 250 + 350 = 600m total -> Category: worker)
        $mainUser = User::factory()->create(['name' => 'Andi Induk']);
        $mainAccount = Partner::factory()->create([
            'user_id' => $mainUser->id,
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
            'full_name' => 'Andi Main Account',
            'mitra_id' => 'KMK-I01',
        ]);

        $sub1User = User::factory()->create(['email' => 'andi_sub1@example.com']);
        $sub1 = Partner::factory()->create([
            'user_id' => $sub1User->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mainAccount->id,
            'full_name' => 'Andi Sub 1',
            'email' => 'andi_sub1@example.com',
        ]);
        $atlasSub1 = AtlasWorker::create([
            'user_id' => $sub1User->id,
            'atlas_email' => 'andi_sub1@example.com',
        ]);
        AtlasTask::create([
            'atlas_worker_id' => $atlasSub1->id,
            'atlas_task_id' => 'T-SUB-1',
            'task_name' => 'Task Sub 1',
            'task_date' => $today,
            'worked_minutes' => 250,
            'approved_minutes' => 240,
            'status' => 'APPROVED',
        ]);

        $sub2User = User::factory()->create(['email' => 'andi_sub2@example.com']);
        $sub2 = Partner::factory()->create([
            'user_id' => $sub2User->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mainAccount->id,
            'full_name' => 'Andi Sub 2',
            'email' => 'andi_sub2@example.com',
        ]);
        $atlasSub2 = AtlasWorker::create([
            'user_id' => $sub2User->id,
            'atlas_email' => 'andi_sub2@example.com',
        ]);
        AtlasTask::create([
            'atlas_worker_id' => $atlasSub2->id,
            'atlas_task_id' => 'T-SUB-2',
            'task_name' => 'Task Sub 2',
            'task_date' => $today,
            'worked_minutes' => 350,
            'approved_minutes' => 330,
            'status' => 'APPROVED',
        ]);

        // 3. Solo Worker (worked 450m -> Category: worker)
        $soloUser = User::factory()->create(['email' => 'solo_andi@example.com']);
        $solo = Partner::factory()->create([
            'user_id' => $soloUser->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => null,
            'full_name' => 'Siti Solo Worker',
            'email' => 'solo_andi@example.com',
        ]);
        $atlasSolo = AtlasWorker::create([
            'user_id' => $soloUser->id,
            'atlas_email' => 'solo_andi@example.com',
        ]);
        AtlasTask::create([
            'atlas_worker_id' => $atlasSolo->id,
            'atlas_task_id' => 'T-SOLO-1',
            'task_name' => 'Task Solo 1',
            'task_date' => $today,
            'worked_minutes' => 450,
            'approved_minutes' => 420,
            'status' => 'APPROVED',
        ]);

        $service = app(LeaderboardService::class);

        // Assert Worker Category
        $workerLeaderboard = $service->getWeeklyLeaderboard(10, LeaderboardService::CATEGORY_WORKER);
        $this->assertCount(2, $workerLeaderboard); // Only Andi Main Account + Siti Solo Worker

        $this->assertEquals('Andi Main Account', $workerLeaderboard[0]['name']);
        $this->assertEquals(600.0, $workerLeaderboard[0]['worked_minutes']);
        $this->assertEquals(570.0, $workerLeaderboard[0]['approved_minutes']);
        $this->assertEquals('main_account', $workerLeaderboard[0]['entity_type']);
        $this->assertEquals(2, $workerLeaderboard[0]['members_count']);

        $this->assertEquals('Siti Solo Worker', $workerLeaderboard[1]['name']);
        $this->assertEquals(450.0, $workerLeaderboard[1]['worked_minutes']);
        $this->assertEquals('solo', $workerLeaderboard[1]['entity_type']);

        // Assert Mitra Category
        $mitraLeaderboard = $service->getWeeklyLeaderboard(10, LeaderboardService::CATEGORY_MITRA);
        $this->assertCount(1, $mitraLeaderboard); // Only Studio Alpha Team

        $this->assertEquals('Studio Alpha Team', $mitraLeaderboard[0]['name']);
        $this->assertEquals(900.0, $mitraLeaderboard[0]['worked_minutes']);
        $this->assertEquals('mitra', $mitraLeaderboard[0]['entity_type']);

        // Assert per-category weekly ranks
        $this->assertEquals(1, $service->getPartnerWeeklyRank($mainAccount->id, 3, LeaderboardService::CATEGORY_WORKER));
        $this->assertEquals(1, $service->getPartnerWeeklyRank($sub1->id, 3, LeaderboardService::CATEGORY_WORKER));
        $this->assertEquals(2, $service->getPartnerWeeklyRank($solo->id, 3, LeaderboardService::CATEGORY_WORKER));
        $this->assertNull($service->getPartnerWeeklyRank($mitra->id, 3, LeaderboardService::CATEGORY_WORKER));

        $this->assertEquals(1, $service->getPartnerWeeklyRank($mitra->id, 3, LeaderboardService::CATEGORY_MITRA));
        $this->assertEquals(1, $service->getPartnerWeeklyRank($mitraWorker->id, 3, LeaderboardService::CATEGORY_MITRA));
        $this->assertNull($service->getPartnerWeeklyRank($mainAccount->id, 3, LeaderboardService::CATEGORY_MITRA));
    }

    public function test_leaderboard_page_displays_worker_and_mitra_categories(): void
    {
        $user = User::factory()->create([
            'role' => 'partner',
            'has_completed_onboarding' => true,
        ]);
        Partner::factory()->create([
            'user_id' => $user->id,
            'partner_role' => Partner::ROLE_WORKER,
        ]);

        $response = $this->actingAs($user)->get(route('leaderboard.index'));
        $response->assertOk();
        $response->assertSee('Kategori Worker');
        $response->assertSee('Kategori Mitra');
        $response->assertSee('Individu');
        $response->assertSee('Main Account');
    }
}
