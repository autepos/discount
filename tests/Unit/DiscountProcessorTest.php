<?php

namespace Tests\Unit\Discount;

use Autepos\Discount\DiscountLineItem;
use Autepos\Discount\DiscountTypes;
use Autepos\Discount\Tests\Unit\Fixtures\BaseDiscountProcessorFixture;
use Autepos\Discount\Tests\Unit\Fixtures\DiscountableDeviceFixture;
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
        $discountableDevice = new DiscountableDeviceFixture();
        $discountInstrument = new DiscountInstrumentFixture();
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);
        $discountableDevice->subtotal();
        $this->assertEquals($discountInstrument->getAmountOff(), $discountLineList->amount());
    }

    // Test that percent_off discount can be calculated
    public function testCalculatePercentOffDiscount()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture();
        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument->setPercentOff(10);
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);
        $expected = $discountInstrument->getPercentOff() * $discountableDevice->subtotal() / 100;
        $actual = $discountLineList->amount();
        $this->assertEquals($expected, $actual);
    }

    // Test that discount can be calculated for multiple discountable devices
    public function testCalculateMultipleDiscountableDevices()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice1 = new DiscountableDeviceFixture(1);
        $discountableDevice2 = new DiscountableDeviceFixture(2);
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
        $expected = $discountInstrument->getPercentOff() * ($discountableDevice1->subtotal() + $discountableDevice2->subtotal()) / 100;

        $actual = $discountLineList->amount();

        $this->assertEquals($expected, $actual);
    }

    // Test that discount can be calculated for multiple discount instruments
    public function testCalculateMultipleDiscountInstruments()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture();
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
        $remainder = $discountableDevice->subtotal() - $amount1;
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
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice1 = new DiscountableDeviceFixture(1);
        $discountableDevice2 = new DiscountableDeviceFixture(2);
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
        $remainder = $discountableDevice1->subtotal() - $amount1;
        $amount2 = ($discountInstrument2->getPercentOff() * $remainder) / 100;
        $expected1 = $amount1 + $amount2;

        // Calculate expected discount for second discountable device
        $amount1 = $discountInstrument1->getAmountOff();
        $remainder = $discountableDevice2->subtotal() - $amount1;
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
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture();
        $discountInstrument = new DiscountInstrumentFixture();
        $discountInstrument->setDiscountType(
            DiscountTypes::PERCENT_OFF
        );
        $discountInstrument->setPercentOff(10);
        $discountInstrument->setRestrictionsMinimumAmount($discountableDevice->subtotal() + 1);
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(0, $discountLineList);
    }

    // Test that discount cannot be applied with an expired discount instrument.
    public function testCalculateExpiredDiscountInstrument()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture();
        $discountInstrument = new DiscountInstrumentFixture();

        $discountInstrument->setHasExpired(true);
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

    // Test that discount cannot be applied with a discount instrument that is not active.
    public function testCalculateNotActiveDiscountInstrument()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture();
        $discountInstrument = new DiscountInstrumentFixture();

        $discountInstrument->setIsActive(false);
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(0, $discountLineList);
    }

    // Test that discount amount is less than or equal to discountable device subtotal.
    public function testCalculateDiscountAmountLessThanOrEqualToDiscountableDeviceSubtotal()
    {
        $processor = new BaseDiscountProcessorFixture();
        $discountableDevice = new DiscountableDeviceFixture();
        $discountInstrument = new DiscountInstrumentFixture();

        $discountInstrument->setAmountOff($discountableDevice->subtotal() + 1);
        $processor->addDiscountableDevice($discountableDevice);
        $processor->addDiscountInstrument($discountInstrument);
        $discountLineList = $processor->calculate();

        $this->assertCount(1, $discountLineList);
        $this->assertEquals($discountableDevice->subtotal(), $discountLineList->amount());
    }

    // Test that discount instrument can be redeemed.
    public function testRedeemDiscountInstrument()
    {
        $order_id = 1;
        $user_id = 'user1';
        $admin_id = 'admin1';
        $processor = new BaseDiscountProcessorFixture();
        $processor->setOrderId($order_id);
        $processor->setUserId($user_id);
        $processor->setAdminId($admin_id);

        $discountableDevice = new DiscountableDeviceFixture();

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
                function (DiscountLineItem $discountLineItem) use ($expected, $discountableDevice, $order_id, $user_id, $admin_id, $processor) {
                    $this->assertEquals($expected, $discountLineItem->getAmount());
                    $this->assertEquals('none', $discountLineItem->getUnitQuantityGroup());
                    $this->assertEquals($discountableDevice, $discountLineItem->getDiscountLine()->getDiscountableDevice());
                    $this->assertNull($discountLineItem->getDiscountLine()->getDiscountableDeviceLine());
                    $this->assertEquals($order_id, $discountLineItem->getOrderId());
                    $this->assertEquals($user_id, $discountLineItem->getUserId());
                    $this->assertEquals($admin_id, $discountLineItem->getAdminId());
                    $this->assertEquals($processor->getProcessor(), $discountLineItem->getProcessor());

                    return true;
                }
            )
            ->once()
            ->andReturn(true);
        //
        $this->assertTrue($discountLineList->redeem());
    }
}
