<?php

namespace Tests\Unit\Discount;

use Autepos\Discount\DiscountTypes;
use Autepos\Discount\Tests\Unit\Fixtures\BaseDiscountProcessorFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceLineFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountInstrumentFixture;
use PHPUnit\Framework\TestCase;

class DiscountProcessorWithDiscountableDeviceLineTest extends TestCase
{
    /**
     * Tear down
     */
    public function tearDown(): void
    {
    }

    // Test that discount can be calculated for discountable device lines
    // with quantity more than 1.
    public function testCalculateDiscountWithMultipleQuantity()
    {
        $quantity = 20;
        $processor = new BaseDiscountProcessorFixture();

        $discountableDeviceLine = new DiscountableDeviceLineFixture(1, 'type1', null, 1000, $quantity);

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->setDiscountableDeviceLines([$discountableDeviceLine]);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountType(DiscountTypes::AMOUNT_OFF);
        $discountInstrument->setAmountOff(100);
        $discountInstrument->setUnitQuantity(1);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);
        $expected = $discountInstrument->getAmountOff() * $quantity;
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that discount can be calculated for discountable device lines
    // with quantity more than 1 for percent_off.
    public function testCalculateDiscountWithMultipleQuantityForPercentOff()
    {
        $quantity = 2;
        $amount = 1000;
        $processor = new BaseDiscountProcessorFixture();

        $discountableDeviceLine = new DiscountableDeviceLineFixture(1, 'type1', null, $amount, $quantity);

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->setDiscountableDeviceLines([$discountableDeviceLine]);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountType(DiscountTypes::PERCENT_OFF);
        $discountInstrument->setPercentOff(50);
        $discountInstrument->setUnitQuantity(1);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);
        $expected = ($amount * $discountInstrument->getPercentOff() / 100) * $quantity;
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that discount can be calculated for discountable device lines
    // with quantity more than 1, with discount stacking.
    public function testCalculateDiscountWithMultipleQuantityAndDiscountInstruments()
    {
        $quantity = 20;
        $processor = new BaseDiscountProcessorFixture();

        $discountableDeviceLine = new DiscountableDeviceLineFixture(1, 'type1', null, 1000, $quantity);

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->setDiscountableDeviceLines([$discountableDeviceLine]);

        $discountInstrument1 = new DiscountInstrumentFixture();
        $discountInstrument1->setDiscountType(DiscountTypes::AMOUNT_OFF);
        $discountInstrument1->setAmountOff(100);
        $discountInstrument1->setUnitQuantity(1);

        $discountInstrument2 = new DiscountInstrumentFixture();
        $discountInstrument2->setDiscountType(DiscountTypes::AMOUNT_OFF);
        $discountInstrument2->setAmountOff(300);
        $discountInstrument2->setUnitQuantity(2); // To make it easy to imagine, we make the unit quantity a number that divides the quantity without a remainder.

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument1);
        $processor->addDiscountInstrument($discountInstrument2);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);
        $amount1 = $discountInstrument1->getAmountOff() * $quantity;
        $amount2 = $discountInstrument2->getAmountOff() * ($quantity / $discountInstrument2->getUnitQuantity());
        $expected = $amount1 + $amount2;
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    public function testCalculateDiscountWithMultipleQuantityAndDiscountInstrumentForPercentOff()
    {
        $quantity = 2;
        $amount = 1000;
        $processor = new BaseDiscountProcessorFixture();

        $discountableDeviceLine = new DiscountableDeviceLineFixture(1, 'type1', null, $amount, $quantity);

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->setDiscountableDeviceLines([$discountableDeviceLine]);

        $discountInstrument1 = new DiscountInstrumentFixture();
        $discountInstrument1->setDiscountType(DiscountTypes::PERCENT_OFF);
        $discountInstrument1->setPercentOff(50);
        $discountInstrument1->setUnitQuantity(1);

        $discountInstrument2 = new DiscountInstrumentFixture();
        $discountInstrument2->setDiscountType(DiscountTypes::PERCENT_OFF);
        $discountInstrument2->setPercentOff(50);
        $discountInstrument2->setUnitQuantity(1);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument1);
        $processor->addDiscountInstrument($discountInstrument2);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);
        $discount1 = ($amount * $discountInstrument1->getPercentOff() / 100) * $quantity;
        $discount2 = (($amount - ($discount1 / $quantity)) * $discountInstrument2->getPercentOff() / 100) * $quantity;
        $expected = $discount1 + $discount2;
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }
}
