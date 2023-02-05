<?php

namespace Autepos\Discount;

final class DiscountTypes
{
    /**
     *  A fixed amount discount type.
     *
     * @var string
     */
    public const AMOUNT_OFF = 'amount_off';

    /**
     * A percentage discount type.
     *
     * @var string
     */
    public const PERCENT_OFF = 'percent_off';

    /**
     * A buy N for the price of M discount type. This is a buy N get M free
     * discount which allows for BOGO type discounts such as buy 1 get 1
     * free; or any arbitrary such offer e.g. buy 3 for the price of 2.
     *
     * @var string
     */
    public const BUY_N_FOR_PRICE_OF_M = 'buy_n_for_price_of_m';

    /**
     * A buy N for the price discount type.
     * E.g. buy 3 for £25
     *
     * @var string
     */
    public const BUY_N_FOR_PRICE = 'buy_n_for_price';

    /**
     * Determine if the discount type is absolute.
     *
     * @param  string  $type
     * @return bool
     * @throws \InvalidArgumentException If the discount type is invalid.
     */
    public static function isAbsolute(string $type): bool
    {
        
        switch ($type) {
            case static::AMOUNT_OFF:
            case static::BUY_N_FOR_PRICE_OF_M:
            case static::BUY_N_FOR_PRICE:
                return true;
            case static::PERCENT_OFF:
                return false;                
            default:
                throw new \InvalidArgumentException("Invalid discount type: {$type}");
        }
    }
}
