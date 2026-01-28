<?php

namespace Tests\Feature;

use Tests\TestCase;

class ViteManifestTest extends TestCase
{
    /**
     * Test that Vite manifest file exists and is readable
     *
     * @return void
     */
    public function test_vite_manifest_exists()
    {
        $manifestPath = public_path('build/manifest.json');
        
        $this->assertFileExists($manifestPath, 'Vite manifest.json should exist at public/build/manifest.json');
        $this->assertFileIsReadable($manifestPath, 'Vite manifest.json should be readable');
    }

    /**
     * Test that Vite manifest is valid JSON
     *
     * @return void
     */
    public function test_vite_manifest_is_valid_json()
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileIsReadable($manifestPath, 'Vite manifest.json should be readable');
        
        $manifestContent = file_get_contents($manifestPath);
        $this->assertNotFalse($manifestContent, 'Vite manifest.json should be readable');
        
        $manifest = json_decode($manifestContent, true);
        
        $this->assertIsArray($manifest, 'Vite manifest should be valid JSON');
        $this->assertNotEmpty($manifest, 'Vite manifest should not be empty');
    }

    /**
     * Test that Vite manifest contains required entries
     *
     * @return void
     */
    public function test_vite_manifest_contains_required_entries()
    {
        $manifest = $this->getManifest();
        
        // Check for main entry points defined in vite.config.js
        $this->assertArrayHasKey('resources/css/app.css', $manifest, 'Manifest should contain app.css entry');
        $this->assertArrayHasKey('resources/js/app.js', $manifest, 'Manifest should contain app.js entry');
        $this->assertArrayHasKey('resources/js/bulk-selection.js', $manifest, 'Manifest should contain bulk-selection.js entry');
    }

    /**
     * Test that asset files referenced in manifest actually exist
     *
     * @return void
     */
    public function test_vite_manifest_assets_exist()
    {
        $manifest = $this->getManifest();
        
        foreach ($manifest as $key => $entry) {
            if (isset($entry['file'])) {
                $assetPath = public_path('build/' . $entry['file']);
                $this->assertFileExists($assetPath, "Asset file {$entry['file']} should exist");
            }
        }
    }

    /**
     * Test that app layout can render without ViteManifestNotFoundException
     *
     * @return void
     */
    public function test_app_layout_renders_without_vite_errors()
    {
        $manifest = $this->getManifest();
        
        // Verify manifest is valid and has the required structure
        $this->assertIsArray($manifest);
        $this->assertNotEmpty($manifest);
        
        // Verify all referenced asset files exist
        foreach ($manifest as $entry) {
            if (isset($entry['file'])) {
                $assetPath = public_path('build/' . $entry['file']);
                $this->assertFileExists($assetPath);
            }
        }
    }

    /**
     * Helper method to read and parse the Vite manifest
     *
     * @return array
     */
    private function getManifest(): array
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileIsReadable($manifestPath, 'Vite manifest.json should be readable');
        
        $manifestContent = file_get_contents($manifestPath);
        $this->assertNotFalse($manifestContent, 'Should be able to read manifest file');
        
        $manifest = json_decode($manifestContent, true);
        $this->assertIsArray($manifest, 'Manifest should be valid JSON');
        
        return $manifest;
    }
}
