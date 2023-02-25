<?php

namespace Autepos\Discount;

use Autepos\Discount\Contracts\DiscountableDevice;
use Autepos\Discount\Contracts\DiscountableDeviceLine;
use Autepos\Discount\Contracts\DiscountInstrument;

class DiscountLine
{
    /**
     * @var array<int,DiscountLineItem>
     */
    protected array $items = [];

    public function __construct(
        protected string $hash,
        protected DiscountableDevice $discountableDevice,
        protected ?DiscountableDeviceLine $discountableDeviceLine = null
    ) {
    }

    /**
     * Construct a discount line.
     */
    public static function constructFrom(
        DiscountableDevice $discountableDevice,
        ?DiscountableDeviceLine $discountableDeviceLine = null
    ): static {
        return new static(
            static::makeHash($discountableDevice, $discountableDeviceLine),
            $discountableDevice,
            $discountableDeviceLine
        );
    }

    /**
     * Build and add a new discount line item.
     *
     * @param  DiscountInstrument  $discountInstrument
     * @param  int  $amount
     * @return void
     */
    public function addItem(
            DiscountInstrument $discountInstrument,
            int $amount = 0,
            int $unit_quantity = 1,
            string|null $unit_quantity_group = 'none',
            ?int $order_id = null,
            int|string|null $user_id = null,
            int|string|null $admin_id = null,
            int|string|null $tenant_id = null,
            string $processor = ''
        ): void {
        $discountLineItem = new DiscountLineItem(
            $this,
            $discountInstrument,
            $amount,
            $unit_quantity,
            $unit_quantity_group,
            $order_id,
            $user_id,
            $admin_id,
            $tenant_id,
            $processor
        );

        $this->add($discountLineItem);
    }

    /**
     * Add a constructed discount line item.
     *
     * @param  DiscountLineItem  $discountLineItem
     * @return void
     *
     * @throws \Exception When discount line item is not from this discount line.
     */
    public function add(DiscountLineItem $discountLineItem): void
    {
        // Throw an exception if the discount line item is not from this discount line.
        if ($discountLineItem->getDiscountLine() != $this) {
            throw new \Exception('Discount line item is not from this discount line.');
        }

        $this->items[] = $discountLineItem;
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Check if the discount line has no line items.
     */
    public function isEmpty(): bool
    {
        return count($this->items) == 0;
    }

    /**
     * Get the total discount amount.
     *
     * @return int
     */
    public function amount(): int
    {
        $amount = 0;
        foreach ($this->items as $item) {
            $amount += $item->getAmount();
        }

        return $amount;
    }

    /**
     * Get the total discount amount for a discount instrument.
     */
    public function amountForDiscountInstrument(DiscountInstrument $discountInstrument): int
    {
        $amount = 0;

        // First group by discount instrument.
        $grouped = $this->groupByDiscountInstrument();

        // Then get the amount for the discount instrument.
        $key = $discountInstrument->getDiscountInstrumentIdentifier();
        if (isset($grouped[$key])) {
            $amount = $grouped[$key]->amount();
        }

        return $amount;
    }

    /**
     * Check whether the discount line has a discountable device line.
     */
    public function hasDiscountableDeviceLine(): bool
    {
        return ! is_null($this->discountableDeviceLine);
    }

    /**
     * The original subtotal on which discount is applied.
     *
     * @return int
     *
     * @throws \Exception When discountable device line is not set.
     */
    public function subtotal(): int
    {
        // Total cannot be called when discountable device line is not set.
        if (! $this->hasDiscountableDeviceLine()) {
            throw new \Exception('Discountable device line is not set. A discount line must have a discountable device line before subtotal can be called.');
        }

        return $this->discountableDeviceLine->getDiscountableDeviceLineSubtotal();
    }

    /**
     * The remaining amount of subtotal after discount following all the
     * discounts that has already been added. The range is >=0.
     *
     * @return int
     *
     * @throws \Exception When discountable device line is not set.
     */
    public function remainder(): int
    {
        return  max(
            $this->subtotal() - $this->amount(),
            0
        );
    }

    /**
     * Persist the discount.
     *
     * @return bool
     */
    public function redeem(): bool
    {
        foreach ($this->items as $item) {
            $item->redeem();
        }

        return $this->isRedeemed();
    }

    /**
     * Returns true if all discounts are redeemed.
     *
     * @return bool
     */
    public function isRedeemed(): bool
    {
        foreach ($this->items as $item) {
            if (! $item->isRedeemed()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Make a unique hash.
     */
    public static function makeHash(DiscountableDevice $discountableDevice, ?DiscountableDeviceLine $discountableDeviceLine = null): string
    {
        return $discountableDevice->getDiscountableDeviceType()
            .'_'.$discountableDevice->getDiscountableDeviceIdentifier()
            .'_'.$discountableDeviceLine?->getDiscountableDeviceLineType()
            .'_'.$discountableDeviceLine?->getDiscountableDeviceLineIdentifier();
    }

        /**
         * Get the value of hash
         */
        public function getHash()
        {
            return $this->hash;
        }

        /**
         * Get the value of discountableDevice
         */
        public function getDiscountableDevice(): DiscountableDevice
        {
            return $this->discountableDevice;
        }

        /**
         * Get the value of discountableDeviceLine
         *
         * @return DiscountableDeviceLine|null
         */
        public function getDiscountableDeviceLine(): ?DiscountableDeviceLine
        {
            return $this->discountableDeviceLine;
        }

    /**
     * Get the value of items
     *
     * @return array<int,DiscountLineItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Group items by discount instrument.
     *
     * @return array<mixed,array<int,DiscountLineItem>> The outer array key is the discount instrument identifier.
     */
    protected function groupByDiscountInstrumentAsArray(): array
    {
        $grouped = [];
        foreach ($this->items as $item) {
            $grouped[$item->getDiscountInstrument()->getDiscountInstrumentIdentifier()][] = $item;
        }

        return $grouped;
    }

    /**
     * Group items by discount instrument and return the result as array of discount lines.
     * Each discount line contains the same discountable device and discountable device line.
     * Each discount line contains only the items that belong to the same discount instrument.
     *
     * @return array<mixed,DiscountLine> The array key is the discount instrument identifier.
     */
    public function groupByDiscountInstrument(): array
    {
        $grouped = $this->groupByDiscountInstrumentAsArray();

        $discountLines = [];
        foreach ($grouped as $discountInstrumentIdentifier => $discountLineItems) {
            $discountLine = DiscountLine::constructFrom(
                $this->getDiscountableDevice(), // same as $discountLineItems[0]->getDiscountLine()->getDiscountableDevice()
                $this->getDiscountableDeviceLine()// same as $discountLineItems[0]->getDiscountLine()->getDiscountableDeviceLine()
            );
            foreach ($discountLineItems as $discountLineItem) {
                $discountLineItem->setDiscountLine($discountLine);
                $discountLine->add($discountLineItem);
            }
            $discountLines[$discountInstrumentIdentifier] = $discountLine;
        }

        return $discountLines;
    }
}
