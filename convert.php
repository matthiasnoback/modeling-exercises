<?php

require __DIR__ . '/TestFrameworkInATweet.php';

/**
 * @param string $from
 * @param string $to
 * @param float $amount
 * @return float|int
 */
function convert($from, $to, $amount)
{
    $exchangeRates = [
        'USD,EUR' => 1.240055,
        // ...
    ];

    $key = $from . ',' . $to;
    if (isset($exchangeRates[$key])) {
        $exchangeRate = $exchangeRates[$key];
    }
    else {
        $alternativeKey = $to . ',' . $from;
        if (isset($exchangeRates[$alternativeKey])) {
            $exchangeRate = 1/$exchangeRates[$alternativeKey];
        } else {
            throw new \RuntimeException('Cannot determine an exchange rate.');
        }
    }

    return $amount * 1/$exchangeRate;
}

it('converts from USD to EUR', floats_are_equal(8.0641584445851, convert('USD', 'EUR', 10.0)));
it('converts from EUR to USD', floats_are_equal(10.0, convert('EUR', 'USD', 8.0641584445851)));
it('fails when the exchange rate for a conversion cannot be determined', throws(\RuntimeException::class, function() {
    convert('GBP', 'EUR', 10.0);
}));
done();

/*
 * Design issues:
 *
 * - What's the currency of the $amount parameter? What should it be?
 * - What's the currency of the return value? What should it be?
 * - What's the precision of the returned amount? What should it be?
 * - There's way too many levels of indentation. How to fix this?
 * - `1/$exchangeRate` is repeated knowledge about "inverting" an exchange rate. How to solve it?
 *
 * By means of introducing proper (value) objects, all of these issues will disappear.
 */
