<?php

namespace Autepos\Discount\Contracts;

/**
 * An item, such as an order  that can directly
 * receive amount deduction.
 * Example candidates are an order/cart.
 */
interface DiscountableDevice
{
    /**
     * Get the id.
     */
    public function getDiscountableDeviceIdentifier(): ?int;

    /**
     * Get the type.    */
    public function getDiscountableDeviceType(): string;

    /**
     * Returns the associated discountable device lines.
     *
     * @return array<int,DiscountableDeviceLine>
     */
    public function getDiscountableDeviceLines(): array;
}
