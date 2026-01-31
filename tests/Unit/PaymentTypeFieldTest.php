<?php

namespace Tests\Unit;

use App\Models\Payment;
use Tests\TestCase;

class PaymentTypeFieldTest extends TestCase
{
    /**
     * Test that payment_type is in the fillable array of the Payment model.
     */
    public function test_payment_type_is_fillable(): void
    {
        $payment = new Payment();
        $fillable = $payment->getFillable();

        $this->assertContains('payment_type', $fillable, 'payment_type should be in the fillable array');
    }

    /**
     * Test that payment_type accepts valid values.
     */
    public function test_payment_type_accepts_valid_values(): void
    {
        $validTypes = ['installation', 'equipment', 'maintenance', 'late_fee', 'hotspot_recharge', 'other'];

        foreach ($validTypes as $type) {
            $payment = new Payment(['payment_type' => $type]);
            $this->assertEquals($type, $payment->payment_type);
        }
    }
}
