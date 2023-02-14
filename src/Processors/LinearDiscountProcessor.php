<?php

namespace Autepos\Discount\Processors;

use Autepos\Discount\Contracts\DiscountInstrument;
use Autepos\Discount\Processors\Contracts\DiscountProcessor;

class LinearDiscountProcessor extends DiscountProcessor
{
    /**
     * The processor tag
     *
     * @var string
     */
    public const PROCESSOR = 'linear';

    /**
     * {@inheritDoc}
     */
    public function getProcessor(): string
    {
        return static::PROCESSOR;
    }

    /**
     * Define the sequence with which the discount instruments are applied.
     *
     * @return void
     */
    protected function sortDiscountInstruments(): void
    {
        // Sort the instruments so that the absolute discounts are
        // applied first.
        usort($this->discountInstruments, function (DiscountInstrument $a, DiscountInstrument $b) {
            return $this->isAbsoluteDiscount($a) <=> $this->isAbsoluteDiscount($b);
        });

        $this->discountInstruments = array_reverse($this->discountInstruments);
    }
}
