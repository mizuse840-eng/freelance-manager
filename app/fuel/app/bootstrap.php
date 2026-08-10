<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.8.2
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

// Bootstrap the framework - THIS LINE NEEDS TO BE FIRST!
require COREPATH.'bootstrap.php';

/**
 * FuelPHP 1.8.2 のコアは PHP 8.1 に対応しておらず、1リクエストあたり十数件の
 * deprecation を出す（Iterator実装の戻り値型、null を渡す組み込み関数呼び出しなど）。
 *
 * これらは public/index.php の error_reporting から除外済みだが、FuelPHPの
 * Errorhandler は「error_reporting に含まれない severity は画面表示せずログに書く」
 * という実装（COREPATH/classes/errorhandler.php の PhpErrorException::recover()）の
 * ため、除外するとかえって全件がログに記録されてしまう。
 * その結果ログが1日で10MBを超え、バインドマウント越しの追記が稀に失敗して
 * 全エンドポイントが500になる問題が起きていた。
 *
 * そこで「コア・パッケージが出した deprecation」だけをここで捨てる。
 * それ以外の severity と、アプリ側(APPPATH)が出した deprecation は
 * FuelPHP標準のハンドラにそのまま渡すので、ログ機能は従来どおり働く。
 */
set_error_handler(function ($severity, $message, $filepath, $line)
{
	if (($severity === E_DEPRECATED or $severity === E_USER_DEPRECATED)
		and (strpos($filepath, COREPATH) === 0 or strpos($filepath, PKGPATH) === 0))
	{
		// 握りつぶす（trueを返すとPHP標準のハンドラも動かない）
		return true;
	}

	// reset the autoloader
	\Autoloader::_reset();

	// deal with PHP bugs #42098/#54054
	if ( ! class_exists('Errorhandler'))
	{
		include COREPATH.'classes/errorhandler.php';
		class_alias('\Fuel\Core\Errorhandler', 'Errorhandler');
		class_alias('\Fuel\Core\PhpErrorException', 'PhpErrorException');
	}

	return \Errorhandler::error_handler($severity, $message, $filepath, $line);
});

// Add framework overload classes here
\Autoloader::add_classes(array(
	// Example: 'View' => APPPATH.'classes/myview.php',
));

// Register the autoloader
\Autoloader::register();

/**
 * Your environment.  Can be set to any of the following:
 *
 * Fuel::DEVELOPMENT
 * Fuel::TEST
 * Fuel::STAGING
 * Fuel::PRODUCTION
 */
Fuel::$env = Arr::get($_SERVER, 'FUEL_ENV', Arr::get($_ENV, 'FUEL_ENV', getenv('FUEL_ENV') ?: Fuel::DEVELOPMENT));

// Initialize the framework with the config file.
\Fuel::init('config.php');
