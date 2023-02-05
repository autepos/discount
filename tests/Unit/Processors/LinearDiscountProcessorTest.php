<?php

namespace Autepos\Discount\Tests\Unit\Processors;

use PHPUnit\Framework\TestCase;
use Autepos\Discount\DiscountTypes;
use Autepos\Discount\Processors\LinearDiscountProcessor;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountInstrumentFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceLineFixture;

class LinearDiscountProcessorTest extends TestCase
{
    // Test that amount_off discount is applied before percent_off discount.
    public function testAmountOffDiscountIsAppliedBeforePercentOffDiscount()
    {
        $linearDiscountProcessor = new LinearDiscountProcessor();

        $subtotal=1000;
        $discountableDevice = (new DiscountableDeviceFixture(1))
        ->setDiscountableDeviceLines(
            [new DiscountableDeviceLineFixture(1, null, null, $subtotal)]
        );
        $linearDiscountProcessor->addDiscountableDevice($discountableDevice);

        // Percent off discount instrument.
        $discountInstrument1 = new DiscountInstrumentFixture();
        $discountInstrument1->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument1->setPercentOff(10);
        $linearDiscountProcessor->addDiscountInstrument($discountInstrument1);

        // Amount off discount instrument.
        $discountInstrument2 = new DiscountInstrumentFixture();
        $discountInstrument2->setAmountOff(500);
        $linearDiscountProcessor->addDiscountInstrument($discountInstrument2);

        // Another percent off discount instrument.
        $discountInstrument3 = new DiscountInstrumentFixture();
        $discountInstrument3->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument3->setPercentOff(20);
        $linearDiscountProcessor->addDiscountInstrument($discountInstrument3);

        // Another amount off discount instrument.
        $discountInstrument4 = new DiscountInstrumentFixture();
        $discountInstrument4->setAmountOff(100);
        $linearDiscountProcessor->addDiscountInstrument($discountInstrument4);
        

        // Calculate the discount.
        $discountLineList = $linearDiscountProcessor->calculate();

        $this->assertCount(1, $discountLineList);

        // 1. The amount of the discount should be the sum of the amount off
        $amount1 = $discountInstrument2->getAmountOff()+$discountInstrument4->getAmountOff();

        // 2. The percent offs are applied in the order they are added currently.
        //    So the first percent off discount added should be applied to the remainder
        $remainder = $subtotal - $amount1;
        $amount2 = floor($remainder * $discountInstrument1->getPercentOff() / 100);

        // 3. The second percent off discount should be applied to the remainder
        $remainder = $subtotal - $amount1 - $amount2;
        $amount3 = floor($remainder * $discountInstrument3->getPercentOff() / 100);


        $expected = $amount1 + $amount2 + $amount3;
        $this->assertEquals($expected, $discountLineList->amount());
    }
}
