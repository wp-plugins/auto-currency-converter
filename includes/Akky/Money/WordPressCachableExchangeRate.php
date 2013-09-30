<?php

namespace Akky\Money;

if ( !function_exists( 'get_transient' ) ) {
    // for test without WordPress
    function get_transient()
    {
        return false;
    }
    function set_transient()
    {
        return false;
    }
}

/**
 * Exchange Rate fetcher from the web
 *
 * This currently uses WordPress cache function so unable to use independently.
 *
 */
class WordPressCachableExchangeRate extends ExchangeRate
{
    protected $_cache_period = 2678400; // one month
    public function __construct($cache_period = 2678400)
    {
        $this->_cache_period = $cache_period;
    }

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
            set_transient($cache_key, $rate, $this->_cache_period);
        }

        return $rate;
    }
}
