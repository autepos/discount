<?php

namespace Autepos\Discount;

use ArrayIterator;
use Autepos\Discount\Contracts\DiscountableDevice;
use Autepos\Discount\Contracts\DiscountableDeviceLine;
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

    public function add(DiscountLine $discountLine): void
    {
        $this->items[$discountLine->getHash()] = $discountLine;
    }

    public function get($key): DiscountLine
    {
        return $this->items[$key];
    }

        /**
     * Retrieve a discount line. 
     *
     * @param  DiscountableDevice  $discountableDevice
     * @param  DiscountableDeviceLine|null  $discountableDeviceLine
     * @return DiscountLine|null
     */
    public function retrieve(DiscountableDevice $discountableDevice, ?DiscountableDeviceLine $discountableDeviceLine = null)
    {
        $key = DiscountLine::makeHash($discountableDevice, $discountableDeviceLine);
        if (! $this->has($key)) {
            return null;
        }

        return $this->get($key);
    }


    /**
     * Retrieve or add a discount line. Tries to find a discount line with the
     * same hash. If not found, creates a new one.
     *
     * @param  DiscountableDevice  $discountableDevice
     * @param  DiscountableDeviceLine|null  $discountableDeviceLine
     * @return DiscountLine
     */
    public function retrieveOrAdd(DiscountableDevice $discountableDevice, ?DiscountableDeviceLine $discountableDeviceLine = null)
    {
        // Try to retrieve a discount line 
        $discountLine=$this->retrieve($discountableDevice, $discountableDeviceLine);
        if (!is_null($discountLine)) {
            return $discountLine;
        }

        $key = DiscountLine::makeHash($discountableDevice, $discountableDeviceLine);
        $discountLine = new DiscountLine($key, $discountableDevice, $discountableDeviceLine);
        $this->add($discountLine);

        return $this->get($key);
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
     * Prune all items with zero amount mutating the collection instance.
     */
    public function prune(): static
    {
        $this->items = $this->allWithAmount();
        return $this;
    }

    /**
     * Get all items with none-zero amount as a new instance of the collection.
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
            return !$discountLine->isEmpty();
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
     * 
     */
    public function filter(?callable $callback=null): static
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
}
