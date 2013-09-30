<?php

namespace Akky\Money;

/**
 * Exchange Rate fetcher from the web
 */
class ExchangeRate {
    // in-process cache to avoid multiple API calls within the same process
    protected $_cached = array();

    /**
     * convert US dollar to Yen
     *
     * @assert(1, "USD", "JPY") > 50
     * @assert(1, "USD", "JPY") < 130
     */
    public function convert($src, $from, $to) {

        $rate = $this->getRate($from, $to);
        if (!$rate) {
            throw new \RuntimeException('rate info not found');
        }
        return $src * $rate;
    }

    /**
     * get exchange rates
     *
     * @assert("USD", "JPY") > 50
     * @assert("USD", "JPY") < 130
     */
    public function getRate($from, $to) {
        $rate = $this->doGetRate($from, $to);
		return $rate;
    }

    /**
     * get exchange rates from Yahoo!
     *
     * @assert("USD", "JPY") > 50
     * @assert("USD", "JPY") < 130
     */
    public function doGetRate($from, $to) {
        if (isset($this->_cached[$from][$to])) {
            return $this->_cached[$from][$to];
        }
        $url = "http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%3D%22{$from}{$to}%22&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
        $json = file_get_contents($url);
        if (!$json) {
            return false;
        }
        $decoded = json_decode($json, true);
        $rate = $decoded['query']['results']['rate']['Rate'];

        $this->_cached[$from][$to] = $rate;
        return $rate;
    }
}