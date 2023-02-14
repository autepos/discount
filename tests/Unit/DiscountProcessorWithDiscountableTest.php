<?php

namespace Tests\Unit\Discount;

use Autepos\Discount\DiscountLineItem;
use Autepos\Discount\DiscountTypes;
use Autepos\Discount\Tests\Unit\Fixtures\BaseDiscountProcessorFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountInstrumentFixture;
use Mockery;
use PHPUnit\Framework\TestCase;

class DiscountProcessorWithDiscountableTest extends TestCase
{
    /**
     * Tear down
     */
    public function tearDown(): void
    {
    }

    // Test that amount_off discount can be calculated for discount
    // instrument with discountable.
    public function testCalculateAmountOffDiscount()
    {
        $processor = new BaseDiscountProcessorFixture();

        $discountable = new DiscountableFixture();

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables([$discountable]);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables([$discountable]);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);
        $expected = $discountInstrument->getAmountOff();
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that percent_off discount can be calculated for discount
    // instrument with discountable.
    public function testCalculatePercentOffDiscount()
    {
        $processor = new BaseDiscountProcessorFixture();

        $discountable = new DiscountableFixture();

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables([$discountable]);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables([$discountable]);
        $discountInstrument->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument->setPercentOff(10);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);
        $expected = $discountable->getPrice() * $discountInstrument->getPercentOff() / 100;
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that amount_off discount can be calculated for discount
    // instrument with multiple discountable.
    public function testCalculateAmountOffDiscountWithMultipleDiscountable()
    {
        $processor = new BaseDiscountProcessorFixture();

        $discountables = [
            new DiscountableFixture(1),
            new DiscountableFixture(2),
        ];

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables($discountables);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables($discountables);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(2, $discountLineList);
        $amount1 = $discountInstrument->getAmountOff();
        $amount2 = $discountInstrument->getAmountOff();
        $expected = $amount1 + $amount2;

        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that percent_off discount can be calculated for discount instrument
    // with multiple discountable.
    public function testCalculatePercentOffDiscountWithMultipleDiscountable()
    {
        $processor = new BaseDiscountProcessorFixture();

        $discountables = [
            new DiscountableFixture(1),
            new DiscountableFixture(2),
        ];

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables($discountables);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables($discountables);
        $discountInstrument->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument->setPercentOff(10);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(2, $discountLineList);
        $amount1 = $discountables[0]->getPrice() * $discountInstrument->getPercentOff() / 100;
        $amount2 = $discountables[1]->getPrice() * $discountInstrument->getPercentOff() / 100;
        $expected = $amount1 + $amount2;
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that discount is applied only if discount instrument's
    // discountable matches the discountable of the discountable device.
    public function testCalculateDiscountWithNoMatchingDiscountable()
    {
        $processor = new BaseDiscountProcessorFixture();

        $discountable1 = new DiscountableFixture(1);
        $discountable2 = new DiscountableFixture(2);

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables([$discountable1]);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables([$discountable2]);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(0, $discountLineList->filter());
    }

    // Test that discount is applied when some of the discountables of
    // the discount instrument matches at least one of the discountables
    // of the discountable device.
    public function testCalculateDiscountWithSomeMatchingDiscountable()
    {
        $processor = new BaseDiscountProcessorFixture();

        $discountable1 = new DiscountableFixture(1);
        $discountable2 = new DiscountableFixture(2);

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables([$discountable1, $discountable2]);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables([$discountable1]);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList->filter());
    }

    // Test that discount is applied when some of the discountables of
    // the discount instrument matches some of the discountables
    // of the discountable device.
    public function testCalculateDiscountWithSomeMatchingAndMismatchingDiscountable()
    {
        $processor = new BaseDiscountProcessorFixture();

        $discountable1 = new DiscountableFixture(1);
        $discountable2 = new DiscountableFixture(2);
        $discountable3 = new DiscountableFixture(3);

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables([$discountable1, $discountable2]);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables([$discountable1, $discountable3]);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList->filter());
    }

    // Test that discount cannot be applied when below unit quantity.
    public function testCalculateDiscountWithBelowUnitQuantity()
    {
        $processor = new BaseDiscountProcessorFixture();

        $discountable1 = new DiscountableFixture(1);
        $discountable2 = new DiscountableFixture(2);

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables([$discountable1, $discountable2]);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables([$discountable1, $discountable2]);
        $discountInstrument->setUnitQuantity(3);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(0, $discountLineList->filter());
    }

    // Test that discount is applied up to max quantity.
    public function testCalculateDiscountWithMaxQuantity()
    {
        $processor = new BaseDiscountProcessorFixture();

        $discountable1 = new DiscountableFixture(1);
        $discountable2 = new DiscountableFixture(2);
        $discountable3 = new DiscountableFixture(3);
        $discountable4 = new DiscountableFixture(4);
        $discountable5 = new DiscountableFixture(5);
        $discountable6 = new DiscountableFixture(6);

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables([
            $discountable1,
            $discountable2,
            $discountable3,
            $discountable4,
            $discountable5,
            $discountable6,
        ]);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables([
            $discountable1,
            $discountable2,
            $discountable3,
            $discountable4,
            $discountable5,
            $discountable6,
        ]);
        $discountInstrument->setUnitQuantity(2);
        $discountInstrument->setMaxQuantity(2);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(4, $discountLineList->filter());
    }

