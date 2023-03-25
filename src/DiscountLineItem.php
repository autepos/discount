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
     * The tag identifying the items grouped together in unit_quantity as one unit
     * for the discount.
     */
    protected string $unitQuantityGroup = 'none';

    /**
     * Within group(i.e $unitQuantityGroup) unique number. E.g. if there are 2 items
     * in the group, the first item will have $unitQuantityGroupNumber = 1 and the
     * second item will have $unitQuantityGroupNumber = 2. This is particularly
     * useful to identify a discount that is part of a group applied as one
     * unit. An example use case is to update the number of times redeemed
     * of a discount instrument only once regardless of how many discounts
     * recorded in the group as one unit.
     *
     * @var int
     */
    protected int $unitQuantityGroupNumber = 1;

    /**
     * @param  DiscountLineAgent  $agent
     * @param  DiscountInstrument  $discountInstrument
     * @param  int  $amount The discount amount resulting from this item.
     * @param  int  $unitQuantity The number of items grouped together in unit_quantity_group as one unit for the discount.
     * @param  string  $unitQuantityGroup Unit quantity group.
     * @param  int  $unitQuantityGroupNumber Unit quantity group number.
     */
    public function __construct(
        protected DiscountLineAgent $agent,
        protected DiscountInstrument $discountInstrument,
        protected int $amount = 0,
        protected int $unitQuantity = 1,
        string $unitQuantityGroup = 'none',
        int $unitQuantityGroupNumber = 1,
        protected int|string|null $orderId = null,
        protected int|string|null $userId = null,
        protected int|string|null $adminId = null,
        protected int|string|null $tenantId = null,
        protected array $meta = [],
        protected string $processor = ''
    ) {
        $this->unitQuantityGroup = $unitQuantityGroup;
        $this->unitQuantityGroupNumber = $unitQuantityGroupNumber;
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
        return $this->agent->getDiscountLine();
    }

    /**
     * Set the value of discountLine
     */
    public function setDiscountLine($discountLine): static
    {
        $this->agent->setDiscountLine($discountLine);

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
     * @return string
     */
    public function getUnitQuantityGroup(): string
    {
        return $this->unitQuantityGroup;
    }

    /**
     * Get the value of unitQuantityGroupNumber
     *
     * @return int
     */
    public function getUnitQuantityGroupNumber(): int
    {
        return $this->unitQuantityGroupNumber;
    }

    /**
     * Get the value of orderId
     *
     * @return int|string|null
     */
    public function getOrderId(): int|string|null
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
     * Get the value of meta
     *
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
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

    /**
     * Get the value of agent
     */
    public function getAgent()
    {
        return $this->agent;
    }
}
