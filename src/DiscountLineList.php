<?php

namespace Autepos\Discount;

use ArrayIterator;
use Autepos\Discount\Contracts\DiscountableDevice;
use Autepos\Discount\Contracts\DiscountableDeviceLine;
use Autepos\Discount\Contracts\DiscountInstrument;
use Countable;
use IteratorAggregate;
use Traversable;

class DiscountLineList implements IteratorAggregate, Countable
{
    /**
     * @var array<string,DiscountLine>
     */
    protected $items = [];

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator<string,DiscountLine>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
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

    public function has($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Add a discount line to the collection.
     *
     * @param  DiscountLine  $discountLine
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function add(DiscountLine $discountLine): void
    {
        // If the discount line already exists, throw an exception.
        if ($this->has($discountLine->getHash())) {
            throw new \InvalidArgumentException('Discount line with hash, "'.$discountLine->getHash().'" already exists.');
        }

        $this->items[$discountLine->getHash()] = $discountLine;
    }

    public function get($key): DiscountLine
    {
        return $this->items[$key];
    }

    /**
     * find a discount line.
     *
     * @param  DiscountableDevice  $discountableDevice
     * @param  DiscountableDeviceLine|null  $discountableDeviceLine
     * @return DiscountLine|null
     */
    public function find(DiscountableDevice $discountableDevice, ?DiscountableDeviceLine $discountableDeviceLine = null)
    {
        $key = DiscountLine::makeHash($discountableDevice, $discountableDeviceLine);
        if (! $this->has($key)) {
            return null;
        }

        return $this->get($key);
    }

    /**
     * find or add a discount line. Tries to find a discount line with the
     * same hash. If not found, creates a new one.
     *
     * @param  DiscountableDevice  $discountableDevice
     * @param  DiscountableDeviceLine|null  $discountableDeviceLine
     * @return DiscountLine
     */
    public function findOrAdd(DiscountableDevice $discountableDevice, ?DiscountableDeviceLine $discountableDeviceLine = null)
    {
        // Try to find a discount line
        $discountLine = $this->find($discountableDevice, $discountableDeviceLine);
        if (! is_null($discountLine)) {
            return $discountLine;
        }

        $discountLine = DiscountLine::constructFrom($discountableDevice, $discountableDeviceLine);
        $this->add($discountLine);

        return $this->get($discountLine->getHash());
    }

    /**
     * Get all items.
     *
     * @return array<string,DiscountLine>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get all items with none-zero amount as an array.
     *
     * @return array<string,DiscountLine>
     */
    public function allWithAmount(): array
    {
        return array_filter($this->all(), function (DiscountLine $discountLine) {
            return $discountLine->amount() > 0;
        });
    }

    /**
     * Prune all items with zero amount mutating the collection instance in place.
     *
     * @return static $this
     */
    public function prune(): static
    {
        $this->items = $this->allWithAmount();

        return $this;
    }

    /**
     * Get all items with none-zero amount as a new instance of the
     * collection (i.e the current instance is not mutated).
     *
     * @return DiscountLineList
     */
    public function allWithAmountAsCollection(): DiscountLineList
    {
        $collection = new DiscountLineList();
        foreach ($this->allWithAmount() as $discountLine) {
            $collection->add($discountLine);
        }

        return $collection;
    }

    /**
     * Get all non-empty items as an array. An empty item is an item without
     * a discount line item.
     *
     * @return array<string,DiscountLine>
     */
    public function allNonEmpty(): array
    {
        return array_filter($this->all(), function (DiscountLine $discountLine) {
            return ! $discountLine->isEmpty();
        });
    }

    /**
     * Get all non-empty items (i.e. items with at least 1 discount line item).
     *
     * @return DiscountLineList
     */
    public function allNonEmptyAsCollection(): DiscountLineList
    {
        $collection = new DiscountLineList();
        foreach ($this->allNonEmpty() as $discountLine) {
            $collection->add($discountLine);
        }

        return $collection;
    }

    /**
     * Filter items by a callback and returns a new instance. if no callback
     * is provided, all empty (i.e. items without a discount line item) will
     * be filtered out.
     */
    public function filter(?callable $callback = null): static
    {
        if (is_null($callback)) {
            return $this->allNonEmptyAsCollection();
        }

        $items = array_filter($this->items, $callback);

        $collection = new DiscountLineList();
        foreach ($items as $discountLine) {
            $collection->add($discountLine);
        }

        return $collection;
    }

    /**
     * Persists the discounts.
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
     * Group items by discount instrument.
     *
     * @return array<mixed,array<DiscountLine>> The key is the discount instrument identifier.
     */
    public function groupByDiscountInstrumentAsArray(): array
    {
        $discountLines = [];
        foreach ($this->items as $item) {
            foreach ($item->groupByDiscountInstrument() as $discountLine) {
                $discountLineItems = $discountLine->getItems();
                if (count($discountLineItems) === 0) {
                    continue;
                }
                $discountInstrument = $discountLineItems[0]->getDiscountInstrument();
                $key = $discountInstrument->getDiscountInstrumentIdentifier();
                $discountLines[$key][] = $discountLine;
            }
        }

        return $discountLines;
    }

    /**
     * Group items amount by discount instrument.
     *
     * @return array<mixed,int> The key is the discount instrument identifier.
     */
    public function groupAmountByDiscountInstrument(): array
    {
        $groupedAmounts = [];

        foreach ($this->groupByDiscountInstrumentAsArray() as $discountInstrumentIdentifier => $discountLines) {
            foreach ($discountLines as $discountLine) {
                if (! array_key_exists($discountInstrumentIdentifier, $groupedAmounts)) {
                    $groupedAmounts[$discountInstrumentIdentifier] = 0;
                }

                $groupedAmounts[$discountInstrumentIdentifier] += $discountLine->amount();
            }
        }

        return $groupedAmounts;
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
            $amount += $item->amount();
        }

        return $amount;
    }

    /**
     * Get the total discount amount for a discount instrument.
     */
    public function amountForDiscountInstrument(DiscountInstrument $discountInstrument): int
    {
        $amount = 0;
        foreach ($this->items as $item) {
            $amount += $item->amountForDiscountInstrument($discountInstrument);
        }

        return $amount;
    }

    /**
     * Get the total discount for a given discountableDevice and discountableDeviceLine.
     */
    public function amountFor(DiscountableDevice $discountableDevice, ?DiscountableDeviceLine $discountableDeviceLine = null): int
    {
        return $this->find($discountableDevice, $discountableDeviceLine)?->amount() ?? 0;
    }
}