    // Test that amount_off discount is applied in groups with size of unit quantity.
    public function testCalculateAmountOffDiscountWithUnitQuantity()
    {
        $processor = new BaseDiscountProcessorFixture();

        $discountable1 = new DiscountableFixture(1);
        $discountable2 = new DiscountableFixture(2);
        $discountable3 = new DiscountableFixture(3);
        $discountable4 = new DiscountableFixture(4);
        $discountables = [$discountable1, $discountable2, $discountable3, $discountable4];

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables($discountables);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setAmountOff(10);
        $discountInstrument->setDiscountables($discountables);
        $discountInstrument->setUnitQuantity(2);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(4, $discountLineList);
        $group1_amount = $discountInstrument->getAmountOff();
        $group2_amount = $discountInstrument->getAmountOff();
        $expected = $group1_amount + $group2_amount;
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that percent_off discount is applied in groups with size of unit quantity.
    public function testCalculatePercentOffDiscountWithUnitQuantity()
    {
        //
        $processor = new BaseDiscountProcessorFixture();

        $discountable1 = new DiscountableFixture(1);
        $discountable2 = new DiscountableFixture(2);
        $discountable3 = new DiscountableFixture(3);
        $discountable4 = new DiscountableFixture(4);
        $discountables = [$discountable1, $discountable2, $discountable3, $discountable4];

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables($discountables);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument->setPercentOff(10);
        $discountInstrument->setDiscountables($discountables);
        $discountInstrument->setUnitQuantity(2);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(4, $discountLineList);
        $amount1 = ($discountable2->getPrice() + $discountable1->getPrice()) * $discountInstrument->getPercentOff() / 100;
        $amount2 = ($discountable3->getPrice() + $discountable4->getPrice()) * $discountInstrument->getPercentOff() / 100;

        $expected = $amount1 + $amount2;
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);

        // NOTE: unlike amount_off,applying discount in groups with
        // size of unit quantity should not affect the discount
        // amount as long as the number of discountables is
        // divisible by unit quantity.

        // Show that for percent_off, discount amount is not affected by unit quantity as long
        // as the number of discountables is divisible by the unit quantity.
        $discountInstrument->setUnitQuantity(1);
        $discountLineList = $processor->calculate();
        $this->assertCount(4, $discountLineList);
        $actual = $discountLineList->amount();

        $this->assertEquals($expected, $actual);
    }

