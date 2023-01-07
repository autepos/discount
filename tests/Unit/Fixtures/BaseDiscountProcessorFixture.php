<?php

declare(strict_types=1);

namespace Autepos\Discount\Tests\Unit\Fixtures;

use Autepos\Discount\Processors\Contracts\DiscountProcessor;

class BaseDiscountProcessorFixture extends DiscountProcessor
{
    //
    public function getProcessor(): string
    {
        return static::class;
    }
}
