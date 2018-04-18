<?php

/*
 * Source: https://gist.github.com/mathiasverraes/9046427
 */

function it($m, $p)
{
    echo "\033[" . ($p ? "32m✔" : "31m✘") . " It $m\033[0m\n";
    if (!$p) {
        $GLOBALS['f'] = 1;
    }
}

function done()
{
    if (@$GLOBALS['f']) {
        die(1);
    }
}

function throws($exp, \Closure $cb)
{
    try {
        $cb();
    } catch (\Exception $e) {
        return $e instanceof $exp;
    }
    return false;
}

function floats_are_equal($left, $right)
{
    return abs($left - $right) < 0.0000000001;
}
