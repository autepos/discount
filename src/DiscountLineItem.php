<?php

namespace Autepos\Discount;

use Autepos\Discount\Contracts\DiscountInstrument;

class DiscountLineItem
{
    /**
     * Determines if the item is persisted
     *
     * @var bool
     */
    protected $redeemed = false;

    /**
     * @param  DiscountLine  $discountLine
     * @param  DiscountInstrument  $discountInstrument
     * @param  int  $amount The discount amount resulting from this item.
     * @param  int  $unitQuantity The number of items grouped together in unit_quantity_group as one unit for the discount.
     * @param  string|null  $unitQuantityGroup The tag identifying the items grouped together in unit_quantity as one unit for the discount.
     */
    public function __construct(
        protected DiscountLine $discountLine,
        protected DiscountInstrument $discountInstrument,
        protected int $amount = 0,
        protected int $unitQuantity = 1,
        protected ?string $unitQuantityGroup = 'none',
        protected ?int $orderId = null,
        protected int|string|null $userId = null,
        protected int|string|null $adminId = null,
        protected int|string|null $tenantId = null,
        protected string $processor = ''
    ) {
    }

    /**
     * Redeem the discount line item.
     *
     * @return bool
     */
    public function redeem(): bool
    {
        if ($this->redeemed) {
            return true;
        }
        $result = $this->discountInstrument->redeem($this);
        $this->redeemed = $result;

        return $this->redeemed;
    }

    /**
     * Get the value of discountLine
     */
    public function getDiscountLine()
    {
        return $this->discountLine;
    }

    /**
     * Set the value of discountLine
     */
    public function setDiscountLine($discountLine): static
    {
        $this->discountLine = $discountLine;

        return $this;
    }

    /**
     * Get the value of discountInstrument
     */
    public function getDiscountInstrument()
    {
        return $this->discountInstrument;
    }

    /**
     * Get the value of amount
     *
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Get the value of redeemed
     */
    public function isRedeemed()
    {
        return $this->redeemed;
    }

    /**
     * Get the value of unitQuantity
     *
     * @return int
     */
    public function getUnitQuantity(): int
    {
        return $this->unitQuantity;
    }

    /**
     * Get the value of unitQuantityGroup
     *
     * @return ?string
     */
    public function getUnitQuantityGroup(): ?string
    {
        return $this->unitQuantityGroup;
    }

    /**
     * Get the value of orderId
     *
     * @return ?int
     */
    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    /**
     * Get the value of userId
     *
     * @return int|string|null
     */
    public function getUserId(): int|string|null
    {
        return $this->userId;
    }

    /**
     * Get the value of adminId
     *
     * @return int|string|null
     */
    public function getAdminId(): int|string|null
    {
        return $this->adminId;
    }

    /**
     * Get the value of tenantId
     *
     * @return int|string|null
     */
    public function getTenantId(): int|string|null
    {
        return $this->tenantId;
    }

    /**
     * Get the value of processor
     *
     * @return string
     */
    public function getProcessor(): string
    {
        return $this->processor;
    }
}
