<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\CreditApplicationController;

class CreditApplicationControllerTest extends TestCase
{
    private CreditApplicationController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new CreditApplicationController();
    }

    public function test_amortization_calculation_basic()
    {
        // Test case with simple numbers
        $price = 1000;  // $1000 loan
        $rate = 2;      // 2% monthly interest rate
        $term = 12;     // 12 months

        $result = $this->controller->amortization($price, $rate, $term);

        // Assert the basic structure of the response
        $this->assertIsArray($result);
        $this->assertArrayHasKey('valor_credito', $result);
        $this->assertArrayHasKey('tasa_interes', $result);
        $this->assertArrayHasKey('plazo', $result);
        $this->assertArrayHasKey('tabla_amortizacion', $result);
        $this->assertArrayHasKey('total_intereses', $result);
        $this->assertArrayHasKey('total_cuotas', $result);
        $this->assertArrayHasKey('total_pagado', $result);

        // Verify input values are correctly stored
        $this->assertEquals($price, $result['valor_credito']);
        $this->assertEquals($rate, $result['tasa_interes']);
        $this->assertEquals($term, $result['plazo']);

        // Verify amortization table structure
        $this->assertCount($term, $result['tabla_amortizacion']);
        
        // Check first payment
        $firstPayment = $result['tabla_amortizacion'][0];
        $this->assertEquals(1, $firstPayment['periodo']);
        $this->assertEquals($price, $firstPayment['saldo_inicial']);
    }

    public function test_amortization_with_zero_interest()
    {
        $price = 1200;
        $rate = 0;
        $term = 12;

        $result = $this->controller->amortization($price, $rate, $term);

        // With 0% interest, monthly payment should be exactly price/term
        $expectedMonthlyPayment = $price / $term;
        $this->assertEquals($expectedMonthlyPayment, $result['tabla_amortizacion'][0]['valor_cuota']);
        
        // Total paid should equal the original price
        $this->assertEquals($price, $result['total_pagado']);
        
        // Total interest should be 0
        $this->assertEquals(0, $result['total_intereses']);
    }

    public function test_amortization_total_paid_greater_than_principal()
    {
        $price = 1000;
        $rate = 5;      // 5% monthly interest
        $term = 12;

        $result = $this->controller->amortization($price, $rate, $term);

        // With interest, total paid should be greater than principal
        $this->assertGreaterThan($price, $result['total_pagado']);
        
        // Total interest should be positive
        $this->assertGreaterThan(0, $result['total_intereses']);
    }

    public function test_amortization_decreasing_balance()
    {
        $price = 1000;
        $rate = 2;
        $term = 12;

        $result = $this->controller->amortization($price, $rate, $term);

        $previousBalance = $price;
        
        // Check that the balance decreases with each payment
        foreach ($result['tabla_amortizacion'] as $payment) {
            $this->assertLessThanOrEqual($previousBalance, $payment['saldo_inicial']);
            $previousBalance = $payment['saldo_inicial'];
        }
    }
} 