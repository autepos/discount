# Introduction
A flexible discounting library for PHP with zero dependency. It is typically used to provide discounts on products, but can be used for any kind of discounting where flexibility is required.


# Features
- Zero dependency
- Flexible discounting
- Extensible
- Supports multiple discount types:
    - Percentage
    - Fixed amount
    - BOGO / Buy N get M free / Buy N for the Price of M
    - Buy N at a price
- Discount stacking: multiple discounts can be applied to a single item.
- Restricting discounts:
    - to specific items
    - to specific user/order
    - to a minimum purchase amount
    - to minimum quantity
- etc.

# Installation
Install the latest version with
```
composer require autepos/discount
```
# Basic Usage
```php
use Autepos\Discount\Processors\LinearDiscountProcessor;
use Autepos\Discount\Contracts\DiscountableDevice;
use Autepos\Discount\Contracts\DiscountInstrument;

class Order implements DiscountableDevice
{
    //...
}

class PromotionCode implements DiscountInstrument
{
    //...
}


$discountableDevice = new Order();
$discountInstrument = new PromotionCode();

$processor = new LinearDiscountProcessor();
$processor->addDiscountableDevice($discountableDevice)
          ->addDiscountInstrument($discountInstrument);
$discountLineList = $processor->calculate();

// Get the discount amount
$discountAmount = $discountLineList->getAmount();

// Persist the discount
$discountLineList->redeem();
```
# Adding a custom discount processor
```php
use Autepos\Discount\Processors\Contracts\DiscountProcessor;

class CustomDiscountProcessor implements DiscountProcessor
{
    public function getProcessor(): string
    {
        return 'custom';
    }

    //...
}
```

