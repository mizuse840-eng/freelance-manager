<?php
/**
 * データベース設定
 *
 * 接続情報はソースコードに持たず、環境変数から読み込む。
 * 値は docker-compose.yml の php サービスの environment で定義している。
 *
 * 環境の違いは環境変数で表現するため、config/development/db.php のような
 * 環境別の設定ファイルは置いていない。FuelPHPの雛形にあった
 * production / staging / test の db.php は、接続先もパスワードも雛形のままで
 * 使われていなかったため削除した。
 */
return array(
	'default' => array(
		'connection'  => array(
			'dsn'        => 'mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_NAME'),
			'username'   => getenv('DB_USER'),
			'password'   => getenv('DB_PASSWORD'),
		),
		'charset' => 'utf8mb4',
	),
);
