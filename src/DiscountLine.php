<?php

namespace Autepos\Discount;

use Autepos\Discount\Contracts\DiscountableDevice;
use Autepos\Discount\Contracts\DiscountableDeviceLine;
use Autepos\Discount\Contracts\DiscountInstrument;

class DiscountLine
{
    /**
     * @var array<int,DiscountLineAgent>
     */
    private array $agents = [];

    public function __construct(
        protected string $hash,
        protected DiscountableDevice $discountableDevice,
        protected DiscountableDeviceLine $discountableDeviceLine
    ) {
        $quantity = $this->discountableDeviceLine->getDiscountableDeviceLineQuantity();
        for ($i = 0; $i < $quantity; $i++) {
            $index = $this->nextAgentIndex();
            $this->agents[$index] = new DiscountLineAgent($this, $index);
        }
    }

    /**
     * Construct a discount line.
     */
    public static function constructFrom(
        DiscountableDevice $discountableDevice,
        DiscountableDeviceLine $discountableDeviceLine
    ): static {
        return new static(
            static::makeHash($discountableDevice, $discountableDeviceLine),
            $discountableDevice,
            $discountableDeviceLine
        );
    }

    /**
     * Make next index
     */
    private function nextAgentIndex(): int
    {
        return count($this->agents);
    }

    /**
     * Add agent
     *
     * @throws \InvalidArgumentException if the agent does not match this discount line.
     */
    private function addAgent(DiscountLineAgent $agent): void
    {
        // If the discount line mismatches, throw an exception.
        if ($agent->getDiscountLine() !== $this) {
            throw new \InvalidArgumentException('Agent\'s Discount line mismatch.');
        }

        $index = $this->nextAgentIndex();
        if ($agent->getIndex() != $index) {
            $agent->setIndex($index);
        }

        $this->agents[$index] = $agent;
    }

    /**
     * Get the discount line agents.
     *
     * @return array<int,DiscountLineAgent>
     */
    public function selectAgents(int $count = 1): array
    {
        // Get the indexes of the virtual discount lines with the highest remainder
        $indexes = $this->argMaxRemainder($count);

        // Return the virtual discount lines with the highest remainder
        return array_intersect_key($this->agents, array_flip($indexes));
    }

    /**
     * Return the indexes of agents with the highest remainder.
     *
     * @return array<int,int>
     */
    private function argMaxRemainder(int $count = 1): array
    {
        $remainders = [];
        foreach ($this->agents as $agent) {
            $remainder = $agent->remainder();
            $remainders[$agent->getIndex()] = $remainder;
        }

        // Sort items by remainder preserving keys
        asort($remainders, SORT_NUMERIC);
        $remainders = array_reverse($remainders, true);

        // Return the first $count items
        $keys = array_keys($remainders);

        return array_slice($keys, 0, $count, true);
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
     * Check whether the discount line has a discountable device line.
     */
    public function hasDiscountableDeviceLine(): bool
    {
        return ! is_null($this->discountableDeviceLine);
    }

    /**
     * Get the value of hash
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Check if the discount line has no line items.
     */
    public function isEmpty(): bool
    {
        foreach ($this->agents as $agent) {
            if (! $agent->isEmpty()) {
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
        foreach ($this->agents as $agent) {
            $amount += $agent->amount();
        }

        return $amount;
    }

    /**
     * Get the total discount amount for a discount instrument.
     */
    public function amountForDiscountInstrument(DiscountInstrument $discountInstrument): int
    {
        $amount = 0;

        foreach ($this->agents as $agent) {
            $amount += $agent->amountForDiscountInstrument($discountInstrument);
        }

        return $amount;
    }

    /**
     * Persist the discount.
     *
     * @return bool
     */
    public function redeem(): bool
    {
        foreach ($this->agents as $agent) {
            $agent->redeem();
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
        foreach ($this->agents as $agent) {
            if (! $agent->isRedeemed()) {
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
    public function items(): array
    {
        $items = [];
        foreach ($this->agents as $agent) {
            $items = array_merge($items, $agent->getItems());
        }

        return $items;
    }

    /**
     * Get all all agents with nonzero amount as an array.
     *
     * @return array<string,DiscountLineAgent>
     */
    public function allWithAmount(): array
    {
        return array_filter($this->agents, function (DiscountLineAgent $discountLineAgent) {
            return $discountLineAgent->amount() > 0;
        });
    }

    /**
     * Prune all agents with nonzero amount mutating the instance in place.
     *
     * @return static $this
     */
    public function prune(): static
    {
        $this->agents = $this->allWithAmount();

        return $this;
    }

    /**
     * Group items by discount instrument and return the result as array of discount lines.
     * Each discount line contains the same discountable device and discountable device line.
     * Each discount line contains only the items that belong to the same discount instrument.
     *
     * @return array<mixed,DiscountLine> The array key is the discount instrument identifier.  The key is the discount instrument identifier. NOTE: The discount lines
     *                                        return by this method should only be used for reading. If you want to
     *                                       modify the discount lines, you should use the methods in this class.
     */
    public function groupByDiscountInstrument(): array
    {
        $discountLines = [];
        foreach ($this->agents as $agent) {
            $dLines = $agent->groupByDiscountInstrument();

            foreach ($dLines as $key => $dLine) {
                if (array_key_exists($key, $discountLines)) {
                    foreach ($dLine->agents as $dLineAgent) {
                        $dLineAgent->setDiscountLine($discountLines[$key]);
                        $discountLines[$key]->addAgent($dLineAgent);
                    }
                } else {
                    $discountLines[$key] = $dLine;
                }
            }
        }

        return $discountLines;
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
}
