<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class AdminApkDownloadTest extends TestCase
{
    use RefreshDatabase;

    private const APK = 'app-debug.apk';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('kamerakita_apk');
    }

    #[DataProvider('allowedRoles')]
    public function test_admin_can_see_and_download_the_private_apk(string $role): void
    {
        $apk = str_repeat('a', 1_250_000);
        Storage::disk('kamerakita_apk')->put(self::APK, $apk);
        $this->actingAs(User::factory()->create(['role' => $role]));

        $this->get('/admin/unduh-apk')->assertOk()
            ->assertSee('Unduh APK KameraKita')
            ->assertSee('app-debug.apk')
            ->assertSee('Debug')
            ->assertSee('1,25 MB')
            ->assertSee(route('admin.apk.download'));

        $response = $this->get('/admin/unduh-apk/download')->assertOk()
            ->assertDownload('app-debug.apk')
            ->assertHeader('Content-Type', 'application/vnd.android.package-archive');

        $binary = $response->baseResponse;
        $this->assertInstanceOf(BinaryFileResponse::class, $binary);
        $this->assertTrue($binary->headers->hasCacheControlDirective('private'));
        $this->assertTrue($binary->headers->hasCacheControlDirective('no-store'));
        $this->assertSame(strlen($apk), $binary->getFile()->getSize());
        $this->assertSame(hash('sha256', $apk), hash_file('sha256', $binary->getFile()->getPathname()));
    }

    public static function allowedRoles(): array
    {
        return [['admin'], ['superadmin']];
    }

    public function test_guest_is_redirected_to_login_from_both_urls(): void
    {
        $this->get('/admin/unduh-apk')->assertRedirect(route('login'));
        $this->get('/admin/unduh-apk/download')->assertRedirect(route('login'));
    }

    public function test_non_admin_is_denied_access_to_both_urls(): void
    {
        Storage::disk('kamerakita_apk')->put(self::APK, 'APK fixture');

        foreach (['worker', 'finance', 'accountant_tax'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get('/admin/unduh-apk')->assertForbidden();
            $this->get('/admin/unduh-apk/download')->assertForbidden();
        }
    }

    public function test_admin_sees_unavailable_status_when_apk_is_missing(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get('/admin/unduh-apk')->assertOk()
            ->assertSee('APK belum tersedia di server.')
            ->assertDontSee(route('admin.apk.download'));
        $this->get('/admin/unduh-apk/download')->assertNotFound();
    }
}
