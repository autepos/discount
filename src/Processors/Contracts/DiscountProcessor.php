<?php

namespace Autepos\Discount\Processors\Contracts;

use Autepos\Discount\AmountDistributor;
use Autepos\Discount\Contracts\Discountable;
use Autepos\Discount\Contracts\DiscountableDevice;
use Autepos\Discount\Contracts\DiscountableDeviceLine;
use Autepos\Discount\Contracts\DiscountInstrument;
use Autepos\Discount\DiscountLine;
use Autepos\Discount\DiscountLineList;
use Autepos\Discount\DiscountTypes;

/**
 * Discount processor template.
 */
abstract class DiscountProcessor
{
    /**
     * @var array<int,DiscountInstrument>
     */
    protected array $discountInstruments = [];

    /**
     * Validate discount instrument.
     *
     * @var array<int,DiscountInstrument>
     */
    private array $validDiscountInstruments = [];

    /**
     * @var array<int,DiscountableDevice>
     */
    protected array $discountableDevices = [];

    /**
     * ID of the underlying order/invoice.
     */
    protected int|string|null $orderId;

    /**
     * ID of discount beneficiary.
     */
    protected int|string|null $userId;

    /**
     * ID of the admin applying the discount.
     *
     * @var int|string|null
     */
    protected int|string|null $adminId;

    /**
     * ID of the tenant.
     *
     * @var int|string|null
     */
    protected int|string|null $tenantId;

    /**
     * Holds the result of the discount calculation. and allows for the
     * results to be persisted.
     *
     * @var DiscountLineList
     */
    protected DiscountLineList $discountLineList;

    // Construct a new instance
    public function __construct(
        ?DiscountInstrument $discountInstrument = null,
        ?DiscountableDevice $discountableDevice = null,
        int|string|null $orderId = null,
        int|string|null $userId = null,
        int|string|null $adminId = null,
        int|string|null $tenantId = null,
    ) {
        // Initialise
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->adminId = $adminId;
        $this->tenantId = $tenantId;

        // set discountable device if provided
        if (! is_null($discountableDevice)) {
            $this->addDiscountableDevice($discountableDevice);
        }
        // Add discount instrument if provided
        if (! is_null($discountInstrument)) {
            $this->addDiscountInstrument($discountInstrument);
        }
    }

    /**
     * Add instrument/s
     */
    final public function addDiscountInstrument(DiscountInstrument ...$discountInstruments): static
    {
        $this->discountInstruments = array_merge($this->discountInstruments, $discountInstruments);

        return $this;
    }

    /**
     * Reset discount instruments.
     */
    final public function resetDiscountInstruments(): static
    {
        $this->discountInstruments = [];

        return $this;
    }

    /**
     * Add discountable device/s
     */
    final public function addDiscountableDevice(DiscountableDevice ...$discountableDevices): static
    {
        $this->discountableDevices = array_merge($this->discountableDevices, $discountableDevices);

        return $this;
    }

    /**
     * Reset discountable devices.
     */
    final public function resetDiscountableDevices(): static
    {
        $this->discountableDevices = [];

        return $this;
    }

