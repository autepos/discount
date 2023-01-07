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
     * Get or add a discount line. Tries to find a discount line with the
     * same hash. If not found, creates a new one.
     *
     * @param  DiscountableDevice  $discountableDevice
     * @param  DiscountableDeviceLine|null  $discountableDeviceLine
     * @return DiscountLine
     */
    public function getOrAdd(DiscountableDevice $discountableDevice, ?DiscountableDeviceLine $discountableDeviceLine = null)
    {
        $key = DiscountLine::makeHash($discountableDevice, $discountableDeviceLine);
        if (! $this->has($key)) {
            $discountLine = new DiscountLine($key, $discountableDevice, $discountableDeviceLine);
            $this->add($discountLine);
        }

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
