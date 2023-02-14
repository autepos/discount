<?php

namespace Autepos\Discount\Contracts;

use Autepos\Discount\DiscountLineItem;

interface DiscountInstrument
{
    /**
     * Return the type of the instrument.
     *
     * @return string
     */
    public function getDiscountInstrumentType(): string;

    /**
     * Get the id of the instrument
     */
    public function getDiscountInstrumentIdentifier(): mixed;

    /**
     * Get the discountables
     *
     * @return array<int,Discountable>
     */
    public function getDiscountables(): array;

    /**
     * Return the type of discount.
     * For accepted types @see \Autepos\Discount\DiscountTypes
     *
     * @return string
     */
    public function getDiscountType(): string;

    /**
     * Get the name of the discount
     *
     * @return string
     */
    public function getDiscountName(): string;

    /**
     * Get the Absolute amount to be taken off. This has precedence percentage amount.
     *
     * @return int
     */
    public function getAmountOff(): int;

    /**
     * Get the percent amount to be taken off e.g. 25%
     *
     * @return float
     */
    public function getPercentOff(): float;

    /**
     * Check if discount instrument is valid
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Check if discount instrument is redeemable
     */
    public function isRedeemable(string|int $user_id = null, int $order_id = null): bool;

    /**
     * Check if discount instrument has expired
     *
     * @return bool
     */
    public function hasExpired(): bool;

    /**
     * Get the minimum amount the instrument is applicable to
     *
     * @return int
     */
    public function getRestrictionsMinimumAmount(): int;

    /**
     * Get the minimum quantity required.
     * E.g. if the minimum quantity is 6, then the discount will be applied only if
     * there are at least 6 eligible items.
     * */
    public function getMinQuantity(): int;

    /**
     * Get the maximum quantity.
     * E.g. if the maximum quantity is 6, then the discount will be applied only
     * to 6 of all eligible items.
     *
     * @return int|null If null, then there is no maximum quantity.
     */
    public function getMaxQuantity(): int|null;

    /**
     * Get the quantity of items that will together as a unit received one discount.
     *
     * E.g. If there are 7 eligible items, and the unit quantity is 3, then 3 items will
     * be discounted as one unit. This means that 2 units (i.e intdiv(7/3)) will be
     * discounted and 1 item (i.e 7%3) will not be discounted.
     *
     * @return int|null If null, then the discount is applied to all eligible items as a unit.
     */
    public function getUnitQuantity(): int|null;

    /**
     * Get the quantity of item that is given for free.
     * E.g. if the unit quantity is 3, then 1 item is free, for a buy 3 for
     * the price of 2 discount.
     */
    public function getFreeQuantity(): int;

    /**
     * Get the actual amount that should be paid.
     * E.g. if the unit quantity is 3, then p2500 needs to be paid, for a discount of
     * buy 3 for £25.
     */
    public function getPrice(): int;

    public function redeem(DiscountLineItem $discountLineItem): bool;
}
