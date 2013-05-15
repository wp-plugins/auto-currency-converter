<?php

namespace Akky\Money;

class JpyFormatter
{
    /**
     * convert number expressions to value
     *
     * @assert ('34000', 'en') == '¥34,000'
     * @assert ('123456.789', 'en') == '¥123,457'
     * @assert ('1234567890', 'en') == '¥1,234,567,890'
     */
    public static function format($value, $locale = "en") {
		switch($locale) {
		case 'ja':
			return self::formatInJapanese($value);
			break;
		case 'en':
		default:
	        $numberFormatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
	        return $numberFormatter->formatCurrency($value, 'JPY');
		}
    }
    /**
     * convert number expressions to value
     *
     * @assert ("34000") == "3万4000円"
     * @assert ("123456.789") == "12万3457円"
     * @assert ("1234567890") == "12億3456万7890円"
     * @assert ("92610000000000") == "92兆6100億円"
     */
    public static function formatInJapanese($value) {
        $isApproximate = false;
        $formatted = '';
        if ($value > 1000000000000) {
            if ($value % 1000000000000 !== 0) {
                $isApproximate = true;
            }
            $unitValue = floor($value / 1000000000000);
            $formatted .= $unitValue . '兆';
            $value -= $unitValue * 1000000000000;
        }
        if ($value > 100000000) {
            if ($value % 100000000 !== 0
                && !$isApproximate) {
                $isApproximate = true;
            }
            $unitValue = floor($value / 100000000);
            $formatted .= $unitValue . '億';
            $value -= $unitValue * 100000000;
        }
        if ($value > 10000) {
            if ($value % 10000 !== 0
                && !$isApproximate) {
                $isApproximate = true;
            }
            $unitValue = floor($value / 10000);
            $formatted .= $unitValue . '万';
            $value -= $unitValue * 10000;
        }
        if ($value != 0) {
            $formatted .= round($value);
        }
        return $formatted . '円';
    }
}
