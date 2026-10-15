<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_guest_can_view_both_languages_with_identical_verified_metrics(): void
    {
        foreach (['id' => ['Momen nyata.', '2.529+', '74,1'], 'en' => ['Real life.', '2,529+', '74.1']] as $locale => $strings) {
            $response = $this->withSession(['locale' => $locale])->get('/portofolio');
            $response->assertOk()->assertSee('lang="'.$locale.'"', false);
            foreach ($strings as $string) {
                $response->assertSee($string);
            }
            $response->assertSee('Atlas Capture')->assertSee('Minute Data')->assertSee('529');
            $response->assertDontSee('portfolio.hero.')->assertDontSee('bank_account')->assertDontSee('payment_status');
        }
    }

    public function test_both_translations_have_the_same_keys(): void
    {
        $flatten = function (array $values, string $prefix = '') use (&$flatten): array {
            $keys = [];
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    $keys = array_merge($keys, $flatten($value, $prefix.$key.'.'));
                } else {
                    $keys[] = $prefix.$key;
                }
            }

            return $keys;
        };
        $this->assertSame($flatten(require lang_path('id/portfolio.php')), $flatten(require lang_path('en/portfolio.php')));
    }

    public function test_contact_validates_before_opening_whatsapp(): void
    {
        $this->withSession(['locale' => 'en'])->from('/portofolio')->post('/portofolio/contact', [])
            ->assertRedirect('/portofolio')->assertSessionHasErrors(['name', 'company', 'email', 'brief']);
    }

    public function test_contact_opens_an_encoded_brief_without_sending_a_message(): void
    {
        $data = ['name' => 'A & B', 'company' => 'Research Lab', 'email' => 'pilot@example.com', 'brief' => '100 hours of kitchen POV; start next month.'];
        $response = $this->withSession(['locale' => 'en'])->post('/portofolio/contact', $data);
        $response->assertRedirect();
        $url = $response->headers->get('Location');
        $this->assertSame('wa.me', parse_url($url, PHP_URL_HOST));
        $this->assertSame('/'.config('portfolio.contact_whatsapp'), parse_url($url, PHP_URL_PATH));
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertStringContainsString('Full name: A & B', $query['text']);
        $this->assertStringContainsString($data['brief'], $query['text']);
        Http::assertNothingSent();
    }

    public function test_public_page_does_not_require_a_database_connection(): void
    {
        config(['database.default' => 'not-configured']);
        $this->withSession(['locale' => 'en'])->get('/portofolio')->assertOk();
    }
}