    // Test that amount_off discount is applied in groups with size of unit quantity
    // while excess quantity less than unit quantity is ignored.
    public function testCalculateAmountOffDiscountWithUnitQuantityAndExcessQuantity()
    {
        $processor = new BaseDiscountProcessorFixture();

        $discountable1 = new DiscountableFixture(1);
        $discountable2 = new DiscountableFixture(2);
        $discountable3 = new DiscountableFixture(3);
        $discountable4 = new DiscountableFixture(4);
        $discountable5_ignored = new DiscountableFixture(5);
        $discountables = [$discountable1, $discountable2, $discountable3, $discountable4, $discountable5_ignored];

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables($discountables);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables($discountables);
        $discountInstrument->setUnitQuantity(2);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(4, $discountLineList->filter());
        $expected = $discountInstrument->getAmountOff() * 2;
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that discount cannot be applied when below minimum quantity.
    public function testCalculateDiscountWithBelowMinimumQuantity()
    {
        $processor = new BaseDiscountProcessorFixture();

        $discountable1 = new DiscountableFixture(1);
        $discountable2 = new DiscountableFixture(2);

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables([$discountable1, $discountable2]);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables([$discountable1, $discountable2]);
        $discountInstrument->setMinQuantity(3);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(0, $discountLineList->filter());
    }

    // Test that buy_n_for_price_of_m discount is applied for a Buy2Get1Free.
    public function testCalculateBuyNForPriceOfMDiscountForBuy2Get1Free()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountable1 = new DiscountableFixture(1, null, 800);
        $discountable2 = new DiscountableFixture(2, null, 900);

        $discountables = [$discountable1, $discountable2];

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables($discountables);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables($discountables);

        // Set up a 2 for 1 discount Buy2Get1Free.
        $discountInstrument->setDiscountType(
            DiscountTypes::BUY_N_FOR_PRICE_OF_M
        );
        $discountInstrument->setUnitQuantity(2);
        $discountInstrument->setFreeQuantity(1);

        //
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(2, $discountLineList);
        $expected = $discountable1->getPrice(); //The savings is the price of the least expensive item.
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that buy_n_for_price_of_m discount is applied for a Buy3Get2Free.
    public function testCalculateBuyNForPriceOfMDiscountForBuy3Get2Free()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountable1 = new DiscountableFixture(1, null, 1000);
        $discountable2 = new DiscountableFixture(2, null, 1000);
        $discountable3 = new DiscountableFixture(3, null, 1000);

        $discountables = [$discountable1, $discountable2, $discountable3];

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables($discountables);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables($discountables);

        // Set up a 3 for 1 discount a.k.a. Buy3Get2Free.
        $discountInstrument->setDiscountType(
            DiscountTypes::BUY_N_FOR_PRICE_OF_M
        );
        $discountInstrument->setUnitQuantity(3);
        $discountInstrument->setFreeQuantity(2);

        //
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(3, $discountLineList);
        $expected = $discountable1->getPrice() * 2; //The savings is the price of the least expensive item times 2.
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that buy_n_for_price discount is applied.
    public function testCalculateBuyNForPriceDiscount()
    {
        $price = 1000;
        $processor = new BaseDiscountProcessorFixture();
        $discountable1 = new DiscountableFixture(1, null, $price);
        $discountable2 = new DiscountableFixture(2, null, $price);
        $discountable3 = new DiscountableFixture(3, null, $price);

        $discountables = [$discountable1, $discountable2, $discountable3];

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables($discountables);

        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountables($discountables);

        // Set up a 3 for $20.
        $discountInstrument->setDiscountType(
            DiscountTypes::BUY_N_FOR_PRICE
        );
        $priceToPay = 2000;
        $discountInstrument->setUnitQuantity(3);
        $discountInstrument->setPrice($priceToPay);

        //
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(3, $discountLineList);
        $expected = $discountable1->getPrice() + $discountable2->getPrice() + $discountable3->getPrice() - $priceToPay;
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that discounts are always applied to remainders.
    public function testCalculateDiscountsAreAlwaysAppliedToRemainders()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountable1 = new DiscountableFixture(1, null, 100);
        $discountable2 = new DiscountableFixture(2, null, 100);

        $discountables = [$discountable1, $discountable2];

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables($discountables);

        // Create a product specific percent_off discount
        $discountInstrument1 = new DiscountInstrumentFixture(1);
        $discountInstrument1->setDiscountables([$discountable1]);
        $discountInstrument1->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument1->setPercentOff(50);

        // Create a non-product specific percent_off discount
        $discountInstrument2 = new DiscountInstrumentFixture(2);
        $discountInstrument2->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument2->setPercentOff(50);
        $discountInstrument2->setRestrictionsMinimumAmount(0);

        //
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument(...[$discountInstrument1, $discountInstrument2]);
        $discountLineList = $processor->calculate();

        //$this->assertCount(2, $discountLineList->filter());
        $expected = 50 + 75; // And not 50+100.
        $actual = $discountLineList->amount();

        $this->assertEquals($expected, $actual);
    }

    // Test that discount instrument can be redeemed.
    public function testRedeemDiscountInstrument()
    {
        $order_id = 1;
        $user_id = 'user1';
        $admin_id = 'admin1';
        $tenant_id = 'tenant1';
        $processor = new BaseDiscountProcessorFixture();
        $processor->setOrderId($order_id);
        $processor->setUserId($user_id);
        $processor->setAdminId($admin_id);
        $processor->setTenantId($tenant_id);

        $discountable = new DiscountableFixture();

        $discountableDevice = new DiscountableDeviceFixture();
        $discountableDevice->withDiscountables([$discountable]);

        $discountInstrumentPartialMock = Mockery::mock(DiscountInstrumentFixture::class)->makePartial();

        $discountInstrumentPartialMock->setDiscountables([$discountable]);

        $discountInstrumentPartialMock->shouldReceive('getDiscountType')
            ->andReturn(DiscountTypes::AMOUNT_OFF);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrumentPartialMock);

        $discountLineList = $processor->calculate();

        //
        $expected = $discountInstrumentPartialMock->getAmountOff();
        $discountInstrumentPartialMock->shouldReceive('redeem')
            ->once()
            ->withArgs(
                function (DiscountLineItem $discountLineItem) use ($expected, $discountableDevice, $order_id, $user_id, $admin_id, $tenant_id, $processor) {
                    $this->assertEquals($expected, $discountLineItem->getAmount());
                    $this->assertEquals('1_of_1', $discountLineItem->getUnitQuantityGroup());
                    $this->assertEquals($discountableDevice, $discountLineItem->getDiscountLine()->getDiscountableDevice());
                    $this->assertEquals(
                        $discountableDevice->getDiscountableDeviceLines()[0],
                        $discountLineItem->getDiscountLine()->getDiscountableDeviceLine()
                    );
                    $this->assertEquals($order_id, $discountLineItem->getOrderId());
                    $this->assertEquals($user_id, $discountLineItem->getUserId());
                    $this->assertEquals($admin_id, $discountLineItem->getAdminId());
                    $this->assertEquals($tenant_id, $discountLineItem->getTenantId());
                    $this->assertEquals($processor->getProcessor(), $discountLineItem->getProcessor());

                    return true;
                }
            )
            ->once()
            ->andReturn(true);

        //
        $this->assertTrue($discountLineList->redeem());

        \Mockery::close();
    }
}
