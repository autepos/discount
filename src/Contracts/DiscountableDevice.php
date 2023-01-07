<?php

namespace Autepos\Discount\Contracts;

/**
 * An item, such as an order  that can directly
 * receive amount deduction.
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
     * @param  Discountable|null  $discountable When not null only the items that has the given discountable are returned.
     * @return array<int,DiscountableDeviceLine>
     */
    public function getDiscountableDeviceLines(?Discountable $discountable = null): array;

    /**
     * The subtotal of a discountable device.
     *
     * @return int
     */
    public function subtotal(): int;
}
