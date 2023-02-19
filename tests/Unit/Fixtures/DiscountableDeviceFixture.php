<?php

declare(strict_types=1);

namespace Autepos\Discount\Tests\Unit\Fixtures;

use Autepos\Discount\Contracts\Discountable;
use Autepos\Discount\Contracts\DiscountableDevice;

class DiscountableDeviceFixture implements DiscountableDevice
{
    /**
     * @var array<int,\Autepos\Discount\Contracts\DiscountableDeviceLine>
     */
    public array $items = [];

    /**
     * Create a new discountable device instance.
     *
     * @param  int  $id
     * @param  string|null  $type
     * @param  int|null  $subtotal If not null, a discountable device line is created with the given subtotal.
     */
    public function __construct(
        public int $id = 1,
        public ?string $type = null,
        ?int $subtotal = null,
    ) {
        if (! is_null($subtotal)) {
            $this->setDiscountableDeviceLines(
                [new DiscountableDeviceLineFixture(1, null, null, $subtotal)]
            );
        }
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
                $discountable->getDiscountableItemPrice()
            );
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscountableDeviceLines(): array
    {
        return $this->items;
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
