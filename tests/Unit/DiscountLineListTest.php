<?php

namespace Autepos\Discount\Tests\Unit;

use Autepos\Discount\DiscountLine;
use Autepos\Discount\DiscountLineList;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceLineFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountInstrumentFixture;
use Mockery;
use PHPUnit\Framework\TestCase;

class DiscountLineListTest extends TestCase
{
    // method to run after each test.
    public function tearDown(): void
    {
        Mockery::close();
    }

    // Test that existing discount lines can be retrieved by hash.
    public function testExistingDiscountLinesCanBeRetrievedByHash()
    {
        // Create a discount line list.
        $discountLineList = new DiscountLineList();

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDeviceLine1 = new DiscountableDeviceLineFixture(1);

        // Create discount line.
        $discountLine1 = DiscountLine::constructFrom($discountableDevice, $discountableDeviceLine1);

        // Add the discount lines to the list.
        $discountLineList->add($discountLine1);

        // Assert that the discount line can be found.
        $this->assertEquals($discountLine1, $discountLineList->get($discountLine1->getHash()));
    }

    // Test that existing discount lines can be found.
    public function testExistingDiscountLinesCanBeFound()
    {
        // Create a discount line list.
        $discountLineList = new DiscountLineList;

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDeviceLine1 = new DiscountableDeviceLineFixture(1);

        // Create discount line.
        $discountLine1 = DiscountLine::constructFrom($discountableDevice, $discountableDeviceLine1);

        // Add the discount lines to the list.
        $discountLineList->add($discountLine1);

        // Assert that the discount line can be found.
        $this->assertEquals($discountLine1, $discountLineList->find($discountableDevice, $discountableDeviceLine1));
    }

        // Test that nonexisting discount lines cannot be found.
        public function testNonexistingDiscountLinesCannotBeFound()
        {
            // Create a discount line list.
            $discountLineList = new DiscountLineList;

            $discountableDevice = new DiscountableDeviceFixture();
            $discountableDeviceLine1 = new DiscountableDeviceLineFixture(1);

            // Assert that the discount line can be found.
            $this->assertNull($discountLineList->find($discountableDevice, $discountableDeviceLine1));
        }

    /**
     * Create a discount line list.
     */
    private function createDiscountLineList(): DiscountLineList
    {
        // Create first discount line.
        $discountLine1 = new DiscountLine('hash1', new DiscountableDeviceFixture());

        // Make the discount line an instance mock.
        $discountLine1 = Mockery::mock($discountLine1);

        // Create  discount instruments with different ids.
        $discountInstrument1 = new DiscountInstrumentFixture(1);
        $discountInstrument2 = new DiscountInstrumentFixture(2);

        // Add the discount line items to the discount line.
        $discountLine1->addItem($discountInstrument1, 20);
        $discountLine1->addItem($discountInstrument1, 10);
        $discountLine1->addItem($discountInstrument2, 5);

        // Get the grouped by discount instrument.
        $grouped1 = $discountLine1->groupByDiscountInstrument();

        // Mock the groupByDiscountInstrument method to return grouped as result.
        $discountLine1->shouldReceive('groupByDiscountInstrument')
        ->once()
        ->andReturn($grouped1);

        // Create a second discount line.
        $discountLine2 = new DiscountLine('hash2', new DiscountableDeviceFixture());

        // Make the discount line an instance mock.
        $discountLine2 = Mockery::mock($discountLine2);

        // Create additional discount instrument.
        $discountInstrument3 = new DiscountInstrumentFixture(3);

        // Add the discount line items to the discount line.
        $discountLine2->addItem($discountInstrument1, 4);
        $discountLine2->addItem($discountInstrument2, 3);
        $discountLine2->addItem($discountInstrument3, 2);

        // Get the grouped by discount instrument.
        $grouped2 = $discountLine2->groupByDiscountInstrument();

        // Mock the groupByDiscountInstrument method to return grouped as result.
        $discountLine2->shouldReceive('groupByDiscountInstrument')
        ->once()
        ->andReturn($grouped2);

        // Create a discount line list.
        $discountLineList = new DiscountLineList();

        // Add the discount lines to the list.
        $discountLineList->add($discountLine1);
        $discountLineList->add($discountLine2);

        return $discountLineList;
    }

