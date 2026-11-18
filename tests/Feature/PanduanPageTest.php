<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanduanPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_roles_see_panduan_page_with_video_tutorials_and_hidden_pdf(): void
    {
        $roles = [
            'worker',
            'mitra',
            'main_account',
            'rekruter',
            'admin',
            'finance',
            'superadmin',
        ];

        foreach ($roles as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'email_verified_at' => now(),
                'has_completed_onboarding' => true,
            ]);

            $response = $this->actingAs($user)->get(route('panduan.index'));

            $response->assertOk();
            $response->assertSeeText('Panduan');
            $response->assertSeeText('Video Tutorial Daftar KameraKita');
            $response->assertSeeText('Video Tutorial Daftar Atlas');
            $response->assertSee('assets/tutorials/tutorial-daftar-kamerakita.mp4', false);
            $response->assertSee('assets/tutorials/tutorial-daftar-atlas.mp4', false);
            $response->assertDontSee('ManualBook_Atlas.pdf', false);
            $response->assertDontSeeText('Unduh PDF');
            $response->assertDontSeeText('Panduan Book');
            $response->assertDontSeeText('Guide Book');
        }
    }
}