    /**
     * Set the value of orderId
     */
    final public function setOrderId(int|string|null $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Set the value of userId
     *
     * @param  int|string|null  $userId
     */
    final public function setUserId(int|string|null $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Set the value of adminId
     *
     * @param  int|string|null  $adminId
     */
    final public function setAdminId(int|string|null $adminId): static
    {
        $this->adminId = $adminId;

        return $this;
    }

    /**
     * Set the value of tenantId
     *
     * @param  int|string|null  $tenantId
     */
    final public function setTenantId(int|string|null $tenantId): static
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    /**
     * Get processor name.
     *
     * @return string
     */
    abstract public function getProcessor(): string;

    /**
     * Determine if the discount instruments is absolute.
     *
     * @param  DiscountInstrument  $discountInstrument
     * @return bool
     */
    protected function isAbsoluteDiscount(DiscountInstrument $discountInstrument): bool
    {
        return DiscountTypes::isAbsolute($discountInstrument->getDiscountType());
    }

    /**
     * Reset discount line list.
     */
    final public function resetDiscountLineList(): static
    {
        $this->discountLineList = new DiscountLineList();

        return $this;
    }

    /**
     * Reset properties.
     */
    final public function reset(): static
    {
        $this->resetDiscountInstruments();
        $this->resetDiscountableDevices();
        $this->setOrderId(null);
        $this->setUserId(null);
        $this->setAdminId(null);
        $this->setTenantId(null);

        return $this;
    }

    /**
     * Calculate discounts.
     *
     * @return DiscountLineList
     */
    final public function calculate(): DiscountLineList
    {
        $this->resetDiscountLineList();
        $this->sortDiscountInstruments();
        $this->validDiscountInstruments = $this->filterValidDiscountInstruments($this->discountInstruments);

        return $this->process($this->validDiscountInstruments);
    }

    /**
     * Hook to define the sequence with which the discount instruments are applied
     * by ordering them.
     *
     * @return void
     */
    protected function sortDiscountInstruments(): void
    {
        //
    }

    /**
     * Hook to filter valid discount instruments.
     *
     * @param  array<int,DiscountInstrument>  $discountInstruments
     * @return array<int,DiscountInstrument>
     */
    protected function filterValidDiscountInstruments(array $discountInstruments): array
    {
        $filteredDiscountInstruments = [];
        foreach ($discountInstruments as $discountInstrument) {
            if ($this->isValid($discountInstrument)) {
                $filteredDiscountInstruments[] = $discountInstrument;
            }
        }

        return $filteredDiscountInstruments;
    }

    /**
     * Hook to check if the discount instrument is valid.
     */
    protected function isValid(DiscountInstrument $discountInstrument): bool
    {
        return $discountInstrument->isRedeemable(1, $this->userId, $this->orderId, $this->adminId, $this->tenantId);
    }

    /**
     * Process the discount instruments.
     *
     * @param  array<int,DiscountInstrument>  $discountInstruments
     */
    protected function process(array $discountInstruments): DiscountLineList
    {
        foreach ($discountInstruments as $discountInstrument) {
            foreach ($this->discountableDevices as $discountableDevice) {
                $this->populateDiscountLines($discountableDevice);
                $this->scan($discountInstrument, $discountableDevice);
            }
        }

        return $this->discountLineList;
    }

    /**
     * Scan a discount instrument against a discountable device.
     */
    protected function scan(DiscountInstrument $discountInstrument, DiscountableDevice $discountableDevice): void
    {
        $instrumentDiscountables = $discountInstrument->getDiscountables();

        // If there are no discountables associated to the discount instrument, then
        // the discount instrument is applied to all discountable device lines. Otherwise
        // the discount instrument is applied to discountable device lines with an
        // applicable discountable.
        if (empty($instrumentDiscountables)) {
            // Enforce minimum amount. This only makes sense when discount is not specific
            // to a discountable as it is the case here.
            if ($this->totalAmountFor($discountableDevice) < $discountInstrument->getRestrictionsMinimumAmount()) {
                return;
            }

            //
            $discountableDeviceLines = $discountableDevice->getDiscountableDeviceLines();
        } else {
            // Apply discount to discountable devices' discountable lines with an
            // applicable discountable.
            $discountableDeviceLines = [];
            foreach ($instrumentDiscountables as $instrumentDiscountable) {
                $discountableDeviceLines = array_merge(
                    $discountableDeviceLines,
                    $this->discountableDeviceLinesFor($discountableDevice, $instrumentDiscountable)
                );
            }
        }

        // Replicate the discountable device lines by their quantities.
        $discountableDeviceLines = $this->replicateDiscountableDeviceLines(...$discountableDeviceLines);

        // Check the minimum quantity criteria.
        if (count($discountableDeviceLines) < $discountInstrument->getMinQuantity()) {
            return;
        }

        // Determine the unit quantity.
        $unit_quantity = $discountInstrument->getUnitQuantity()
                        ?? $discountInstrument->getMaxQuantity()
                        ?? count($discountableDeviceLines);

        // A zero or negative unit quantity is not allowed.
        if ($unit_quantity < 0) {
            return;
        }

        // Apply discount in chunks of the unit quantity
        $discountableDeviceLinesChunks = array_chunk($discountableDeviceLines, $unit_quantity);

        // Discard the last chunk if it is not complete
        if (count($discountableDeviceLinesChunks[count($discountableDeviceLinesChunks) - 1]) < $unit_quantity) {
            array_pop($discountableDeviceLinesChunks);
        }

        // Return if there are no chunk.
        if (empty($discountableDeviceLinesChunks)) {
            return;
        }

        // Limit the number of chunks to the maximum quantity.
        if ($discountInstrument->getMaxQuantity() !== null) {
            $discountableDeviceLinesChunks = array_slice($discountableDeviceLinesChunks, 0, $discountInstrument->getMaxQuantity());
        }

        // Limit the number of chunks to the number of times the discount can be redeemed.
        $discountableDeviceLinesChunks = array_slice(
            $discountableDeviceLinesChunks,
            0,
            $this->timesRedeemable($discountInstrument, count($discountableDeviceLinesChunks))
        );

        // Again, return if there are no chunk. Although there should be at least one
        // chunk since a check for one count of redeemability has been made before
        // this method is called in the first place.
        if (empty($discountableDeviceLinesChunks)) {
            return;
        }

        // Apply discount to chunks.
        $unit_quantities = count($discountableDeviceLinesChunks);
        $counter = 1;
        foreach ($discountableDeviceLinesChunks as $discountableDeviceLinesChunk) {
            $unit_quantity_group = $counter.'_of_'.$unit_quantities;
            $counter++;
            $this->apply($discountInstrument, $discountableDevice, $discountableDeviceLinesChunk, $unit_quantity, $unit_quantity_group);
        }
    }

    /**
     * Apply a scanned discount instrument.
     *
     * @param  DiscountInstrument  $discountInstrument
     * @param  DiscountableDevice  $discountableDevice
     * @param  array<int,DiscountableDeviceLine>  $discountableDeviceLines
     * @param  int  $unit_quantity This is the number of items that are discounted together
     * @param  string  $unit_quantity_group This is the tag that identifies a group/chunk that are discounted together
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function apply(DiscountInstrument $discountInstrument, DiscountableDevice $discountableDevice, array $discountableDeviceLines = [], int $unit_quantity = 1, string $unit_quantity_group = 'none')
    {
        // First validate inputs, it's easier to debug.
        foreach ($discountableDeviceLines as $discountableDeviceLine) {
            if (! is_a($discountableDeviceLine, DiscountableDeviceLine::class)) {
                throw new \InvalidArgumentException('Argument, $discountableDeviceLines 
                    must be an array of '.DiscountableDeviceLine::class);
            }
        }

        // Group the discountable device lines by their type and id.
        $discountableDeviceLinesGroups = $this->groupDiscountableDeviceLines(...$discountableDeviceLines);

        /**
         * @var  array<int,\Autepos\Discount\DiscountLineAgent> $discountLineAgents
         */
        $discountLineAgents = [];

        /**
         * @var array<string,int> $discount_shares This is the share of the discount
         * for each discountable device line. The key is the discount line hash. The
         * shares correspond to the discount lines.
         */
        $discount_shares = [];

        // Applying the discount as unit/whole.
        $remainders = [];
        foreach ($discountableDeviceLinesGroups as $discountableDeviceLinesGroup) {
            $discountableDeviceLine = $discountableDeviceLinesGroup[0];
            $agents = $this->discountLineList->find($discountableDevice, $discountableDeviceLine)
            ->selectAgents(count($discountableDeviceLinesGroup));
            foreach ($agents as $agent) {
                $key = $agent->hash();
                $remainders[$key] = $agent->remainder();
                $discountLineAgents[$key] = $agent;
            }
        }

        // Apply the discount to the discountableDeviceLines as a unit/whole.
        switch($discountInstrument->getDiscountType()) {
            case DiscountTypes::AMOUNT_OFF:
            case DiscountTypes::PERCENT_OFF:

                $discount_amount = $this->off(\array_sum($remainders), $discountInstrument);

                // Share the unit/whole discount.
                $discount_shares = $this->tricklingShare('down', $discount_amount, $remainders);

                break;
            case DiscountTypes::BUY_N_FOR_PRICE_OF_M:
                // Step 1. Get the remainder of the discountable device lines in an array as $remainders.
                // Step 2. Then select the biggest remainder.
                // Step 3. Then multiply the biggest remainder by the effective quantity to get $new_total_remainder (the amount that must be paid following the discount).
                // Step 4. Then formulate the discount amount equ as $new_total_remainder =sum($remainders) - $discount_amount. And rearrange to get: $discount_amount=sum($remainders) - $new_total_remainder.
                // Step 5. Then share the discount amount to the discountable device lines using trickling strategy.

                // Step 1.
                // Already done above.

                // Step 2.
                $biggest_remainder = \max($remainders);

                // Step 3.
                $new_total_remainder = $biggest_remainder * ($discountInstrument->getUnitQuantity() - $discountInstrument->getFreeQuantity());

                // Step 4.
                $discount_amount = \array_sum($remainders) - $new_total_remainder;

                // Step 5.
                $discount_shares = $this->tricklingShare('up', $discount_amount, $remainders);

                break;
            case DiscountTypes::BUY_N_FOR_PRICE:
                // Step 1. Get the remainder of the discountable device lines in an array as $remainders.
                // Step 2. Then get $discountInstrument->getDiscountedPrice() to get $new_total_remainder (the amount that must be paid following the discount).
                // Step 3. Then formulate the discount amount equ as $new_total_remainder =sum($remainders) - $discount_amount. And rearrange to get: $discount_amount=sum($remainders) - $new_total_remainder.
                // Step 4. Then share the discount amount to the discountable device lines using trickling strategy.

                // Step 1.
                // Already done above.

                // Step 2.
                $new_total_remainder = $discountInstrument->getDiscountedPrice();

                // Step 3.
                $discount_amount = \max(0, \array_sum($remainders) - $new_total_remainder);

                // Step 4.
                $discount_shares = $this->tricklingShare('up', $discount_amount, $remainders);
                break;
            default:
                throw new \InvalidArgumentException('Invalid discount type.');
        }

        // Record the discount
        $unit_quantity_group_number = 0;
        foreach ($discountLineAgents as $hash => $discountLineAgent) {
            $discountLineAgent->addItem(
                $discountInstrument,
                $discount_shares[$hash],
                $unit_quantity,
                $unit_quantity_group,
                ++$unit_quantity_group_number,
                $this->orderId,
                $this->userId,
                $this->adminId,
                $this->tenantId,
                $this->getProcessor()
            );
        }
    }

    /**
     * Add items to discount line list.
     *
     * @param  DiscountableDevice  $discountableDevice
     */
    protected function populateDiscountLines(DiscountableDevice $discountableDevice): void
    {
        foreach ($discountableDevice->getDiscountableDeviceLines() as $discountableDeviceLine) {
            $this->discountLineList->findOrAdd($discountableDevice, $discountableDeviceLine);
        }
    }

    /**
     * Replicate the discountable device lines by their quantities.
     * For example, if a discountable device line has a quantity of 3, then it will
     * be replicated 3 times in the returned array.
     *
     * @return array<DiscountableDeviceLine>
     */
    private function replicateDiscountableDeviceLines(DiscountableDeviceLine ...$discountableDeviceLines): array
    {
        $replicatedDiscountableDeviceLines = [];

        foreach ($discountableDeviceLines as $discountableDeviceLine) {
            $quantity = $discountableDeviceLine->getDiscountableDeviceLineQuantity();

            for ($i = 0; $i < $quantity; $i++) {
                $replicatedDiscountableDeviceLines[] = $discountableDeviceLine;
            }
        }

        return $replicatedDiscountableDeviceLines;
    }

    /**
     * Count the number of times a discountable a discount instrument is redeemable.
     *
     * @param  int  $times_requested The number of times the discount instrument is requested to be redeemed.
     */
    protected function timesRedeemable(DiscountInstrument $discountInstrument, int $times_requested): int
    {
        $times_redeemable = 0;
        for ($i = 1; $i <= $times_requested; $i++) {
            if ($discountInstrument->isRedeemable($i, $this->userId, $this->orderId, $this->adminId, $this->tenantId)) {
                $times_redeemable++;
            } else {
                break;
            }
        }

        return $times_redeemable;
    }

    /**
     * Group the discountable device lines by their type and id.
     *
     * @return array<string,array<DiscountableDeviceLine>> The key of the outer array is the type_id of the discountable device line.
     */
    private function groupDiscountableDeviceLines(DiscountableDeviceLine ...$discountableDeviceLines): array
    {
        $discountableDeviceLinesGroup = [];

        foreach ($discountableDeviceLines as $discountableDeviceLine) {
            $discountableDeviceLinesGroup[$discountableDeviceLine->getDiscountableDeviceLineType().'_'.$discountableDeviceLine->getDiscountableDeviceLineIdentifier()][] = $discountableDeviceLine;
        }

        return $discountableDeviceLinesGroup;
    }

    /**
     * Get the Discountable device lines for the given discountable device. And optionally filter by the given discountable.
     *
     * @param  DiscountableDevice  $discountableDevice
     * @param  Discountable|null  $discountable When not null only the items that has the given discountable are returned.
     * @return array<int,DiscountableDeviceLine>
     *
     * @throws \InvalidArgumentException When discountable device lines are not of type DiscountableDeviceLine.
     */
    private function discountableDeviceLinesFor(DiscountableDevice $discountableDevice, ?Discountable $discountable = null): array
    {
        $discountableDeviceLine = $discountableDevice->getDiscountableDeviceLines();

        // Validate discountable device lines are of correct type.
        foreach ($discountableDeviceLine as $item) {
            if (! $item instanceof DiscountableDeviceLine) {
                throw new \InvalidArgumentException('Items returned by an implementation of '
                .DiscountableDevice::class.'::getDiscountableDeviceLines() 
                must be of type '.DiscountableDeviceLine::class);
            }
        }

        // Return all discountable device lines if no discountable is given.
        if (is_null($discountable)) {
            return $discountableDeviceLine;
        }

        // Filter by discountable.
        return array_filter($discountableDeviceLine, function (DiscountableDeviceLine $item) use ($discountable) {
            return
                ($item->getDiscountable()->getDiscountableIdentifier() == $discountable->getDiscountableIdentifier())
                and
                ($item->getDiscountable()->getDiscountableType() == $discountable->getDiscountableType());
        });
    }

    /**
     * Get registered discount lines.
     */
    protected function discountLinesFor(DiscountableDevice $discountableDevice): DiscountLineList
    {
        return $this->discountLineList->filter(function (DiscountLine $discountLine) use ($discountableDevice) {
            return $discountLine->getDiscountableDevice() === $discountableDevice;
        });
    }

    /**
     * Get the initial total discountable amount for a discountable device.
     *
     * @param  DiscountableDevice  $discountableDevice
     * @return int
     */
    protected function totalAmountFor(DiscountableDevice $discountableDevice): int
    {
        $subtotal = 0;
        foreach ($discountableDevice->getDiscountableDeviceLines() as $discountableDeviceLine) {
            $subtotal += $discountableDeviceLine->getDiscountableDeviceLineAmount() * $discountableDeviceLine->getDiscountableDeviceLineQuantity();
        }

        return $subtotal;
    }

    /**
     * Share an amount into $count places and return each share as array.
     * Any remainder is given to the first entry in the returned array.
     *
     * @param  int  $amount
     * @param  int  $count
     * @return array
     */
    protected function share(int $amount, int $count): array
    {
        $distributor = new AmountDistributor;
        $distributor->share($amount, $count);
        $shares = $distributor->getDistributedAmounts();
        $shares[0] += $distributor->getRemainder();

        return $shares;
    }

    /**
     * Share an amount by using a trickling strategy.
     *
     * @param  string  $strategy 'up' or 'down'.
     * @param  int  $amount
     * @param  array<string,int>  $capacities An array of discount line agent [hash=>capacity]. Where capacity is the total amount that can be distributed to the corresponding discount line.
     * @return array<string,int> An array of discount line hash=>amount.
     */
    protected function tricklingShare(string $strategy, int $amount, $capacities): array
    {
        $distributor = new AmountDistributor();

        //
        if ($strategy == 'up') {
            $distributor->trickleUp($amount, $capacities);
        } else {
            $distributor->trickleDown($amount, $capacities);
        }

        return $distributor->getDistributedAmounts();
    }

    /**
     * Determine the actual amount that will be discounted for amount_off and
     * percent_off discount types.
     *
     * @param  int  $subtotal
     * @param  DiscountInstrument  $discountInstrument
     * @return int
     *
     * @throws \InvalidArgumentException When discount type is not supported.
     */
    protected function off(int $subtotal, DiscountInstrument $discountInstrument): int
    {
        // Throw exception if discount type is not supported.
        if (! in_array($discountInstrument->getDiscountType(), [DiscountTypes::AMOUNT_OFF, DiscountTypes::PERCENT_OFF])) {
            throw new \InvalidArgumentException('Discount type '.$discountInstrument->getDiscountType().' is not supported by '.__METHOD__);
        }

        return $discountInstrument->getDiscountType() == DiscountTypes::AMOUNT_OFF
                ? $this->amountOff($subtotal, $discountInstrument)
                : $this->percentOff($subtotal, $discountInstrument);
    }

    /**
     * Determine the actual amount that will be discounted for an absolute
     * discount.
     *
     * @param  int  $subtotal
     * @param  DiscountInstrument  $instrument
     * @return int
     */
    protected function amountOff(int $subtotal, DiscountInstrument $instrument): int
    {
        return min(intval($instrument->getAmountOff()), $subtotal);
    }

    /**
     * Determine the actual amount that will be discounted for a percentage discount.
     *
     * @param  int  $subtotal
     * @param  DiscountInstrument  $instrument
     * @return int
     */
    protected function percentOff(int $subtotal, DiscountInstrument $instrument): int
    {
        /**
         * Precision is not necessary.
         */
        $off = floor($instrument->getPercentOff() * $subtotal) / 100;

        return min($off, $subtotal);
    }

    /**
     * Get the set discount instruments
     *
     * @return array<int,\Autepos\Discount\Contracts\DiscountInstrument>
     */
    public function getDiscountInstruments(): array
    {
        return $this->discountInstruments;
    }

    /**
     * Get the selected discount instruments found to be valid.
     *
     * @return array
     */
    public function getValidDiscountInstruments(): array
    {
        return $this->validDiscountInstruments;
    }

    /**
     * Get the value of discountableDevices
     *
     * @return array<int,\Autepos\Discount\Contracts\DiscountableDevice>
     */
    public function getDiscountableDevices(): array
    {
        return $this->discountableDevices;
    }

    /**
     * Get the value of orderId
     */
    public function getOrderId(): int|string|null
    {
        return $this->orderId;
    }

    /**
     * Get the value of userId
     *
     * @return int|string|null
     */
    public function getUserId(): int|string|null
    {
        return $this->userId;
    }

    /**
     * Get the value of adminId
     *
     * @return int|string|null
     */
    public function getAdminId(): int|string|null
    {
        return $this->adminId;
    }

    /**
     * Get the value of tenantId
     *
     * @return int|string|null
     */
    public function getTenantId(): int|string|null
    {
        return $this->tenantId;
    }
}
