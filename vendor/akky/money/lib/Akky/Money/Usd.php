<?php

namespace Akky\Money;

class Usd extends Currency
{
    /**
     * find and convert financial number expressions from plain text
     *
     * fraction will be counted
     *
     * @assert ("abcde") == "abcde"
     * @assert ("1 million dollars") == '1 million dollars(1000000)'
     * @assert ("23.4 billion Dollars") == '23.4 billion Dollars(23400000000)'

     * @assert ("350 US dollars") == '350 US dollars(350)'
     * @assert ("1 dollar") == '1 dollar(1)'
     * @assert ("$7.50") == '$7.50(7.5)'
     * @assert ("$199,666") == '$199,666(199666)'
     * @assert ("$2,000,000.12") == '$2,000,000.12(2000000.12)'
     * @assert ("$2,000,000.12345") == '$2,000,000.12345(2000000.12345)'
     * @assert ("399332ドル") == '399332ドル(399332)'
     * @assert ("$199") == '$199(199)'
     * @assert ("キンドル") == 'キンドル'
     * @assert ("円ドル") == '円ドル'
     * @assert ("6.99ドル") == '6.99ドル(6.99)'
     * @assert ("399ドル") == '399ドル(399)'
     * @assert ("38万2400ドル") == '38万2400ドル(382400)'
     * @assert ("748億6000ドル") == '748億6000ドル(74800006000)'
     * @assert ("1234567890ドル") == '1234567890ドル(1234567890)'
     * @assert ("6.85億ドル") == '6.85億ドル(685000000)'
     * @assert ("この34,000ドルを支払うには") == 'この34,000ドル(34000)を支払うには'
     * @assert ("経済効果が、最大で約80億ドルになると分析した") == '経済効果が、最大で約80億ドル(8000000000)になると分析した'
     */
    public function apply($text) {
        $count = 0;
        $results = preg_replace_callback(
            '/'
            .'(?:'

            // pattern all with digits
            .'(?<firstsign>\$)?'
            .'(?<dollar1>\d[\d\,]{4,18}(?:\.\d+)?)'
            // if prefix $ did not exist, then check suffix $
            .'(?(firstsign)|(?:米)?ドル)'

            .'|'

            // pattern with unit(s) in English
            .'(?<firstsign2>\$)?'
            .'(?:(?P<trillion>\d[\d\,]{0,2}(?:\.\d+)?)\strillion)?'
            .'(?:(?P<billion>\d[\d\,]{0,2}(?:\.\d+)?)\sbillion)?'
            .'(?:(?P<million>\d[\d]{0,2}(?:\.\d+)?)\smillion)?'
            .'(?:(?P<thousand>\d[\d]{0,2}(?:\.\d+)?)\sthousand)?'
            .'(?<dollar2>\d[\d]{0,2}(?:\.\d+)?)?'
            // if prefix $ did not exist, then check suffix $
            .'(?(firstsign2)|(?:\sUS)?\sdollars?)'

            .'|'

            // pattern with unit(s) in Japanese
            .'(?<firstsign3>\$)?'
            .'(?:(?P<chou>\d[\d\,]{0,3}(?:\.\d+)?)兆)?'
            .'(?:(?P<oku>\d[\d]{0,3}(?:\.\d+)?)億)?'
            .'(?:(?P<man>\d[\d]{0,3}(?:\.\d+)?)万)?'
            .'(?<dollar3>\d[\d]{0,3}(?:\.\d+)?)?'
            // if prefix $ did not exist, then check suffix $
            .'(?(firstsign3)|(?:米)?ドル)'

            .')'
            .'/i',
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
     * @assert (array("dollar1"=>"12,345")) == "12345"
     * @assert (array("dollar1"=>"12,345.67")) == "12345.67"
     *
     * case 2:
     * @assert (array("trillion"=>"3")) == "3000000000000"
     * @assert (array("billion"=>"1","million"=>30,"dollar2"=>600)) == "1030000600"
     * @assert (array("million"=>1)) == "1000000"
     * @assert (array("million"=>1.28)) == "1280000"
     * @assert (array("thousand"=>12,"dollar2"=>500)) == "12500"
     *
     * case 3:
     * @assert (array("chou"=>"","oku"=>48,"man"=>6000)) == "4860000000"
     * @assert (array("chou"=>1,"oku"=>2,"man"=>3,"dollar3"=>4)) == "1000200030004"
     * @assert (array("chou"=>12,"oku"=>3000)) == "12300000000000"
     * @assert (array("chou"=>12,"man"=>500)) == "12000005000000"
     * @assert (array("dollar3"=>"6.99")) == "6.99"
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
                if (!empty($value)) {
                    $amount += $value * 10000 * 10000 * 10000;
                }
                break;
            case 'oku':
                $amount += $value * 10000 * 10000;
                break;
            case 'man':
                $amount += $value * 10000;
                break;
            case 'dollar1':
            case 'dollar2':
            case 'dollar3':
                $amount += $value;
                break;
            default:
            }
        }
        return $amount;
    }
}
