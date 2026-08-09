<?php

class Controller_Base extends Controller_Template
{
	use Trait_Headers;

	/**
	 * ログイン中のユーザー情報
	 */
	protected $login_user = null;

	/**
	 * 各アクションの実行前に必ず呼ばれる
	 * 未ログインの場合はログイン画面へリダイレクトする
	 */
	public function before()
	{
		parent::before();

		// セキュリティヘッダを設定
		$this->set_security_headers();

		$user_id = \Session::get('user_id');

		if (empty($user_id))
		{
			\Response::redirect('login');
		}

		$this->login_user = \DB::select('id', 'name', 'email')
			->from('users')
			->where('id', $user_id)
			->execute()
			->current();

		if (empty($this->login_user))
		{
			\Session::destroy();
			\Response::redirect('login');
		}
	}
}