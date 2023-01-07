<?php

namespace Autepos\Discount\Tests\Unit\Processors;

use Autepos\Discount\DiscountTypes;
use Autepos\Discount\Processors\LinearDiscountProcessor;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountInstrumentFixture;
use PHPUnit\Framework\TestCase;

class LinearDiscountProcessorTest extends TestCase
{
    // Test that amount_off discount is applied before percent_off discount.
    public function testAmountOffDiscountIsAppliedBeforePercentOffDiscount()
    {
        $linearDiscountProcessor = new LinearDiscountProcessor();

        $discountableDevice = new DiscountableDeviceFixture(1, null, 1000);
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

        // Calculate the discount.
        $discountLineList = $linearDiscountProcessor->calculate();

        $this->assertCount(1, $discountLineList);
        $amount1 = $discountInstrument2->getAmountOff();

        $remainder = $discountableDevice->subtotal() - $amount1;
        $amount2 = floor($remainder * $discountInstrument1->getPercentOff() / 100);
        $expected = $amount1 + $amount2;
        $this->assertEquals($expected, $discountLineList->amount());
    }
}
