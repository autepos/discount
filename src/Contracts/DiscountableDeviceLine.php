<?php

namespace Autepos\Discount\Contracts;

/**
 * An item, such as an order  line item, that can directly
 * receive amount deduction.
 */
interface DiscountableDeviceLine
{
    /**
     * Get the id.
     */
    public function getDiscountableDeviceLineIdentifier(): ?int;

    /**
     * Get the type.    */
    public function getDiscountableDeviceLineType(): string;

    /**
     * Returns the associated discountable
     */
    public function getDiscountable(): Discountable;

    /**
     * The subtotal of a discountable device.
     * This is the amount that will be discounted so should be a line item subtotal
     * before discount and tax.
     *
     * @return int
     */
    public function getDiscountableDeviceLineSubtotal(): int;
}
