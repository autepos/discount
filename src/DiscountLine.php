<?php

namespace Autepos\Discount;

use Autepos\Discount\Contracts\DiscountableDevice;
use Autepos\Discount\Contracts\DiscountableDeviceLine;
use Autepos\Discount\Contracts\DiscountInstrument;

class DiscountLine
{
    /**
     * @var array<int,DiscountLineItem>
     */
    protected array $items = [];

    public function __construct(
        protected string $hash,
        protected DiscountableDevice $discountableDevice,
        protected ?DiscountableDeviceLine $discountableDeviceLine = null
    ) {
    }

    /**
     * Add a new discount line item.
     *
     * @param  DiscountInstrument  $discountInstrument
     * @param  int  $amount
     * @return void
     */
    public function addItem(
            DiscountInstrument $discountInstrument,
            int $amount = 0,
            string|null $unit_quantity_group = 'none',
            ?int $order_id = null,
            int|string|null $user_id = null,
            int|string|null $admin_id = null,
            string $processor = ''
        ): void {
        $this->items[] = new DiscountLineItem(
            $this,
            $discountInstrument,
            $amount,
            $unit_quantity_group,
            $order_id,
            $user_id,
            $admin_id,
            $processor
        );
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
            $amount += $item->getAmount();
        }

        return $amount;
    }

    /**
     * The original subtotal on which discount is applied.
     *
     * @return int
     */
    public function subtotal(): int
    {
        return $this->discountableDeviceLine?->subtotal() ?? $this->discountableDevice->subtotal();
    }

    /**
     * The remaining amount of subtotal after discount following all the
     * discounts that has already been added. The range is >=0.
     *
     * @return int
     */
    public function remainder(): int
    {
        return  max($this->subtotal() - $this->amount(), 0);
    }

    /**
     * Persist the discount.
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
         * Get the value of hash
         */
        public function getHash()
        {
            return $this->hash;
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

    /**
     * Get the value of items
     *
     * @return array<int,DiscountLineItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
