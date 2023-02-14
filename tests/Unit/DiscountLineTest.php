<?php

namespace Autepos\Discount\Tests\Unit;

use Autepos\Discount\DiscountLine;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountInstrumentFixture;
use PHPUnit\Framework\TestCase;

class DiscountLineTest extends TestCase
{
    // Test that discount line items can be grouped by discount instrument
    // and returned as discount lines.
    public function testDiscountLineItemsCanBeGroupedAsLinesByDiscountInstrument()
    {
        // Create discount line.
        $discountLine = new DiscountLine('hash', new DiscountableDeviceFixture());

        // Create  discount instruments with different ids.
        $discountInstrument1 = new DiscountInstrumentFixture(1);
        $discountInstrument2 = new DiscountInstrumentFixture(2);

        // Add the discount line items to the discount line.
        $discountLine->addItem($discountInstrument1, 20);
        $discountLine->addItem($discountInstrument1, 10);
        $discountLine->addItem($discountInstrument2, 5);

        // Get the grouped by discount instrument.
        $grouped = $discountLine->groupByDiscountInstrument();

        $this->assertCount(2, $grouped);

        // Sort grouped by discount instrument id.
        ksort($grouped);

        $this->assertEquals(30, $grouped[1]->amount());
        $this->assertEquals(5, $grouped[2]->amount());
    }

    // Test that the discount amount for a discount instrument can be retrieved.
    public function testDiscountAmountCanBeRetrievedForDiscountInstrument()
    {
        // Create discount line.
        $discountLine = new DiscountLine('hash', new DiscountableDeviceFixture());

        // Create  discount instruments with different ids.
        $discountInstrument1 = new DiscountInstrumentFixture(1);
        $discountInstrument2 = new DiscountInstrumentFixture(2);

        // Add the discount line items to the discount line.
        $discountLine->addItem($discountInstrument1, 20);
        $discountLine->addItem($discountInstrument1, 10);
        $discountLine->addItem($discountInstrument2, 5);

        //
        $this->assertEquals(30, $discountLine->amountForDiscountInstrument($discountInstrument1));
        $this->assertEquals(5, $discountLine->amountForDiscountInstrument($discountInstrument2));
    }
}
