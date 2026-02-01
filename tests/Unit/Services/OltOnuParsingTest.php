<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Olt;
use App\Services\OltService;
use Tests\TestCase;

/**
 * Tests for vendor-specific ONU parsing in OltService.
 * 
 * Ensures regex patterns correctly parse CLI output from different vendors.
 * Note: Does not use database - creates Olt models without persisting.
 */
class OltOnuParsingTest extends TestCase
{
    private OltService $oltService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oltService = new OltService();
    }

    public function test_parses_vsol_onu_format_correctly(): void
    {
        $olt = new Olt([
            'brand' => 'VSOL',
            'model' => 'V1600D',
        ]);

        $output = <<<'OUTPUT'
gpon-onu_1/1:1    HWTC12345678    online    0/1/1    1
gpon-onu_1/1:2    HWTC87654321    offline   0/1/1    2
gpon-onu_1/2:1    ZTEG11223344    online    0/1/2    1
OUTPUT;

        $parseMethod = new \ReflectionMethod($this->oltService, 'parseOnuListOutput');
        $parseMethod->setAccessible(true);
        $onus = $parseMethod->invoke($this->oltService, $output, $olt);

        $this->assertCount(3, $onus);
        
        // Check first ONU
        $this->assertEquals('1/1', $onus[0]['pon_port']);
        $this->assertEquals(1, $onus[0]['onu_id']);
        $this->assertEquals('HWTC12345678', $onus[0]['serial_number']);
        $this->assertEquals('online', $onus[0]['status']);
        
        // Check second ONU
        $this->assertEquals('1/1', $onus[1]['pon_port']);
        $this->assertEquals(2, $onus[1]['onu_id']);
        $this->assertEquals('HWTC87654321', $onus[1]['serial_number']);
        $this->assertEquals('offline', $onus[1]['status']);
    }

    public function test_parses_huawei_onu_format_correctly(): void
    {
        $olt = new Olt([
            'brand' => 'Huawei',
            'model' => 'MA5608T',
        ]);

        $output = <<<'OUTPUT'
0/1/1    1    HWTC12345678    online    
0/1/1    2    HWTC87654321    offline   
0/1/2    1    ZTEG11223344    online    
OUTPUT;

        $parseMethod = new \ReflectionMethod($this->oltService, 'parseOnuListOutput');
        $parseMethod->setAccessible(true);
        $onus = $parseMethod->invoke($this->oltService, $output, $olt);

        $this->assertCount(3, $onus);
        
        $this->assertEquals('0/1/1', $onus[0]['pon_port']);
        $this->assertEquals(1, $onus[0]['onu_id']);
        $this->assertEquals('HWTC12345678', $onus[0]['serial_number']);
        $this->assertEquals('online', $onus[0]['status']);
        
        $this->assertEquals('0/1/2', $onus[2]['pon_port']);
        $this->assertEquals(1, $onus[2]['onu_id']);
        $this->assertEquals('ZTEG11223344', $onus[2]['serial_number']);
        $this->assertEquals('online', $onus[2]['status']);
    }

    public function test_parses_zte_onu_format_correctly(): void
    {
        $olt = new Olt([
            'brand' => 'ZTE',
            'model' => 'C300',
        ]);

        $output = <<<'OUTPUT'
gpon-onu_1/1:1    ZTEG12345678    Working    
gpon-onu_1/1:2    ZTEG87654321    LOS        
gpon-onu_1/2:1    HWTC11223344    Working    
OUTPUT;

        $parseMethod = new \ReflectionMethod($this->oltService, 'parseOnuListOutput');
        $parseMethod->setAccessible(true);
        $onus = $parseMethod->invoke($this->oltService, $output, $olt);

        $this->assertCount(3, $onus);
        
        // ZTE uses "Working" for online
        $this->assertEquals('1/1', $onus[0]['pon_port']);
        $this->assertEquals(1, $onus[0]['onu_id']);
        $this->assertEquals('ZTEG12345678', $onus[0]['serial_number']);
        $this->assertEquals('online', $onus[0]['status']);
        
        // LOS (Loss of Signal) is offline
        $this->assertEquals('1/1', $onus[1]['pon_port']);
        $this->assertEquals(2, $onus[1]['onu_id']);
        $this->assertEquals('ZTEG87654321', $onus[1]['serial_number']);
        $this->assertEquals('offline', $onus[1]['status']);
    }

    public function test_parses_fiberhome_onu_format_correctly(): void
    {
        $olt = new Olt([
            'brand' => 'Fiberhome',
            'model' => 'AN5516-01',
        ]);

        $output = <<<'OUTPUT'
1/1    1    FHTT12345678    online    
1/1    2    FHTT87654321    offline   
1/2    1    FHTT11223344    online    
OUTPUT;

        $parseMethod = new \ReflectionMethod($this->oltService, 'parseOnuListOutput');
        $parseMethod->setAccessible(true);
        $onus = $parseMethod->invoke($this->oltService, $output, $olt);

        $this->assertCount(3, $onus);
        
        $this->assertEquals('1/1', $onus[0]['pon_port']);
        $this->assertEquals(1, $onus[0]['onu_id']);
        $this->assertEquals('FHTT12345678', $onus[0]['serial_number']);
        $this->assertEquals('online', $onus[0]['status']);
    }

    public function test_generic_parser_handles_unknown_format(): void
    {
        $olt = new Olt([
            'brand' => 'Unknown',
            'model' => 'TEST-OLT',
        ]);

        // Generic format with various separator styles
        $output = <<<'OUTPUT'
1/1:1    GENR12345678    online    
1-1:2    GENR87654321    offline   
OUTPUT;

        $parseMethod = new \ReflectionMethod($this->oltService, 'parseOnuListOutput');
        $parseMethod->setAccessible(true);
        $onus = $parseMethod->invoke($this->oltService, $output, $olt);

        // Generic parser should catch at least the first line
        $this->assertGreaterThanOrEqual(1, count($onus));
        
        if (count($onus) > 0) {
            $this->assertEquals('GENR12345678', $onus[0]['serial_number']);
            $this->assertEquals('online', $onus[0]['status']);
        }
    }

    public function test_handles_empty_output_gracefully(): void
    {
        $olt = new Olt([
            'brand' => 'VSOL',
        ]);

        $parseMethod = new \ReflectionMethod($this->oltService, 'parseOnuListOutput');
        $parseMethod->setAccessible(true);
        $onus = $parseMethod->invoke($this->oltService, '', $olt);

        $this->assertIsArray($onus);
        $this->assertEmpty($onus);
    }

    public function test_ignores_invalid_lines(): void
    {
        $olt = new Olt([
            'brand' => 'Huawei',
        ]);

        $output = <<<'OUTPUT'
--- Header Line ---
Invalid Line Without Pattern
0/1/1    1    HWTC12345678    online    
Another Invalid Line
OUTPUT;

        $parseMethod = new \ReflectionMethod($this->oltService, 'parseOnuListOutput');
        $parseMethod->setAccessible(true);
        $onus = $parseMethod->invoke($this->oltService, $output, $olt);

        // Should only parse the valid line
        $this->assertCount(1, $onus);
        $this->assertEquals('HWTC12345678', $onus[0]['serial_number']);
    }
}
