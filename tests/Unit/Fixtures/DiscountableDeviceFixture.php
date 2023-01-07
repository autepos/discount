<?php

declare(strict_types=1);

namespace Autepos\Discount\Tests\Unit\Fixtures;

use Autepos\Discount\Contracts\Discountable;
use Autepos\Discount\Contracts\DiscountableDevice;
use Autepos\Discount\Contracts\DiscountableDeviceLine;

class DiscountableDeviceFixture implements DiscountableDevice
{
    /**
     * @var array<int,\Autepos\Discount\Contracts\DiscountableDeviceLine>
     */
    public array $items = [];

    public function __construct(
        public int $id = 1,
        public ?string $type = null,
        public int $subtotal = 100000,
        ?DiscountableDeviceLine $discountableDeviceLine = null
    ) {
        if ($discountableDeviceLine) {
            $this->items[] = $discountableDeviceLine;
        }
    }

    public function subtotal(): int
    {
        return $this->subtotal;
    }

    /**
     * Set discountable device lines.
     *
     * @param  array<int,\Autepos\Discount\Contracts\DiscountableDeviceLine>  $items
     */
    public function setDiscountableDeviceLines(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Add discountable device lines with the given discountables.
     *
     * @param  array<int,\Autepos\Discount\Contracts\Discountable>  $discountables
     */
    public function withDiscountables(array $discountables): self
    {
        foreach ($discountables as $discountable) {
            $this->items[] = new DiscountableDeviceLineFixture(
                100 + $discountable->getDiscountableIdentifier(), // Just borrow the id from the discountable
                null,
                $discountable,
                $discountable->getPrice()
            );
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscountableDeviceLines(?Discountable $discountable = null): array
    {
        if (is_null($discountable)) {
            return $this->items;
        }

        return array_filter($this->items, function (DiscountableDeviceLine $item) use ($discountable) {
            return
                ($item->getDiscountable()->getDiscountableIdentifier() == $discountable->getDiscountableIdentifier())
                and
                ($item->getDiscountable()->getDiscountableType() == $discountable->getDiscountableType());
        });
    }

    /**
     * {@inheritDoc}
     *
     * @return int|null
     */
    public function getDiscountableDeviceIdentifier(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getDiscountableDeviceType(): string
    {
        return $this->type ?? get_class();
    }
}
