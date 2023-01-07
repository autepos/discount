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
     *
     * @return int
     */
    public function subtotal(): int;
}
