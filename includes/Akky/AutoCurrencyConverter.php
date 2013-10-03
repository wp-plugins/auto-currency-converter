<?php

namespace Akky;

require_once __DIR__. '/Money/WordPressCachableExchangeRate.php';

/** @Plugin */
class AutoCurrencyConverter
{
    const PLUGIN_KEY = 'auto_currency_converter';
    const DISPLAY_NAME = 'Auto Currency Converter';
    const DB_OPTION_KEY = self::PLUGIN_KEY;
    protected $_tagger = array();
    public $_target_curerncy = 'usd';
    public $_exchangeRate = null;

    private static function dummyForI18nResource()
    {
        // Poedit can not pick up resource text in constant
        //   so these are needed to be somewhere in code
        __( 'Auto Currency Converter' , self::PLUGIN_KEY );
        __( 'Auto Currency Converter Setting' , self::PLUGIN_KEY );
    }

    public function __construct()
    {
        $this->_exchangeRate = new \Akky\Money\WordPressCachableExchangeRate();

        $this->_taggers[] = new \Akky\Money\Usd(
            function($value) {
				if ($value === 0) { return; }
                return '[acc value="' . $value . '" currency="usd"]';
            }
        );
        $this->_taggers[] = new \Akky\Money\Jpy(
            function($value) {
				if ($value === 0) { return; }
                return '[acc value="' . $value . '" currency="jpy"]';
            }
        );
    }

    /**
     * pass-1: let converters to find money notations and add short codes
     *
     * @Filter(tag="the_content")
     */
    public function tagifyConvertedMoney($text)
    {
        // if the post was modified before the begin date set by plugin, pass.
        $options = get_option(self::DB_OPTION_KEY);
        if (is_array($options) && array_key_exists('begin_date', $options)) {
            $begin_date = $options['begin_date'];
            $modified_date = get_the_modified_date( 'Y-m-d' );
            // we can asssume that the both date format are the same in Y-m-d
            if ($begin_date > $modified_date) {
                return $text;
            }
        }

        foreach ($this->_taggers as $tagger) {
            $text = $tagger->apply($text);
        }

        return $text;
    }

