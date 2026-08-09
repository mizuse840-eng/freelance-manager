<?php

/**
 * 404などのエラーページを表示するコントローラ
 * ログイン前でも表示されるため Controller_Base ではなく Controller_Template を継承する
 */
class Controller_Welcome extends Controller_Template
{
	use Trait_Headers;

	/**
	 * 各アクションの実行前に必ず呼ばれる
	 */
	public function before()
	{
		parent::before();

		$this->set_security_headers();
	}

	/**
	 * ページが見つからない場合に表示する（routes.phpの_404_から呼ばれる）
	 */
	public function action_404()
	{
		$this->template->title   = 'ページが見つかりません';
		$this->template->content = \View::forge('welcome/404');
	}

	/**
	 * アクション実行後にレスポンスを組み立てる
	 * Responseオブジェクトはアクション時点では未生成のため、
	 * ステータスコード404の指定はここで行う
	 */
	public function after($response)
	{
		$response = parent::after($response);
		$response->set_status(404);

		return $response;
	}
}
