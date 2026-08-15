<?php

namespace Tests\Feature;

use App\Models\FastworkOnboarding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FastworkOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_onboarding_form_can_be_accessed(): void
    {
        $response = $this->get('/onboarding');
        $response->assertStatus(200);
        $response->assertSee('Portal Onboarding');
    }

    public function test_public_onboarding_submission_creates_record_and_redirects(): void
    {
        $data = [
            'full_name' => 'Budi Santoso',
            'whatsapp_number' => '081299998888',
            'device_type' => 'iPhone 15 Pro / Max',
            'fastwork_username' => 'budi_fastwork',
        ];

        $response = $this->post('/onboarding', $data);

        $response->assertRedirect('https://chat.whatsapp.com/G50crzNGAa6GFPLuXWljK0');
        $this->assertDatabaseHas('fastwork_onboardings', [
            'full_name' => 'Budi Santoso',
            'whatsapp_number' => '081299998888',
            'device_type' => 'iPhone 15 Pro / Max',
            'fastwork_username' => 'budi_fastwork',
        ]);
    }

    public function test_admin_onboarding_page_requires_authentication_and_role(): void
    {
        $response = $this->get('/admin/onboardings');
        $response->assertRedirect('/login');

        // Normal user
        $user = User::factory()->create(['role' => 'worker']);
        $response = $this->actingAs($user)->get('/admin/onboardings');
        $response->assertStatus(403);
    }

    public function test_admin_can_view_registrations_and_delete(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $onboarding = FastworkOnboarding::create([
            'full_name' => 'Andi Wijaya',
            'whatsapp_number' => '081388887777',
            'device_type' => 'iPhone 14',
            'fastwork_username' => 'andi_fw',
        ]);

        $response = $this->actingAs($admin)->get('/admin/onboardings');
        $response->assertStatus(200);
        $response->assertSee('Andi Wijaya');
        $response->assertSee('081388887777');

        // Delete test
        $response = $this->actingAs($admin)->delete("/admin/onboardings/{$onboarding->id}");
        $response->assertRedirect('/admin/onboardings');
        $this->assertDatabaseMissing('fastwork_onboardings', ['id' => $onboarding->id]);
    }
}
