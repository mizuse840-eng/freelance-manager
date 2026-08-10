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

		$this->login_user = \Model_User::find_by_id($user_id);

		if (empty($this->login_user))
		{
			\Session::destroy();
			\Response::redirect('login');
		}
	}

	/**
	 * JSONレスポンスを返す（非同期API共通）
	 *
	 * CSRFトークンはリクエストのたびに再生成される（Security::fetch_token が
	 * 内部で set_token(true) を呼ぶため、csrf_rotate の設定では止められない）。
	 * 非同期更新では画面をリロードしないので、JS側が持つトークンは1回目の送信で
	 * 古くなり、2回目以降が必ず失敗してしまう。
	 * そのため毎回レスポンスに最新のトークンを含め、JS側で差し替えてもらう。
	 * 検証に失敗した場合もトークンは再生成済みなので、成功・失敗を問わず返す。
	 */
	protected function json_response($data, $status = 200)
	{
		$data['csrf_token'] = \Security::fetch_token();

		return new \Response(json_encode($data), $status, array(
			'Content-Type' => 'application/json; charset=utf-8',
		));
	}
}
