<?php

/**
 * Returns the discount amount.
 * Amount * Discount%
 */
function calculateDiscount(int|float $amount, int|float $discount = 0): int|float
{
    return $amount * ($discount / 100);
}

/**
 * Returns the amount after discount.
 * Amount - Discount$
 */
function calculatePostDiscount(int|float $amount, int|float $discount = 0): int|float
{
    return $amount - calculateDiscount($amount, $discount);
}

/**
 * Returns the inclusive taxes amount.
 * PostDiscount - PostDiscount / (1 + TaxRate)
 */
function calculateInclusiveTaxes(int|float $amount, int|float $discount = 0, int|float $inclusiveTaxRate = 0): int|float
{
    return calculatePostDiscount($amount, $discount) - (calculatePostDiscount($amount, $discount) / (1 + ($inclusiveTaxRate / 100)));
}

/**
 * Returns the amount after discount and included taxes.
 * PostDiscount - InclusiveTaxes$
 */
function calculatePostDiscountLessInclTaxes(int|float $amount, int|float $discount = 0, int|float $inclusiveTaxRates = 0): int|float
{
    return calculatePostDiscount($amount, $discount) - calculateInclusiveTaxes($amount, $discount, $inclusiveTaxRates);
}

/**
 * Returns the amount of an inclusive tax.
 * PostDiscountLessInclTaxes * (Tax / 100)
 */
function calculateInclusiveTax(int|float $amount, int|float $discount = 0, int|float $inclusiveTaxRate = 0, int|float $inclusiveTaxRates = 0): int|float
{
    return calculatePostDiscountLessInclTaxes($amount, $discount, $inclusiveTaxRates) * ($inclusiveTaxRate / 100);
}

/**
 * Returns the exclusive tax amount.
 * PostDiscountLessInclTaxes * TaxRate
 */
function checkoutExclusiveTax(int|float $amount, int|float $discount = 0, int|float $exclusiveTaxRate = 0, int|float $inclusiveTaxRates = 0): int|float
{
    return calculatePostDiscountLessInclTaxes($amount, $discount, $inclusiveTaxRates) * ($exclusiveTaxRate / 100);
}

/**
 * Calculate the total, including the exclusive taxes.
 * PostDiscount + ExclusiveTax$
 */
function checkoutTotal(int|float $amount, int|float $discount = 0, int|float $exclusiveTaxRates = 0, int|float $inclusiveTaxRates = 0): int|float
{
    return calculatePostDiscount($amount, $discount) + checkoutExclusiveTax($amount, $discount, $exclusiveTaxRates, $inclusiveTaxRates);
}

/**
 * Get the enabled payment processors.
 */
function enabledPaymentProcessors(): array
{
    $paymentProcessors = config('payment.processors');

    foreach ($paymentProcessors as $key => $value) {
        if (!config('settings.' . $key)) {
            unset($paymentProcessors[$key]);
        }
    }

    return $paymentProcessors;
}
