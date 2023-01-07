<?php

namespace Tests\Unit\Discount;

use Autepos\Discount\AmountDistributor;
use Autepos\Discount\Exceptions\AmountDistributionException;
use PHPUnit\Framework\TestCase;

class AmountDistributorTest extends TestCase
{
    // Test that a small amount is distributed using trickle-up strategy.
    public function testSmallAmountIsDistributedUsingTrickleUpStrategy()
    {
        $amount = 10;
        $capacities = [
            'A' => 10,
            'B' => 15,
            'C' => 21,
        ];
        $expected = [
            'A' => 10,
            'B' => 0,
            'C' => 0,
        ];
        $amountDistributor = new AmountDistributor();
        $this->assertEquals($expected, $amountDistributor->trickleUp($amount, $capacities)->getDistributedAmounts());
    }

    // Test that a large amount is distributed using trickle-up strategy.
    public function testLargeAmountIsDistributedUsingTrickleUpStrategy()
    {
        $amount = 30;
        $capacities = [
            'A' => 10,
            'B' => 15,
            'C' => 21,
        ];
        $expected = [
            'A' => 10,
            'B' => 15,
            'C' => 5,
        ];
        $amountDistributor = new AmountDistributor();
        $this->assertEquals($expected, $amountDistributor->trickleUp($amount, $capacities)->getDistributedAmounts());
    }

    // Test that a small amount is distributed using trickle-down strategy.
    public function testSmallAmountIsDistributedUsingTrickleDownStrategy()
    {
        $amount = 10;
        $capacities = [
            'A' => 10,
            'B' => 15,
            'C' => 21,
        ];
        $expected = [
            'A' => 0,
            'B' => 0,
            'C' => 10,
        ];
        $amountDistributor = new AmountDistributor();
        $this->assertEquals($expected, $amountDistributor->trickleDown($amount, $capacities)->getDistributedAmounts());
    }

    // Test that a large amount is distributed using trickle-down strategy.
    public function testLargeAmountIsDistributedUsingTrickleDownStrategy()
    {
        $amount = 37;
        $capacities = [
            'A' => 10,
            'B' => 15,
            'C' => 21,
        ];
        $expected = [
            'A' => 1,
            'B' => 15,
            'C' => 21,
        ];
        $amountDistributor = new AmountDistributor();
        $this->assertEquals($expected, $amountDistributor->trickleDown($amount, $capacities)->getDistributedAmounts());
    }

    // Test that an exception is thrown when the amount to be distributed is greater than the total capacity.
    public function testExceptionIsThrownWhenAmountToBeDistributedIsGreaterThanTotalCapacity()
    {
        $amount = 100;
        $capacities = [
            'A' => 10,
            'B' => 15,
            'C' => 21,
        ];
        $amountDistributor = new AmountDistributor();
        $this->expectException(AmountDistributionException::class);
        $amountDistributor->trickleUp($amount, $capacities);
    }

    // Test that an amount can be distributed when some capacities are zero.
    public function testAmountCanBeDistributedWhenSomeCapacitiesAreZero()
    {
        $amount = 10;
        $capacities = [
            'A' => 10,
            'B' => 0,
            'C' => 21,
        ];
        $expected = [
            'A' => 10,
            'B' => 0,
            'C' => 0,
        ];
        $amountDistributor = new AmountDistributor();
        $this->assertEquals($expected, $amountDistributor->trickleUp($amount, $capacities)->getDistributedAmounts());
    }

    // Test that a zero amount is distributed.
    public function testZeroAmountIsDistributed()
    {
        $amount = 0;
        $capacities = [
            'A' => 10,
            'B' => 15,
            'C' => 21,
        ];
        $expected = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
        ];
        $amountDistributor = new AmountDistributor();
        $this->assertEquals($expected, $amountDistributor->trickleUp($amount, $capacities)->getDistributedAmounts());
    }

    // Test that an exception is thrown when the amount to be distributed is less than zero.
    public function testExceptionIsThrownWhenAmountToBeDistributedIsLessThanZero()
    {
        $amount = -100;
        $capacities = [
            'A' => 10,
            'B' => 15,
            'C' => 21,
        ];
        $amountDistributor = new AmountDistributor();
        $this->expectException(AmountDistributionException::class);
        $amountDistributor->trickleUp($amount, $capacities);
    }

    // Test that an amount is shared with a remainder.
    public function testAmountIsShared()
    {
        $amount = 10;
        $beneficiaries = 3;
        $expected = [3, 3, 3];
        $expected_remainder = 1;
        $amountDistributor = new AmountDistributor();
        $this->assertEquals($expected, $amountDistributor->share($amount, $beneficiaries)->getDistributedAmounts());

        $this->assertEquals($expected_remainder, $amountDistributor->getRemainder());
    }

    // Test that an amount is shared without a remainder.
    public function testAmountIsSharedWithoutRemainder()
    {
        $amount = 10;
        $beneficiaries = 2;
        $expected = [5, 5];
        $expected_remainder = 0;
        $amountDistributor = new AmountDistributor();
        $this->assertEquals($expected, $amountDistributor->share($amount, $beneficiaries)->getDistributedAmounts());

        $this->assertEquals($expected_remainder, $amountDistributor->getRemainder());
    }

    // Test that a negative amount is shared with a remainder.
    public function testNegativeAmountIsShared()
    {
        $amount = -10;
        $beneficiaries = 3;
        $expected = [-3, -3, -3];
        $expected_remainder = -1;
        $amountDistributor = new AmountDistributor();
        $this->assertEquals($expected, $amountDistributor->share($amount, $beneficiaries)->getDistributedAmounts());

        $this->assertEquals($expected_remainder, $amountDistributor->getRemainder());
    }

    // Test that a negative amount is shared without a remainder.
    public function testNegativeAmountIsSharedWithoutRemainder()
    {
        $amount = -10;
        $beneficiaries = 2;
        $expected = [-5, -5];
        $expected_remainder = 0;
        $amountDistributor = new AmountDistributor();
        $this->assertEquals($expected, $amountDistributor->share($amount, $beneficiaries)->getDistributedAmounts());

        $this->assertEquals($expected_remainder, $amountDistributor->getRemainder());
    }
}
