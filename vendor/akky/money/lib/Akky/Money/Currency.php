<?php

namespace Akky\Money;

/**
 *
 *
 */
abstract class Currency
{
	protected $_callbackConvert = null;
    function __construct(/*callable - not works on PHP 5.3*/ $callbackConvert = null) {
		if (!is_null($callbackConvert)) {
			$this->_callbackConvert = $callbackConvert;
		}
    }

    abstract public function apply($text);
    abstract public function normalize($numbers);

    /**
     * replace the found price with converted information
     */
    public function processNumber($matched) {
        $normalized = $this->normalize($matched);
		$callbackConvert = $this->_callbackConvert;
		if (is_callable($callbackConvert)) {
			$addings = $callbackConvert($normalized);
		} else {
			if ($normalized === 0) {
				$addings = '';
			} else {
				$addings = '(' . $normalized . ')';
			}
		}
        return $matched[0] . $addings;
    }

}
