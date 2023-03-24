<?php

namespace Autepos\Discount\Contracts;

/**
 * An item, such as an order  line item, that can directly
 * receive amount deduction.
 * Example candidates are an order/cart line item.
 */
interface DiscountableDeviceLine
{
    /**
     * Get the id of the discountable device line.
     */
    public function getDiscountableDeviceLineIdentifier(): ?int;

    /**
     * Get the type of the discountable device line.
     * */
    public function getDiscountableDeviceLineType(): string;

    /**
     * Returns the associated discountable
     */
    public function getDiscountable(): Discountable;

    /**
     * The quantity of a discountable device line.
     *
     * @return int
     */
    public function getDiscountableDeviceLineQuantity(): int;

    /**
     * The amount for one discountable device line.
     * This is the amount to which discount will be applied.
     *
     * @return int
     */
    public function getDiscountableDeviceLineAmount(): int;
}
