<?php

declare(strict_types=1);

namespace Autepos\Discount\Tests\Unit\Fixtures;

use Autepos\Discount\Contracts\Discountable;

/**
 * An item to which discount can be applied.
 */
class DiscountableFixture implements Discountable
{
    public function __construct(
        public int $id = 1,
        public ?string $type = null,
        public int $price = 1000
    ) {
        if (is_null($this->type)) {
            $this->type = get_class();
        }
    }

    public function getDiscountableIdentifier(): ?int
    {
        return $this->id;
    }

    public function getDiscountableType(): string
    {
        return get_class();
    }

    public function getPrice(): int
    {
        return $this->price;
    }
}
