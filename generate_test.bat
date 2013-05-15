call phpunit-skelgen --test -- Akky\Money\ExchangeRate vendor\Akky\Money\lib\Akky\Money\ExchangeRate.php Akky\Money\ExchangeRateTest tests/ExchangeRateTest.php
if not %ERRORLEVEL% == 0 (
	exit /b %ERRORLEVEL%
)

call phpunit-skelgen --test --bootstrap tests\bootstrap.php -- Akky\Money\Usd vendor\Akky\Money\lib\Akky\Money\Usd.php Akky\Money\UsdTest tests/UsdTest.php
if not %ERRORLEVEL% == 0 (
	exit /b %ERRORLEVEL%
)

call phpunit-skelgen --test --bootstrap tests\bootstrap.php -- Akky\Money\Jpy vendor\Akky\Money\lib\Akky\Money\Jpy.php Akky\Money\JpyTest tests/JpyTest.php
if not %ERRORLEVEL% == 0 (
	exit /b %ERRORLEVEL%
)

call phpunit-skelgen --test -- Akky\Money\JpyFormatter vendor\Akky\Money\lib\Akky\Money\JpyFormatter.php Akky\Money\JpyFormatterTest tests/JpyFormatterTest.php
if not %ERRORLEVEL% == 0 (
	exit /b %ERRORLEVEL%
)

call phpunit-skelgen --test -- Akky\Money\UsdFormatter vendor\Akky\Money\lib\Akky\Money\UsdFormatter.php Akky\Money\UsdFormatterTest tests/UsdFormatterTest.php
if not %ERRORLEVEL% == 0 (
	exit /b %ERRORLEVEL%
)

