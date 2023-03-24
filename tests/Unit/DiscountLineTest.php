<?php

namespace Autepos\Discount\Tests\Unit;

use Autepos\Discount\DiscountLine;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceLineFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountInstrumentFixture;
use PHPUnit\Framework\TestCase;

class DiscountLineTest extends TestCase
{
    // Test that discount line items can be grouped by discount instrument
    // and returned as discount lines.
    public function testDiscountLineItemsCanBeGroupedAsLinesByDiscountInstrument()
    {
        $quantity = 2;
        $amount = 1000;
        $discountableDeviceLine = new DiscountableDeviceLineFixture(1, 'type1', null, $amount, $quantity);

        // Create discount line.
        $discountLine = new DiscountLine('hash', new DiscountableDeviceFixture(), $discountableDeviceLine);

        // Create  discount instruments with different ids.
        $discountInstrument1 = new DiscountInstrumentFixture(1);
        $discountInstrument2 = new DiscountInstrumentFixture(2);

        // Add the discount line items to the discount line.
        $discountLineAgent = array_values($discountLine->selectAgents())[0];
        $discountLineAgent->addItem($discountInstrument1, 20);

        $discountLineAgent = array_values($discountLine->selectAgents())[0];
        $discountLineAgent->addItem($discountInstrument1, 10);

        $discountLineAgent = array_values($discountLine->selectAgents())[0];
        $discountLineAgent->addItem($discountInstrument2, 5);

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
        $quantity = 1;
        $amount = 1000;
        $discountableDeviceLine = new DiscountableDeviceLineFixture(1, 'type1', null, $amount, $quantity);

        // Create discount line.
        $discountLine = new DiscountLine('hash', new DiscountableDeviceFixture(), $discountableDeviceLine);

        // Create  discount instruments with different ids.
        $discountInstrument1 = new DiscountInstrumentFixture(1);
        $discountInstrument2 = new DiscountInstrumentFixture(2);

        // Add the discount line items to the discount line.
        $discountLineAgent = $discountLine->selectAgents()[0];
        $discountLineAgent->addItem($discountInstrument1, 20);
        $discountLineAgent->addItem($discountInstrument1, 10);
        $discountLineAgent->addItem($discountInstrument2, 5);

        //
        $this->assertEquals(30, $discountLine->amountForDiscountInstrument($discountInstrument1));
        $this->assertEquals(5, $discountLine->amountForDiscountInstrument($discountInstrument2));
    }
}
