<?php

namespace Tests\Feature;

use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\Partner;
use App\Models\User;
use App\Models\VideoWorkReport;
use App\Services\CalculatePartnerMetricsService;
use App\Services\DashboardCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_metrics_are_cached_and_isolated_per_partner(): void
    {
        $userA = User::factory()->create(['role' => 'worker']);
        $userB = User::factory()->create(['role' => 'worker']);

        $partnerA = Partner::create([
            'user_id' => $userA->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_id' => 'KMK-A01',
            'full_name' => 'Worker A',
            'whatsapp_number' => '628111111111',
            'email' => $userA->email,
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $partnerB = Partner::create([
            'user_id' => $userB->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_id' => 'KMK-B01',
            'full_name' => 'Worker B',
            'whatsapp_number' => '628222222222',
            'email' => $userB->email,
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $awA = AtlasWorker::create(['user_id' => $userA->id, 'atlas_email' => $userA->email, 'group' => 'ASTRO']);
        $awB = AtlasWorker::create(['user_id' => $userB->id, 'atlas_email' => $userB->email, 'group' => 'ASTRO']);

        AtlasTask::create([
            'atlas_worker_id' => $awA->id,
            'atlas_task_id' => 'task-a-1',
            'task_date' => now()->toDateString(),
            'task_name' => 'Task A',
            'worked_minutes' => 60,
            'approved_minutes' => 60,
        ]);

        AtlasTask::create([
            'atlas_worker_id' => $awB->id,
            'atlas_task_id' => 'task-b-1',
            'task_date' => now()->toDateString(),
            'task_name' => 'Task B',
            'worked_minutes' => 120,
            'approved_minutes' => 120,
        ]);

        $service = app(CalculatePartnerMetricsService::class);

        $metricsA = $service->getWorkerMetrics($partnerA);
        $metricsB = $service->getWorkerMetrics($partnerB);

        $this->assertEquals(60.0, $metricsA['all_time_minutes']);
        $this->assertEquals(120.0, $metricsB['all_time_minutes']);
        $this->assertTrue(Cache::has(DashboardCacheService::workerKey($partnerA->id)));
        $this->assertTrue(Cache::has(DashboardCacheService::workerKey($partnerB->id)));

        // Second call should hit cache (0 queries)
        DB::enableQueryLog();
        DB::flushQueryLog();
        $cachedA = $service->getWorkerMetrics($partnerA);
        $this->assertSame([], DB::getQueryLog());
        $this->assertEquals(60.0, $cachedA['all_time_minutes']);
        DB::disableQueryLog();
    }

    public function test_cache_is_invalidated_automatically_when_source_data_changes(): void
    {
        $user = User::factory()->create(['role' => 'worker']);
        $mitraUser = User::factory()->create(['role' => 'mitra']);

        $mitra = Partner::create([
            'user_id' => $mitraUser->id,
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
            'mitra_id' => 'KMK-M01',
            'full_name' => 'Main Account',
            'whatsapp_number' => '628333333333',
            'email' => $mitraUser->email,
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $worker = Partner::create([
            'user_id' => $user->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mitra->id,
            'mitra_id' => 'KMK-W01',
            'full_name' => 'Sub Worker',
            'whatsapp_number' => '628444444444',
            'email' => $user->email,
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $aw = AtlasWorker::create(['user_id' => $user->id, 'atlas_email' => $user->email, 'group' => 'ASTRO']);

        $service = app(CalculatePartnerMetricsService::class);
        $service->getGlobalMetrics();
        $service->getWorkerMetrics($worker);
        $service->getMitraMetrics($mitra);

        $this->assertTrue(Cache::has(DashboardCacheService::ownerGlobalKey()));
        $this->assertTrue(Cache::has(DashboardCacheService::workerKey($worker->id)));
        $this->assertTrue(Cache::has(DashboardCacheService::mitraKey($mitra->id)));

        // Saving an AtlasTask must invalidate worker, parent mitra, and owner global caches
        AtlasTask::create([
            'atlas_worker_id' => $aw->id,
            'atlas_task_id' => 'task-inv-1',
            'task_date' => now()->toDateString(),
            'task_name' => 'Task Invalidation',
            'worked_minutes' => 45,
            'approved_minutes' => 45,
        ]);

        $this->assertFalse(Cache::has(DashboardCacheService::ownerGlobalKey()));
        $this->assertFalse(Cache::has(DashboardCacheService::workerKey($worker->id)));
        $this->assertFalse(Cache::has(DashboardCacheService::mitraKey($mitra->id)));

        // Re-warm and verify VideoWorkReport save also invalidates
        $service->getGlobalMetrics();
        $service->getWorkerMetrics($worker);
        $service->getMitraMetrics($mitra);

        VideoWorkReport::create([
            'partner_id' => $worker->id,
            'project_name' => 'atlas',
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 60,
            'approved_duration_minutes' => 60,
            'qc_status' => 'approved',
            'payment_status' => 'unpaid',
            'evidence_email_image_path' => 'dummy/email.jpg',
        ]);

        $this->assertFalse(Cache::has(DashboardCacheService::ownerGlobalKey()));
        $this->assertFalse(Cache::has(DashboardCacheService::workerKey($worker->id)));
        $this->assertFalse(Cache::has(DashboardCacheService::mitraKey($mitra->id)));
    }

    public function test_cache_service_falls_back_to_database_when_cache_backend_fails(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andThrow(new \RuntimeException('Redis connection refused'));

        $result = app(DashboardCacheService::class)->remember('test:failover', 60, fn () => ['ok' => true]);

        $this->assertSame(['ok' => true], $result);
    }

    public function test_mitra_and_rekruter_metrics_work_with_file_cache_and_serializable_classes_disabled(): void
    {
        config(['cache.default' => 'file', 'cache.serializable_classes' => false]);
        Cache::forgetDriver('file');

        $mitraUser = User::factory()->create(['role' => 'mitra']);
        $workerUser = User::factory()->create(['role' => 'worker']);

        $mitra = Partner::create([
            'user_id' => $mitraUser->id,
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
            'mitra_id' => 'KMK-FILE-M1',
            'full_name' => 'File Cache Parent',
            'whatsapp_number' => '628555555555',
            'email' => $mitraUser->email,
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $worker = Partner::create([
            'user_id' => $workerUser->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mitra->id,
            'recruiter_partner_id' => $mitra->id,
            'mitra_id' => 'KMK-FILE-W1',
            'full_name' => 'File Cache Worker',
            'whatsapp_number' => '628666666666',
            'email' => $workerUser->email,
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $service = app(CalculatePartnerMetricsService::class);

        // First call populates file cache; second call unserializes with serializable_classes = false
        $service->getMitraMetrics($mitra);
        $mitraSecond = $service->getMitraMetrics($mitra);

        $this->assertCount(1, $mitraSecond['workers_data']);
        $this->assertInstanceOf(Partner::class, $mitraSecond['workers_data'][0]['worker']);
        $this->assertSame('File Cache Worker', $mitraSecond['workers_data'][0]['worker']->full_name);
        $this->assertSame($workerUser->email, $mitraSecond['workers_data'][0]['worker']->user->email);

        $service->getRekruterMetrics($mitra);
        $rekruterSecond = $service->getRekruterMetrics($mitra);

        $this->assertCount(1, $rekruterSecond['workers_data']);
        $this->assertInstanceOf(Partner::class, $rekruterSecond['workers_data'][0]['worker']);
        $this->assertSame($worker->id, $rekruterSecond['workers_data'][0]['worker']->id);

        Cache::store('file')->flush();
    }
}
