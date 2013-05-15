<?php

namespace Akky\Money;

/**
 *
 * @todo full-width digits are not supported
 *
 */
class Jpy extends Currency
{
    /**
     * find and convert financial number expressions from plain text
     *
     * fraction will not be counted, as JPY fraction is too small
     *
     * @assert ("abcde") == "abcde"
     * @assert ("1 million yen") == '1 million yen(1000000)'
     * @assert ("23.4 billion Yen") == '23.4 billion Yen(23400000000)'
     * @assert ("1 yen") == '1 yen(1)'
     * @assert ("\\15973280") == '\\15973280(15973280)'
     * @assert ("3194万6560円") == '3194万6560円(31946560)'
     * @assert ("15920円") == '15920円(15920)'
     * @assert ("半円") == '半円'
     * @assert ("円ドル") == '円ドル'
     * @assert ("559円") == '559円(559)'
     * @assert ("3万1920円") == '3万1920円(31920)'
     * @assert ("3059万2000円") == '3059万2000円(30592000)'
     * @assert ("３０５９万２０００円") == '3059万2000円(30592000)'
     * @assert ("5兆9840億48万円") == '5兆9840億48万円(5984000480000)'
     * @assert ("987億6543万1200円") == '987億6543万1200円(98765431200)'
     * @assert ("548億円") == '548億円(54800000000)'
     * @assert ("この272万円を支払うには") == 'この272万円(2720000)を支払うには'
     * @assert ("経済効果が、最大で約6400億円になると分析した") == '経済効果が、最大で約6400億円(640000000000)になると分析した'
     * @assert ("この272万円($34,000)を支払うには") == 'この272万円(2720000)($34,000)を支払うには'
     */
    public function apply($text) {
		if (function_exists('mb_convert_kana')) {
			$text = mb_convert_kana($text, 'n', 'UTF-8');
		}

        $count = 0;
        $results = preg_replace_callback(
            '/'
            .'(?:'

            // pattern all with digits
            .'(?<firstsign>\\\\)?'
            .'(?<yen1>\d[\d\,]{4,18})'
            // if prefix $ did not exist, then check suffix $
            .'(?(firstsign)|(?:日本)?円)'

            .'|'

            // pattern with unit(s) in English
            .'(?<firstsign2>\\\\)?'
            .'(?:(?P<trillion>\d[\d\,]{0,2}(?:\.\d+)?)\strillion)?'
            .'(?:(?P<billion>\d[\d\,]{0,2}(?:\.\d+)?)\sbillion)?'
            .'(?:(?P<million>\d[\d]{0,2}(?:\.\d+)?)\smillion)?'
            .'(?:(?P<thousand>\d[\d]{0,2}(?:\.\d+)?)\sthousand)?'
            .'(?<yen2>\d[\d]{0,2}(?:\.\d+)?)?'
            // if prefix $ did not exist, then check suffix $
            .'(?(firstsign2)|(?:\sJapanese)?\syens?)'

            .'|'

            // pattern with unit(s) in Japanese
            .'(?<firstsign3>\\\\)?'
            .'(?:(?P<chou>\d[\d\,]{0,3}(?:\.\d+)?)兆)?'
            .'(?:(?P<oku>\d[\d]{0,3}(?:\.\d+)?)億)?'
            .'(?:(?P<man>\d[\d]{0,3}(?:\.\d+)?)万)?'
            .'(?<yen3>\d[\d]{0,3}(?:\.\d+)?)?'
            // if prefix $ did not exist, then check suffix $
            .'(?(firstsign3)|(?:日本)?円)'

            .')/i',
            array(&$this, 'processNumber'),
            $text,
            -1,
            $count
        );
        return $results;
    }

    /**
     * normalized units with absolute amount of money
     *
     * case 1:
     * @assert (array("yen1"=>"12,345")) == "12345"
     *
     * case 2:
     * @assert (array("trillion"=>"3")) == "3000000000000"
     * @assert (array("billion"=>"1","million"=>30,"yen2"=>600)) == "1030000600"
     * @assert (array("million"=>1)) == "1000000"
     * @assert (array("thousand"=>12,"yen2"=>500)) == "12500"
     *
     * case 3:
     * @assert (array("chou"=>"","oku"=>48,"man"=>6000)) == "4860000000"
     * @assert (array("chou"=>1,"oku"=>2,"man"=>3,"yen3"=>4)) == "1000200030004"
     * @assert (array("chou"=>12,"oku"=>3000)) == "12300000000000"
     * @assert (array("chou"=>12,"man"=>500)) == "12000005000000"
     * @assert (array("yen3"=>"6.99")) == "6.99"
     * @assert (array("man"=>2.7)) == "27000"
     * @assert (array("oku"=>168)) == "16800000000"
     */
    public function normalize($numbers) {
        $amount = 0;
        foreach($numbers as $key => $value) {
            $value = str_replace(',', '', $value);

            switch((string)$key) {
                // cast for sure as the array may have other key/values
            case 'trillion':
                $amount += $value * 1000 * 1000 * 1000 * 1000;
                break;
            case 'billion':
                $amount += $value * 1000 * 1000 * 1000;
                break;
            case 'million':
                $amount += $value * 1000 * 1000;
                break;
            case 'thousand':
                $amount += $value * 1000;
                break;
            case 'chou':
                $amount += $value * 10000 * 10000 * 10000;
                break;
            case 'oku':
                $amount += $value * 10000 * 10000;
                break;
            case 'man':
                $amount += $value * 10000;
                break;
            case 'yen1':
            case 'yen2':
            case 'yen3':
                $amount += $value;
                break;
            default:
            }
        }
        return $amount;
    }
}
