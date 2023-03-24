<?php

declare(strict_types=1);

namespace Autepos\Discount\Tests\Unit\Fixtures;

use Autepos\Discount\Contracts\Discountable;
use Autepos\Discount\Contracts\DiscountableDeviceLine;

class DiscountableDeviceLineFixture implements DiscountableDeviceLine
{
    public function __construct(
        public int $id = 1,
        public ?string $type = null,
        public ?Discountable $discountable = null,
        public int $subtotal = 1001,
        public int $quantity = 1,

    ) {
        if (is_null($this->type)) {
            $this->type = get_class();
        }

        if (is_null($this->discountable)) {
            $this->discountable = new DiscountableFixture();
        }
    }

    public function getDiscountableDeviceLineIdentifier(): ?int
    {
        return $this->id;
    }

    public function getDiscountableDeviceLineType(): string
    {
        return $this->type;
    }

    public function getDiscountable(): Discountable
    {
        return $this->discountable;
    }

    public function getDiscountableDeviceLineAmount(): int
    {
        return $this->subtotal;
    }

    public function getDiscountableDeviceLineQuantity(): int
    {
        return $this->quantity;
    }
}