    // Test that discount line list items can be grouped by discount instrument.
    /**
     * @return array @see \Autepos\Discount\DiscountLineList::groupByDiscountInstrumentAsArray()
     */
    public function testGroupByDiscountInstrument(): array
    {
        // Get the grouped by discount instrument.
        $grouped = $this->createDiscountLineList()->groupByDiscountInstrumentAsArray();

        // Assert that the grouped by discount instrument has 3 items.
        $this->assertCount(3, $grouped);

        // Sort grouped by discount instrument id.
        ksort($grouped);

        // Check discount instrument 1.
        $grouped1_amount = 0;
        foreach ($grouped[1] as $discountLine) {
            $grouped1_amount += $discountLine->amount();
        }
        $this->assertEquals(34, $grouped1_amount);

        // Check discount instrument 2.
        $grouped2_amount = 0;
        foreach ($grouped[2] as $discountLine) {
            $grouped2_amount += $discountLine->amount();
        }
        $this->assertEquals(8, $grouped2_amount);

        // Check discount instrument 3.
        $grouped3_amount = 0;
        foreach ($grouped[3] as $discountLine) {
            $grouped3_amount += $discountLine->amount();
        }
        $this->assertEquals(2, $grouped3_amount);

        //
        return $grouped;
    }

        // Test that discount line list items can be grouped by discount instrument.
        /**
         * @depends testGroupByDiscountInstrument
         */
        public function testGroupAmountByDiscountInstrument(array $grouped)
        {
            // Create a discount line list.
            $discountLineList = Mockery::mock(DiscountLineList::class)->makePartial();
            $discountLineList->shouldReceive('groupByDiscountInstrumentAsArray')
            ->once()
            ->andReturn($grouped);

            $grouped_amount = $discountLineList->groupAmountByDiscountInstrument();

            $this->assertCount(3, $grouped_amount);

            // Sort grouped by discount instrument id.
            ksort($grouped_amount);

            // Check discount instrument 1.
            $this->assertEquals(34, $grouped_amount[1]);

            // Check discount instrument 2.
            $this->assertEquals(8, $grouped_amount[2]);

            // Check discount instrument 3.
            $this->assertEquals(2, $grouped_amount[3]);
        }

    // Test that the discount amount for a discount instrument can be retrieved.
    public function testDiscountAmountCanBeRetrievedForDiscountInstrument()
    {
        // Create a discount line list.
        $discountLineList = new DiscountLineList;

        // Create discount instrument.
        $discountInstrument = new DiscountInstrumentFixture(1);

        // Create first discount line.
        $discountLine1 = Mockery::mock(new DiscountLine('hash1', new DiscountableDeviceFixture()));
        $discountLine1->shouldReceive('amountForDiscountInstrument')
        ->once()
                        ->with($discountInstrument)
                        ->andReturn(10);

        // Create second discount line.
        $discountLine2 = Mockery::mock(new DiscountLine('hash2', new DiscountableDeviceFixture()));
        $discountLine2->shouldReceive('amountForDiscountInstrument')
        ->once()
                        ->with($discountInstrument)
                        ->andReturn(20);

        // Create third discount line.
        $discountLine3 = Mockery::mock(new DiscountLine('hash3', new DiscountableDeviceFixture()));
        $discountLine3->shouldReceive('amountForDiscountInstrument')
        ->once()
                        ->with($discountInstrument)
                        ->andReturn(0);

        // Add the discount lines to the list.
        $discountLineList->add($discountLine1);
        $discountLineList->add($discountLine2);
        $discountLineList->add($discountLine3);

        // Get the discount amount for discount instrument 1.
        $discountAmount = $discountLineList->amountForDiscountInstrument($discountInstrument);

        // Assert that the discount amount is correct.
        $this->assertEquals(30, $discountAmount);
    }

        public function testDiscountAmountCanBeRetrievedForDiscountableDeviceAndDiscountableDeviceLine()
        {
            // Create a discount line list.
            $discountLineList = Mockery::mock(DiscountLineList::class)->makePartial();

            $discountableDevice = new DiscountableDeviceFixture();
            $discountableDeviceLine1 = new DiscountableDeviceLineFixture(1);
            $discountableDeviceLine2 = new DiscountableDeviceLineFixture(2);

            // Create discount line.
            $discountLineInstance1 = DiscountLine::constructFrom($discountableDevice, $discountableDeviceLine1);
            $discountLine1 = Mockery::mock($discountLineInstance1);
            $discountLine1->shouldReceive('amount')
                            ->once()
                            ->andReturn(10);

            // Add the discount lines to the list.
            $discountLineList->add($discountLine1);

            //
            $discountLineList->shouldReceive('find')
                            ->once()
                            ->with($discountableDevice, $discountableDeviceLine1)
                            ->andReturn($discountLine1);
            $discountLineList->shouldReceive('find')
                            ->once()
                            ->with($discountableDevice, $discountableDeviceLine2)
                            ->andReturn(null);

            // Get the discount amount for discountable device and discountable device line.
            $discountAmount1 = $discountLineList->amountFor($discountableDevice, $discountableDeviceLine1);
            $discountAmount2 = $discountLineList->amountFor($discountableDevice, $discountableDeviceLine2);

            // Assert that the discount amount is correct.
            $this->assertEquals(10, $discountAmount1);
            $this->assertEquals(0, $discountAmount2);
        }
}
