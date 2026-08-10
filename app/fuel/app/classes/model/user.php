<?php

class Model_User
{
	/**
	 * IDで1件取得（ログイン中のユーザー情報の取得用）
	 * パスワードは使わないため取得しない
	 */
	public static function find_by_id($id)
	{
		return \DB::select('id', 'name', 'email')
			->from('users')
			->where('id', $id)
			->execute()
			->current();
	}

	/**
	 * メールアドレスで1件取得（ログイン認証用）
	 * password_verifyで照合するため password も取得する
	 */
	public static function find_by_email($email)
	{
		return \DB::select('id', 'name', 'email', 'password')
			->from('users')
			->where('email', $email)
			->execute()
			->current();
	}
}
