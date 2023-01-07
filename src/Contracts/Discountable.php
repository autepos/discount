<?php

namespace Autepos\Discount\Contracts;

/**
 * An item to which discount can be applied.
 */
interface Discountable
{
    /**
     * Get the id of the discountable.    */
    public function getDiscountableIdentifier(): ?int;

    /**
     * Get the type of the discountable.    */
    public function getDiscountableType(): string;

    /**
     * The subtotal of a discountable Item
     *
     * @return int
     */
    public function getPrice(): int;
}
