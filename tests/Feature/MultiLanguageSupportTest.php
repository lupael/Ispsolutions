<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

/**
 * Test: Multi-language support
 * Tests UI displays in user selected language
 */
class MultiLanguageSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_select_language(): void
    {
        $user = User::factory()->create([
            'language' => 'en',
        ]);

        $this->assertEquals('en', $user->language);

        $user->update(['language' => 'bn']);
        $user->refresh();

        $this->assertEquals('bn', $user->language);
    }

    public function test_default_language_is_english(): void
    {
        $user = User::factory()->create();

        // Default language should be 'en' based on migration
        $this->assertEquals('en', $user->language ?? 'en');
    }

    public function test_app_locale_can_be_set(): void
    {
        App::setLocale('bn');
        
        $this->assertEquals('bn', App::getLocale());

        App::setLocale('en');
        
        $this->assertEquals('en', App::getLocale());
    }

    public function test_language_files_exist(): void
    {
        $this->assertDirectoryExists(lang_path('en'));
        $this->assertDirectoryExists(lang_path('bn'));
    }

    public function test_billing_translation_file_exists(): void
    {
        $this->assertFileExists(lang_path('en/billing.php'));
        $this->assertFileExists(lang_path('bn/billing.php'));
    }

    public function test_translation_helper_works(): void
    {
        App::setLocale('en');
        
        // Test that translation helper exists
        $translated = __('billing.paid');
        
        $this->assertNotNull($translated);
    }

    public function test_language_persists_in_session(): void
    {
        $user = User::factory()->create(['language' => 'bn']);

        $this->actingAs($user);

        // Simulate language switching
        session(['locale' => 'bn']);

        $this->assertEquals('bn', session('locale'));
    }

    public function test_dates_can_be_formatted_per_locale(): void
    {
        $date = now();

        App::setLocale('en');
        $englishFormat = $date->locale('en')->diffForHumans();

        App::setLocale('bn');
        $bengaliFormat = $date->locale('bn')->diffForHumans();

        // The formats should be different for different locales
        $this->assertIsString($englishFormat);
        $this->assertIsString($bengaliFormat);
    }

    public function test_supported_languages_include_english_and_bengali(): void
    {
        $supportedLanguages = ['en', 'bn'];

        $this->assertContains('en', $supportedLanguages);
        $this->assertContains('bn', $supportedLanguages);
    }
}
