<?php

class Controller_Auth extends Controller_Template
{
	/**
	 * ログイン画面の表示（GET）／ログイン処理（POST）
	 */
	public function action_login()
	{
		// すでにログイン済みならクライアント一覧へ
		if (\Session::get('user_id'))
		{
			\Response::redirect('clients');
		}

		$error = null;

		// フォームが送信された場合（POST）
		if (\Input::method() === 'POST')
		{
			$email    = \Input::post('email');
			$password = \Input::post('password');

			// DBから該当メールアドレスのユーザーを取得
			$user = \DB::select('id', 'name', 'email', 'password')
				->from('users')
				->where('email', $email)
				->execute()
				->current();

			// ユーザーが存在し、かつパスワードが一致するか
			if ($user and password_verify($password, $user['password']))
			{
				// ログイン成功：セッションにユーザーIDを保存
				\Session::set('user_id', $user['id']);
				\Response::redirect('clients');
			}
			else
			{
				// ログイン失敗
				$error = 'メールアドレスまたはパスワードが正しくありません。';
			}
		}

		$this->template->title   = 'ログイン';
		$this->template->content = \View::forge('auth/login', array(
			'error' => $error,
		));
	}

	/**
	 * ログアウト処理
	 */
	public function action_logout()
	{
		\Session::destroy();
		\Response::redirect('login');
	}
}