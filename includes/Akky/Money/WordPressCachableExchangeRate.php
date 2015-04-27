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
            if ($rate != false) {
                set_transient($cache_key, $rate, $this->_cache_period);
            }
        }

        return $rate;
    }

    /**
     * get exchange rates from Yahoo!
     *  override to use WordPress's remote fetch function
     *  to let it work even if allow_url_fopen === false
     *
     * @assert("USD", "JPY") > 50
     * @assert("USD", "JPY") < 130
     */
    public function doGetRate($from, $to) {
        if (isset($this->_cached[$from][$to])) {
            return $this->_cached[$from][$to];
        }
        $url = "http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%3D%22{$from}{$to}%22&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
        $response = wp_remote_get($url);
        if (is_wp_error($response)
            || ($response['response']['code'] !== 200)) {
            return false;
        }
        $json = $response['body'];
        $decoded = json_decode($json, true);
        $rate = $decoded['query']['results']['rate']['Rate'];

        $this->_cached[$from][$to] = $rate;
        return $rate;
    }
}
