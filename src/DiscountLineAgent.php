<?php

namespace Autepos\Discount;

use Autepos\Discount\Contracts\DiscountInstrument;

/**
 * A DiscountLineAgent is a virtual discount line that is used to calculate
 * the discount amount for a discount line. It makes it possible to use one
 * discount line to calculate the discount amount for a DiscountableDeviceLine
 * with quantity > 1.
 */
class DiscountLineAgent
{
    /**
     * @var array<int,DiscountLineItem>
     */
    protected array $items = [];

    public function __construct(
        protected DiscountLine $discountLine,
        private int $index = 0
    ) {
    }

    /**
     * Set index
     */
    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    /**
     * Get the index of the agent.
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * Get hash
     */
    public function hash(): string
    {
        return $this->discountLine->getHash().'_'.$this->getIndex();
    }

    /**
     * Build and add a new discount line item.
     *
     * @see DiscountLineItem::__construct
     */
    public function addItem(
        DiscountInstrument $discountInstrument,
        int $amount = 0,
        int $unit_quantity = 1,
        string|null $unit_quantity_group = 'none',
        int $unit_quantity_group_number = 1,
        string $processor = '',
        array $meta = [],
    ): void {
        $discountLineItem = new DiscountLineItem(
            $this,
            $discountInstrument,
            $amount,
            $unit_quantity,
            $unit_quantity_group,
            $unit_quantity_group_number,
            $processor,
            $meta

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
        if ($discountLineItem->getDiscountLine() != $this->discountLine) {
            throw new \Exception('Discount line mismatch.');
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
     * The original subtotal on which discount is applied.
     *
     * @return int
     *
     * @throws \Exception When discountable device line is not set.
     */
    public function subtotal(): int
    {
        // Total cannot be called when discountable device line is not set.
        if (! $this->discountLine->hasDiscountableDeviceLine()) {
            throw new \Exception('Discountable device line is not set. A discount line must have a discountable device line before subtotal can be called.');
        }

        return $this->discountLine->getDiscountableDeviceLine()->getDiscountableDeviceLineAmount();
    }

    /**
     * The remaining amount following all the discounts that has already
     * been added. The range is >=0.
     *
     * @return int
     *
     * @throws \Exception When discountable device line is not set.
     */
    public function remainder(): int
    {
        return max(
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
     * @return array<mixed,DiscountLine> The array key is the discount instrument identifier.  The key is the discount instrument identifier. NOTE: The discount lines
     *                                        return by this method should only be used for reading.
     */
    public function groupByDiscountInstrument(): array
    {
        $grouped = $this->groupByDiscountInstrumentAsArray();

        $discountLines = [];
        foreach ($grouped as $discountInstrumentIdentifier => $discountLineItems) {
            $discountLine = DiscountLine::constructFrom(
                $this->discountLine->getDiscountableDevice(), // same as $discountLineItems[0]->getDiscountLine()->getDiscountableDevice()
                $this->discountLine->getDiscountableDeviceLine() // same as $discountLineItems[0]->getDiscountLine()->getDiscountableDeviceLine()
            );
            foreach ($discountLineItems as $discountLineItem) {
                $discountLineItem->setDiscountLine($discountLine);
                $discountLineAgents = $discountLine->selectAgents();
                $discountLineAgent = array_pop($discountLineAgents);
                $discountLineAgent->add($discountLineItem);
            }
            $discountLines[$discountInstrumentIdentifier] = $discountLine;
        }

        return $discountLines;
    }

    /**
     * Get all the discount instruments.
     *
     * @return array<string,DiscountInstrument> The array key is the discount instrument type_identifier.
     */
    public function discountInstruments(string $discount_type = null): array
    {
        $discountInstruments = [];
        foreach ($this->items as $item) {
            $key = $item->getDiscountInstrument()->getDiscountInstrumentType().'_'.$item->getDiscountInstrument()->getDiscountInstrumentIdentifier();
            $discountInstruments[$key] = $item->getDiscountInstrument();
        }

        if (is_null($discount_type)) {
            return $discountInstruments;
        }

        return array_filter($discountInstruments, function (DiscountInstrument $discountInstrument) use ($discount_type) {
            return $discountInstrument->getDiscountType() == $discount_type;
        });
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
     * Get the value of discountLine
     */
    public function getDiscountLine(): DiscountLine
    {
        return $this->discountLine;
    }
}