    /**
     * pass-2: extract short code
     *
     * @Shortcode(tag="acc")
     */
    public function shortCode( $params )
    {
        // parameters check
        if (!array_key_exists('value', $params)) {
            return '';
        }
        if (!array_key_exists('currency', $params)) {
            return '';
        }

        $value = (float) $params['value'];
        static $supportedCurrencies = array(
            'jpy', 'usd'
        );
        $currency = $params['currency'];
        if (!in_array($currency, $supportedCurrencies)) {
            $currency = 'jpy';
        }
        static $supportedLocales = array(
            'ja', 'en'
        );
        if (array_key_exists('locale', $params)) {
            $locale = $params['locale'];
        } else {
            $locale = substr(get_locale(), 0, 2);
        }
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'en';
        }
		try {
	        switch ($currency) {
	        case 'usd':
	            $jpy = $this->_exchangeRate->convert($value, 'usd', 'jpy');
	            if ($jpy > 1000) { $jpy = floor($jpy); }

	            return '(' . \Akky\Money\JpyFormatter::format($jpy, $locale) . ')';
	        case 'jpy':
	        default:
	            $usd = $this->_exchangeRate->convert($value, 'jpy', 'usd');
	            if ($usd > 1000) { $usd = floor($usd); }

	            return '(' . \Akky\Money\UsdFormatter::format($usd, $locale) . ')';
	        }
		} catch (\RuntimeException $ex) {
			// for case rate info unavailable, do nothing
			return '';
		}
    }

    /**
     *
     * @Action(tag="admin_menu")
     */
    public function registerMenu()
    {
        add_options_page(
            __( self::DISPLAY_NAME . ' Setting', self::PLUGIN_KEY ),
            __( self::DISPLAY_NAME, self::PLUGIN_KEY ),
            'administrator',
            self::PLUGIN_KEY,
            array( &$this, 'callbackRenderForm')
        );
    }

    public function callbackRenderForm()
    {
        echo '<div class="wrap">';
        echo '<div class="icon32" id="icon-options-general"></div>';
        echo '<h2>' . __( self::DISPLAY_NAME . ' Setting', self::PLUGIN_KEY ), '</h2>';
        echo '<div class="' . self::PLUGIN_KEY . '">';
        echo '<form action="options.php" method="post">';
        settings_fields( self::PLUGIN_KEY );
        do_settings_sections( self::PLUGIN_KEY );
        submit_button();
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }

    /**
     *
     * @Action(tag="admin_init")
     */
    public function registerSettings()
    {
        register_setting(
            self::PLUGIN_KEY,
            self::DB_OPTION_KEY,
            array( &$this, 'validateOptions')
        );

        $this->registerSectionUsage();
        $this->registerSectionDateBegin();
    }

    /**
     *
     * @Action(tag="admin_enqueue_scripts")
     */
    public function registerJqueryDatepicker()
    {
        $plugin_root_dir = dirname(dirname(__FILE__));
        wp_enqueue_style( 'jquery-ui-fresh', plugins_url( 'css/jquery-ui-fresh.css' , $plugin_root_dir ) );
        wp_enqueue_script( 'register-date-picker-js', plugins_url( 'js/register-datepicker.js' , $plugin_root_dir) , array( 'jquery', 'jquery-ui-datepicker' ), '1.0.0', true );
    }

    // ------------------------------------------------------------------
    // ---------------------- register sections -------------------------
    // ------------------------------------------------------------------

    protected function registerSectionUsage()
    {
        $sectionName = 'acc_usage';
        add_settings_section(
            $sectionName,
            __( 'Usage', self::PLUGIN_KEY ),
            array( &$this, 'callbackRenderUsage' ),
            self::PLUGIN_KEY
        );
    }

    protected function registerSectionDateBegin()
    {
        $sectionName = 'acc_date_begin';

        add_settings_section(
            $sectionName,
            __( 'Date to begin conversion', self::PLUGIN_KEY ),
            array( &$this, 'callbackRenderBeginDate' ),
            self::PLUGIN_KEY
        );

        add_settings_field(
            'eia_affiliate_jp',
            __( 'starting date from where the conversion works', self::PLUGIN_KEY ),
            array( &$this, 'callbackRenderBeginDateField' ),
            self::PLUGIN_KEY,
            $sectionName
        );

    }

    // ------------------------------------------------------------------
    // -------------------------- valildator ----------------------------
    // ------------------------------------------------------------------

    public function validateOptions( $input )
    {
        if (is_null($input) || empty($input)) {
            return $input;
        }
        if (!array_key_exists('begin_date', $input)) {
            return $input;
        }
        $begin_date = $input['begin_date'];

        if (is_null($begin_date) || empty($begin_date)) {
            $input['begin_date'] = '';

            return $input;
        }

        if (!\DateTime::createFromFormat('Y-m-d', $begin_date)) {
            add_settings_error(
                self::PLUGIN_KEY,
                'invalid_begin_date',
                __( 'The date is not a proper format.', self::PLUGIN_KEY ),
                'error'
            );

            $input['begin_date'] = '';
        }

        return $input;
    }

    // ------------------------------------------------------------------
    // -------------------------- renderers -----------------------------
    // ------------------------------------------------------------------

    public function callbackRenderUsage()
    {
        echo '<p>' . __( 'Add exchanged ammount after the money notations in posts/pages.', self::PLUGIN_KEY ) . '</p>';
        echo '<blockquote>' . __( 'example: <br />"$199,666"<br /> in a post will be changed to <br />"$199,666(￥15,973,280)"<br /><br />The real ammount will be different as the plugin to use the latest exchange rate."', self::PLUGIN_KEY ) . '</blockquote>';
        echo '<p>' . __( 'At now, only US dollars and Japanese yen are supported.', self::PLUGIN_KEY ) . '</p>';
    }

    public function callbackRenderBeginDate()
    {
        echo '<p>' . __( 'Set a date after which you make the conversion effective. Only on the pages/posts which modified after the date will have converted money info, so your past posts which you manually added similar info will not be affected.', self::PLUGIN_KEY ) . '</p>';
    }

    public function callbackRenderBeginDateField()
    {
        $options = get_option(self::DB_OPTION_KEY);
        $begin_date = '';
        if (is_array($options) && array_key_exists('begin_date', $options)) {
            $begin_date = $options['begin_date'];
        }

        echo '<input name="' . self::DB_OPTION_KEY . '[begin_date]"' . ' id="acc_begin_date" type="text"' . ' size="10" maxlength="10"' . ' value="' . $begin_date . '" />';
    }
}
