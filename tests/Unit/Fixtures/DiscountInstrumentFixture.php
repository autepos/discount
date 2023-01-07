<?php

declare(strict_types=1);

namespace Autepos\Discount\Tests\Unit\Fixtures;

use Autepos\Discount\Contracts\Discountable;
use Autepos\Discount\Contracts\DiscountInstrument;
use Autepos\Discount\DiscountLineItem;
use Autepos\Discount\DiscountTypes;

class DiscountInstrumentFixture implements DiscountInstrument
{
    /**
     * @var array<int,Discountable>
     */
    public array $discountables = [];

    // CONFIGURATION THAT ARE LIKELY FROM MODELS
    public $discount_name = 'test';

    public ?int $amount_off = 100;

    public int $percent_off = 0;

    public bool $is_active = true;

    public bool $is_redeemable = true;

    public bool $has_expired = false;

    public int $restrictions_minimum_amount = 1000;

    public int $min_quantity = 1;

    public int $unit_quantity = 1;

    public ?int $free_quantity = null;

    public ?int $price = null;

    public bool $redeem_succeeded = true;

    public function __construct(
        public int $id = 1,
        public string $discount_type = DiscountTypes::AMOUNT_OFF,
        public ?string $discount_instrument_type = null,
        ?Discountable $discountable = null
    ) {
        if (is_null($this->discount_instrument_type)) {
            $this->discount_instrument_type = get_class();
        }

        if ($discountable) {
            $this->discountables[] = $discountable;
        }
    }

    /**
     * Return the type of the instrument.
     */
    public function getDiscountInstrumentType(): string
    {
        return $this->discount_instrument_type;
    }

    /**
     * Get the id of the instrument
     */
    public function getDiscountInstrumentIdentifier(): mixed
    {
        return $this->id;
    }

    public function getDiscountables(): array
    {
        return $this->discountables;
    }

    /**
     * Set discountables
     *
     * @param  array<int,Discountable>  $discountables
     */
    public function setDiscountables(array $discountables): self
    {
        $this->discountables = $discountables;

        return $this;
    }

    /**
     * Return the type of the instrument.
     */
    public function getDiscountType(): string
    {
        return $this->discount_type;
    }

    /**
     * Get the name of the discount
     *
     * @return string
     */
    public function getDiscountName(): string
    {
        return $this->discount_name;
    }

    public function getAmountOff(): int
    {
        return $this->amount_off;
    }

    /**
     * Get the percent amount to be taken off.
     *
     * @return float
     */
    public function getPercentOff(): float
    {
        return $this->percent_off;
    }

    /**
     * Check if discount instrument is valid
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isRedeemable(string|int $user_id = null, int $order_id = null): bool
    {
        return $this->is_redeemable;
    }

    public function hasExpired(): bool
    {
        return $this->has_expired;
    }

    /**
     * Get the minimum amount the instrument is applicable to
     *
     * @return int
     */
    public function getRestrictionsMinimumAmount(): int
    {
        return $this->restrictions_minimum_amount;
    }

    public function getMinQuantity(): int
    {
        return $this->min_quantity;
    }

    public function getUnitQuantity(): int
    {
        return $this->unit_quantity;
    }

    /**
     * {@inheritDoc}
     */
    public function getFreeQuantity(): int
    {
        return $this->free_quantity;
    }

    /**
     * {@inheritDoc}
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * Set the value of discount_type
     *
     * @param  string  $discount_type
     * @return self
     */
    public function setDiscountType(string $discount_type): self
    {
        $this->discount_type = $discount_type;

        return $this;
    }

    /**
     * Set the value of discount_name
     *
     * @param  string  $discount_name
     * @return self
     */
    public function setDiscountName(string $discount_name): self
    {
        $this->discount_name = $discount_name;

        return $this;
    }

    /**
     * Set the value of amount_off
     *
     * @param  int  $amount_off
     * @return self
     */
    public function setAmountOff(?int $amount_off): self
    {
        $this->amount_off = $amount_off;

        return $this;
    }

    public function setPercentOff(?int $percent_off): self
    {
        $this->percent_off = $percent_off;

        return $this;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function setIsRedeemable(bool $is_redeemable): self
    {
        $this->is_redeemable = $is_redeemable;

        return $this;
    }

    public function setHasExpired(bool $has_expired): self
    {
        $this->has_expired = $has_expired;

        return $this;
    }

    public function setRestrictionsMinimumAmount(int $restrictions_minimum_amount): self
    {
        $this->restrictions_minimum_amount = $restrictions_minimum_amount;

        return $this;
    }

    public function setMinQuantity(int $min_quantity): self
    {
        $this->min_quantity = $min_quantity;

        return $this;
    }

    public function setUnitQuantity(int $unit_quantity): self
    {
        $this->unit_quantity = $unit_quantity;

        return $this;
    }

    /**
     * @return self
     */
    public function setFreeQuantity(?int $free_quantity): self
    {
        $this->free_quantity = $free_quantity;

        return $this;
    }

    /**
     * @return self
     */
    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function setRedeemSucceeded(bool $redeem_succeeded): self
    {
        $this->redeem_succeeded = $redeem_succeeded;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function redeem(DiscountLineItem $discountLineItem): bool
    {
        return $this->redeem_succeeded;
    }
}
