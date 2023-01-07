<?php

namespace Autepos\Discount;

use Autepos\Discount\Exceptions\AmountDistributionException;

/**
 * Distribute an amount to individuals who receives integer amounts.
 */
class AmountDistributor
{
    /**
     * The distributed amounts.
     *
     * @var array<mixed,int>
     */
    protected array $distributedAmounts = [];

    /**
     * The remainder of the amount to be distributed.
     *
     * @var int
     */
    protected $remainder = 0;

    /**
     * Reset the object.
     */
    protected function reset(): void
    {
        $this->distributedAmounts = [];
        $this->remainder = 0;
    }

    /**
     * Distribute an amount to individuals using the trickle-up strategy.
     * Distribute an amount to individuals with respect to their capacities
     * which limits how much they can have. The trickle-up strategy state
     * that the least capacity are served first up to their capacity, the
     * remaining amount trickles up to larger capacities.
     *
     * @param  int  $amount
     * @param  array<string,int>  $capacities
     * @return static
     *
     * @throws AmountDistributionException
     */
    public function trickleUp(int $amount, array $capacities): static
    {
        return $this->trickle($amount, $capacities, 'up');
    }

    /**
     * Distribute an amount to individuals using the trickle-down strategy.
     * Distribute an amount to individuals with respect to their capacities
     * which limits how much they can have. The trickle-down strategy state
     * that the largest capacity are served first up to their capacity, the
     * remaining amount trickles down to smaller capacities.
     *
     * @param  int  $amount
     * @param  array<string,int>  $capacities
     * @return static
     *
     * @throws AmountDistributionException.
     */
    public function trickleDown(int $amount, array $capacities): static
    {
        return $this->trickle($amount, $capacities, 'down');
    }

    /**
     * Distribute an amount to individuals using the trickle-up/down strategy.
     *
     * @param  int  $amount
     * @param  array<string,int>  $capacities
     * @param  string  $strategy The trickling direction:{'up'|'down'}. Default is 'up'.
     * @return static
     *
     * @throws AmountDistributionException If the total capacity is less than the amount
     *      to be distributed. Or if the amount to be distributed is less than zero. Or
     *      if the strategy is not 'up' or 'down'. Or if the remaining amount after
     *      distribution is not zero.
     */
    protected function trickle(int $amount, array $capacities, $strategy = 'up'): static
    {
        $this->reset();

        if (! in_array($strategy, ['up', 'down'])) {
            throw new AmountDistributionException("Strategy must be 'up' or 'down'.");
        }

        if ($amount < 0) {
            throw new AmountDistributionException('Amount to be distributed is less than zero.');
        }

        $amounts = [];

        $originalKeyOrder = array_keys($capacities);

        // sort the capacities in ascending order and preserve the keys
        uasort($capacities, function (int $a, int $b) {
            return $a <=> $b;
        });

        if ($strategy == 'down') {
            $capacities = array_reverse($capacities, true);
        }

        //
        foreach ($capacities as $key => $capacity) {
            //initialize the amounts array
            $amounts[$key] = 0;
        }

        if (array_sum($capacities) < $amount) {
            throw new AmountDistributionException('Total amount of discount lines is less than the amount to be distributed.');
        }

        $amountLeft = $amount;
        foreach ($capacities as $key => $capacity) {
            switch($amountLeft <=> $capacity) {
                case 1:
                case 0:
                    $share = $capacity;
                    break;
                case -1:
                    $share = $amountLeft;
                    break;
            }
            $amounts[$key] = $share;
            $amountLeft = $amountLeft - $share;

            if ($amountLeft == 0) {
                break;
            }
        }

        $totalDistributedAmount = array_sum($amounts);
        if ($amountLeft != 0) {
            throw new AmountDistributionException('Unexpectedly, the distributed amount, '
                .$totalDistributedAmount.' does not equal the amount to be distributed, '
                .$amount.'.'
            );
        }

        // sort the amounts array in the same order as the original capacities array
        $amounts = array_merge(array_flip($originalKeyOrder), $amounts);

        //
        $this->distributedAmounts = $amounts;

        return $this;
    }

    /**
     * Share an amount into $count places equally.
     *
     * @param  int  $amount
     * @param  int  $count
     * @return static
     */
    public function share(int $amount, int $count): static
    {
        $this->reset();

        $part = intdiv($amount, $count);
        $rem = $amount % $count;
        $shares = array_fill(0, $count, $part);
        $this->remainder = $rem;
        $this->distributedAmounts = $shares;

        return $this;
    }

    /**
     * Get the value of distributedAmounts
     *
     * @return array<mixed,int>
     */
    public function getDistributedAmounts(): array
    {
        return $this->distributedAmounts;
    }

    /**
     * Get the value of remainder
     */
    public function getRemainder(): int
    {
        return $this->remainder;
    }
}
