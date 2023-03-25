<?php

namespace Tests\Unit\Discount;

use Autepos\Discount\DiscountLineItem;
use Autepos\Discount\DiscountTypes;
use Autepos\Discount\Tests\Unit\Fixtures\BaseDiscountProcessorFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceLineFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountInstrumentFixture;
use Mockery;
use PHPUnit\Framework\TestCase;

class DiscountProcessorTest extends TestCase
{
    // Test that discount instrument can be added to the processor
    public function testAddDiscountInstrument()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountInstrument = new DiscountInstrumentFixture();
        $processor->addDiscountInstrument($discountInstrument);

        $discountInstruments = $processor->getDiscountInstruments();
        $this->assertCount(1, $discountInstruments);
        $this->assertEquals($discountInstrument, $discountInstruments[0]);
    }

    // Test that discountable device can be added to the processor
    public function testAddDiscountableDevice()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture();
        $processor->addDiscountableDevice($discountableDevice);

        $discountableDevices = $processor->getDiscountableDevices();
        $this->assertCount(1, $discountableDevices);
        $this->assertEquals($discountableDevice, $discountableDevices[0]);
    }

    // Test that order id can be set
    public function testSetOrderId()
    {
        $processor = new BaseDiscountProcessorFixture();
        $processor->setOrderId(1);
        $this->assertEquals(1, $processor->getOrderId());
    }

    // Test that user id can be set
    public function testSetUserId()
    {
        $processor = new BaseDiscountProcessorFixture();
        $processor->setUserId(1);
        $this->assertEquals(1, $processor->getUserId());
    }

    // Test that admin id can be set
    public function testSetAdminId()
    {
        $processor = new BaseDiscountProcessorFixture();
        $processor->setAdminId(1);
        $this->assertEquals(1, $processor->getAdminId());
    }

    // Test that tenant id can be set
    public function testSetTenantId()
    {
        $processor = new BaseDiscountProcessorFixture();
        $processor->setTenantId(1);
        $this->assertEquals(1, $processor->getTenantId());
    }

    // Test that processor can be reset
    public function testResetProcessor()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture();
        $discountInstrument = new DiscountInstrumentFixture();
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $processor->setOrderId(1);
        $processor->setUserId(1);
        $processor->setAdminId(1);
        $processor->reset();

        $this->assertCount(0, $processor->getDiscountableDevices());
        $this->assertCount(0, $processor->getDiscountInstruments());
        $this->assertNull($processor->getOrderId());
        $this->assertNull($processor->getUserId());
        $this->assertNull($processor->getAdminId());
    }

    // Test that amount_off discount can be calculated
    public function testCalculateAmountOffDiscount()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture(1, null, 1000);
        $discountInstrument = new DiscountInstrumentFixture();
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);

        $this->assertEquals($discountInstrument->getAmountOff(), $discountLineList->amount());
    }

    // Test that percent_off discount can be calculated
    public function testCalculatePercentOffDiscount()
    {
        $subtotal = 1000;
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture(1, null, $subtotal);
        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument->setPercentOff(10);
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);
        $expected = $discountInstrument->getPercentOff() * $subtotal / 100;
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that discount can be calculated for multiple discountable devices
    public function testCalculateMultipleDiscountableDevices()
    {
        $device1_subtotal = 1000;
        $device2_subtotal = 2000;
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice1 = new DiscountableDeviceFixture(1, null, $device1_subtotal);
        $discountableDevice2 = new DiscountableDeviceFixture(2, null, $device2_subtotal);
        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument->setPercentOff(10);
        $processor->addDiscountableDevice($discountableDevice1);
        $processor->addDiscountableDevice($discountableDevice2);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(2, $discountLineList);
        $expected = $discountInstrument->getPercentOff() * ($device1_subtotal + $device2_subtotal) / 100;

        $actual = $discountLineList->amount();

        $this->assertEquals($expected, $actual);
    }

    // Test that discount can be calculated for multiple discount instruments
    public function testCalculateMultipleDiscountInstruments()
    {
        $subtotal = 1000;
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture(1, null, $subtotal);
        $discountInstrument1 = new DiscountInstrumentFixture();

        $discountInstrument2 = new DiscountInstrumentFixture();
        $discountInstrument2->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument2->setPercentOff(10);

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument1);
        $processor->addDiscountInstrument($discountInstrument2);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);

        // Calculate expected discount
        $amount1 = $discountInstrument1->getAmountOff();
        $remainder = $subtotal - $amount1;
        $amount2 = ($discountInstrument2->getPercentOff() * $remainder) / 100;
        $expected = $amount1 + $amount2;

        // Calculate actual discount
        $actual = $discountLineList->amount();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    // Test that discount can be calculated for multiple discount instruments and multiple discountable devices
    public function testCalculateMultipleDiscountInstrumentsAndMultipleDiscountableDevices()
    {
        $device1_subtotal = 1000;
        $device2_subtotal = 2000;
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice1 = new DiscountableDeviceFixture(1, null, $device1_subtotal);
        $discountableDevice2 = new DiscountableDeviceFixture(2, null, $device2_subtotal);
        $discountInstrument1 = new DiscountInstrumentFixture(1);

        $discountInstrument2 = new DiscountInstrumentFixture(2);
        $discountInstrument2->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument2->setPercentOff(10);

        $processor->addDiscountableDevice($discountableDevice1);
        $processor->addDiscountableDevice($discountableDevice2);
        $processor->addDiscountInstrument($discountInstrument1);
        $processor->addDiscountInstrument($discountInstrument2);
        $discountLineList = $processor->calculate();

        $this->assertCount(2, $discountLineList);

        // Calculate expected discount for first discountable device
        $amount1 = $discountInstrument1->getAmountOff();
        $remainder = $device1_subtotal - $amount1;
        $amount2 = ($discountInstrument2->getPercentOff() * $remainder) / 100;
        $expected1 = $amount1 + $amount2;

        // Calculate expected discount for second discountable device
        $amount1 = $discountInstrument1->getAmountOff();
        $remainder = $device2_subtotal - $amount1;
        $amount2 = ($discountInstrument2->getPercentOff() * $remainder) / 100;
        $expected2 = $amount1 + $amount2;

        // Total expected discount
        $expected = $expected1 + $expected2;

        // Calculate actual discount
        $actual = $discountLineList->amount();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    // Test that discount cannot be applied to a discountable device with subtotal less than the minimum amount
    public function testCalculateDiscountableDeviceSubtotalLessThanMinimumAmount()
    {
        $subtotal = 1000;
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture($subtotal);
        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument->setPercentOff(10);
        $discountInstrument->setRestrictionsMinimumAmount($subtotal + 1);
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(0, $discountLineList);
    }

    // Test that discount cannot be applied with a discount instrument that is not redeemable.
    public function testCalculateNotRedeemableDiscountInstrument()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture();
        $discountInstrument = new DiscountInstrumentFixture();

        $discountInstrument->setIsRedeemable(false);
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(0, $discountLineList);
    }

    // Test that discount amount is not greater than discountable device subtotal for amount_off.
    public function testAmountoffDiscountAmountIsNotGreaterThanSubtotal()
    {
        $subtotal = 1000;
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture(1, null, $subtotal);
        $discountInstrument = new DiscountInstrumentFixture();

        $discountInstrument->setAmountOff($subtotal + 1);
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);
        $this->assertEquals($subtotal, $discountLineList->amount());
    }

    // Test that discount amount is not greater than discountable device subtotal for percent_off.
    public function testPercentoffDiscountAmountIsNotGreaterThanSubtotal()
    {
        $subtotal = 1000;
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture(1, null, $subtotal);
        $discountInstrument = new DiscountInstrumentFixture();

        $discountInstrument->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument->setPercentOff(100 + 10);
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);
        $this->assertEquals($subtotal, $discountLineList->amount());
    }

    // Test that the number of times discount is applied is limited to the number of times the discount is redeemable.
    public function testTimesDiscountCanBeAppliedIsLimitedToTimesDiscountIsRedeemable()
    {
        $quantity = 4;
        $max_times_redeemable = 3;
        $subtotal = 1000;

        $processor = new BaseDiscountProcessorFixture();

        $discountableDeviceLine = new DiscountableDeviceLineFixture(1, 'type1', null, 1000, $quantity);

        $discountableDevice = new DiscountableDeviceFixture(1, null, $subtotal);

        $discountableDevice->setDiscountableDeviceLines([$discountableDeviceLine]);

        $discountInstrumentPartialMock = Mockery::mock(DiscountInstrumentFixture::class)->makePartial();
        $discountInstrumentPartialMock->shouldReceive('getDiscountType')
            ->andReturn(DiscountTypes::AMOUNT_OFF);
        $discountInstrumentPartialMock->shouldReceive('isRedeemable')
        ->atLeast(1)
        ->andReturnUsing(function ($count) use ($max_times_redeemable) {
            return $count <= $max_times_redeemable;
        });

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrumentPartialMock);
        $discountLineList = $processor->calculate();

        $discountLine = current($discountLineList->all());
        $this->assertCount($max_times_redeemable, $discountLine->allWithAmount());
    }

    // Test that discount instrument can be redeemed.
    public function testRedeemDiscountInstrument()
    {
        $order_id = 1;
        $user_id = 'user1';
        $admin_id = 'admin1';
        $tenant_id = 'tenant1';
        $meta= ['meta1' => 'meta1'];
        $processor = new BaseDiscountProcessorFixture();
        $processor->setOrderId($order_id);
        $processor->setUserId($user_id);
        $processor->setAdminId($admin_id);
        $processor->setTenantId($tenant_id);
        $processor->setMeta($meta);

        $discountableDevice = new DiscountableDeviceFixture(1, null, 1000);

        $discountInstrumentPartialMock = Mockery::mock(DiscountInstrumentFixture::class)->makePartial();
        $discountInstrumentPartialMock->shouldReceive('getDiscountType')
            ->andReturn(DiscountTypes::AMOUNT_OFF);

        $expected = $discountInstrumentPartialMock->getAmountOff();

        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrumentPartialMock);

        $discountLineList = $processor->calculate();

        //
        $discountInstrumentPartialMock->shouldReceive('redeem')
            ->withArgs(
                function (DiscountLineItem $discountLineItem) use ($expected, $discountableDevice, $order_id, $user_id, $admin_id, $tenant_id,$meta, $processor) {
                    $this->assertEquals($expected, $discountLineItem->getAmount());
                    $this->assertEquals('1_of_1', $discountLineItem->getUnitQuantityGroup());
                    $this->assertEquals(1, $discountLineItem->getUnitQuantityGroupNumber());
                    $this->assertEquals($discountableDevice, $discountLineItem->getDiscountLine()->getDiscountableDevice());
                    $this->assertEquals(
                        $discountableDevice->getDiscountableDeviceLines()[0],
                        $discountLineItem->getDiscountLine()->getDiscountableDeviceLine()
                    );
                    $this->assertEquals($order_id, $discountLineItem->getOrderId());
                    $this->assertEquals($user_id, $discountLineItem->getUserId());
                    $this->assertEquals($admin_id, $discountLineItem->getAdminId());
                    $this->assertEquals($tenant_id, $discountLineItem->getTenantId());
                    $this->assertEquals($meta, $discountLineItem->getMeta());
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
