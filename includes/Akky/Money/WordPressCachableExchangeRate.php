<?php

namespace Akky\Money;

/**
 * Exchange Rate fetcher from the web
 *
 * This currently uses WordPress cache function so unable to use independently.
 *
 */
class WordPressCachableExchangeRate extends ExchangeRate
{
    /**
     * get exchange rates and cache in WordPress
     *  use WordPress object cache functions(get_transient, set_transient);
     *
     * @assert("USD", "JPY") > 50
     * @assert("USD", "JPY") < 130
     */
    public function getRate($from, $to)
    {
        $cache_key = 'acc_' . $from . '_' . $to;
        $rate = get_transient( $cache_key );
        if ($rate === false) {
            $rate = $this->doGetRate($from, $to);
            set_transient($cache_key, $rate, 60*60*24);
        }

        return $rate;
    }
}
