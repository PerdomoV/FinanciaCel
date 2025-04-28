<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\CreditApplicationController;

class CreditApplicationControllerTest extends TestCase
{
    private CreditApplicationController $controller;
    private const DECIMAL_PRECISION = 4;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new CreditApplicationController();
    }

    private function assertFloatEquals($expected, $actual, $message = '')
    {
        $this->assertEquals(
            round($expected, self::DECIMAL_PRECISION),
            round($actual, self::DECIMAL_PRECISION),
            $message
        );
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
        $this->assertArrayHasKey('total_abono_capital', $result);

        // Verify input values are correctly stored
        $this->assertEquals($price, $result['valor_credito']);
        $this->assertEquals($rate, $result['tasa_interes']);
        $this->assertEquals($term, $result['plazo']);

        // Calculate expected values based on the new formula
        $expectedInstallment = $price * (1 + $rate/100 * $term) / $term;
        $expectedTotalInterest = $price * ($rate/100) * $term;
        $expectedTotalPayment = $price * (1 + $rate/100 * $term);

        // Verify amortization table structure and first payment
        $this->assertCount($term, $result['tabla_amortizacion']);
        $firstPayment = $result['tabla_amortizacion'][0];
        $this->assertEquals(1, $firstPayment['periodo']);
        $this->assertEquals($price, $firstPayment['saldo_inicial']);
        $this->assertFloatEquals($expectedInstallment, $firstPayment['valor_cuota']);
        
        // Verify totals
        $this->assertFloatEquals($expectedTotalInterest, $result['total_intereses']);
        $this->assertFloatEquals($expectedTotalPayment, $result['total_cuotas']);
        $this->assertFloatEquals($price, $result['total_abono_capital']);
    }

    public function test_amortization_with_zero_interest()
    {
        $price = 1200;
        $rate = 0;
        $term = 12;

        $result = $this->controller->amortization($price, $rate, $term);

        // With 0% interest, monthly payment should be exactly price/term
        $expectedMonthlyPayment = $price / $term;
        $this->assertFloatEquals($expectedMonthlyPayment, $result['tabla_amortizacion'][0]['valor_cuota']);
        
        // Total paid should equal the original price
        $this->assertFloatEquals($price, $result['total_cuotas']);
        
        // Total interest should be 0
        $this->assertFloatEquals(0, $result['total_intereses']);
        
        // Total capital payment should equal the price
        $this->assertFloatEquals($price, $result['total_abono_capital']);
    }

    public function test_amortization_total_paid_calculation()
    {
        $price = 1000;
        $rate = 5;      // 5% monthly interest rate
        $term = 12;

        $result = $this->controller->amortization($price, $rate, $term);

        // Calculate expected values
        $expectedTotalPayment = $price * (1 + $rate/100 * $term);
        $expectedTotalInterest = $price * ($rate/100) * $term;

        // Verify total payment
        $this->assertFloatEquals($expectedTotalPayment, $result['total_cuotas']);
        
        // Verify total interest
        $this->assertFloatEquals($expectedTotalInterest, $result['total_intereses']);
        
        // Verify total capital payment equals original price
        $this->assertFloatEquals($price, $result['total_abono_capital']);
    }

    public function test_amortization_constant_installment()
    {
        $price = 1000;
        $rate = 2;
        $term = 12;

        $result = $this->controller->amortization($price, $rate, $term);

        // Calculate expected constant installment
        $expectedInstallment = $price * (1 + $rate/100 * $term) / $term;
        
        // Verify all installments are equal
        foreach ($result['tabla_amortizacion'] as $payment) {
            $this->assertFloatEquals($expectedInstallment, $payment['valor_cuota']);
        }
    }

    public function test_amortization_interest_calculation()
    {
        $price = 1000;
        $rate = 2;
        $term = 12;

        $result = $this->controller->amortization($price, $rate, $term);

        // Expected monthly interest is constant (price * rate/100)
        $expectedMonthlyInterest = $price * $rate/100;
        
        // Verify interest calculation for each period
        foreach ($result['tabla_amortizacion'] as $payment) {
            $this->assertFloatEquals($expectedMonthlyInterest, $payment['valor_interes']);
        }
    }
} 