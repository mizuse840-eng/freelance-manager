<?php

class Controller_Auth extends Controller_Template
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
	 * ログイン画面の表示
	 */
	public function get_login()
	{
		$this->redirect_if_logged_in();

		$this->render_login();
	}

	/**
	 * ログイン処理
	 */
	public function post_login()
	{
		$this->redirect_if_logged_in();

		$email    = \Input::post('email');
		$password = \Input::post('password');

		// DBから該当メールアドレスのユーザーを取得
		$user = \Model_User::find_by_email($email);

		// ユーザーが存在しないか、パスワードが一致しない
		if (empty($user) or ! password_verify($password, $user['password']))
		{
			return $this->render_login('メールアドレスまたはパスワードが正しくありません。');
		}

		// ログイン成功：セッションにユーザーIDを保存
		\Session::set('user_id', $user['id']);
		\Response::redirect('clients');
	}

	/**
	 * ログアウト処理
	 */
	public function action_logout()
	{
		\Session::destroy();
		\Response::redirect('login');
	}

	/**
	 * すでにログイン済みならクライアント一覧へ
	 */
	private function redirect_if_logged_in()
	{
		if (\Session::get('user_id'))
		{
			\Response::redirect('clients');
		}
	}

	/**
	 * ログイン画面を表示する
	 */
	private function render_login($error = null)
	{
		$this->template->title   = 'ログイン';
		$this->template->content = \View::forge('auth/login', array(
			'error' => $error,
		));
	}
}
