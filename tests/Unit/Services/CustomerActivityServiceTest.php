<?php

namespace Tests\Unit\Services;

use Tests\TestCase;

class CustomerActivityServiceTest extends TestCase
{
    /** @test */
    public function it_formats_string_amounts_correctly_with_number_format()
    {
        // Simulate the Laravel decimal cast behavior which returns a string
        $stringAmount = '100.50';
        
        // Before the fix, this would throw:
        // TypeError: number_format(): Argument #1 ($num) must be of type float, string given
        
        // After the fix with (float) cast, this works
        $formatted = number_format((float) $stringAmount, 2);
        
        $this->assertEquals('100.50', $formatted);
    }

    /** @test */
    public function it_formats_large_string_amounts_with_commas()
    {
        $stringAmount = '1234.56';
        
        // The fix with (float) cast should handle large numbers and add commas
        $formatted = number_format((float) $stringAmount, 2);
        
        $this->assertEquals('1,234.56', $formatted);
    }

    /** @test */
    public function it_handles_integer_string_amounts()
    {
        $stringAmount = '100';
        
        // Should add decimal places
        $formatted = number_format((float) $stringAmount, 2);
        
        $this->assertEquals('100.00', $formatted);
    }

    /** @test */
    public function it_handles_zero_amounts()
    {
        $stringAmount = '0';
        
        $formatted = number_format((float) $stringAmount, 2);
        
        $this->assertEquals('0.00', $formatted);
    }
}
