<?php

/**
 * セキュリティ関連のHTTPヘッダを設定する処理を共通化するトレイト
 * Controller_Base / Controller_Auth など、継承関係が異なるコントローラ間で共有する
 */
trait Trait_Headers
{
	protected function set_security_headers()
	{
		// クリックジャッキング対策(他サイトのiframeへの埋め込みを禁止)
		header('X-Frame-Options: SAMEORIGIN');

		// MIMEタイプの推測を禁止
		header('X-Content-Type-Options: nosniff');

		// 外部サイトへの遷移時にURLを送らない
		header('Referrer-Policy: same-origin');
	}
}
