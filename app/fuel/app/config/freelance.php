<?php
/**
 * フリーランス案件管理アプリ 独自設定
 */
return array(

	/**
	 * 期限までの残り日数による警告レベルの閾値（日数）
	 */
	'deadline' => array(
		// この日数以内は「緊急」扱い
		'danger_days'  => 3,

		// この日数以内は「注意」扱い
		'warning_days' => 7,
	),

	/**
	 * 残り日数の表示に使うCSSクラス
	 */
	'deadline_class' => array(
		'expired' => 'text-danger',
		'danger'  => 'text-danger',
		'warning' => 'text-warning',
		'safe'    => 'text-success',
	),

);
