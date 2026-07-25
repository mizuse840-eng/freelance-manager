<?php

class Controller_Client extends Controller_Base
{
	/**
	 * クライアント一覧
	 */
	public function action_index()
	{
		$clients = \Model_Client::find_all($this->login_user['id']);

		$this->template->title   = 'クライアント一覧';
		$this->template->content = \View::forge('client/index', array(
			'clients' => $clients,
		));
	}

	/**
	 * クライアント登録
	 */
	public function action_create()
	{
		$error = null;
		$name  = '';

		if (\Input::method() === 'POST')
		{
			// CSRFトークンの検証
			if ( ! \Security::check_token())
			{
				
				throw new \HttpNotFoundException();
			}

			$name = trim(\Input::post('name', ''));

			// バリデーション
			if ($name === '')
			{
				$error = 'クライアント名を入力してください。';
			}
			elseif (mb_strlen($name) > 100)
			{
				$error = 'クライアント名は100文字以内で入力してください。';
			}
			else
			{
				\Model_Client::create(array(
					'user_id' => $this->login_user['id'],
					'name'    => $name,
				));

				\Response::redirect('clients');
			}
		}

		$this->template->title   = 'クライアント登録';
		$this->template->content = \View::forge('client/create', array(
			'error' => $error,
			'name'  => $name,
		));
	}
	/**
	 * クライアント編集
	 */
	public function action_edit($id = null)
	{
		if (empty($id))
		{
			throw new \HttpNotFoundException();
		}

		$client = \Model_Client::find_by_id($id, $this->login_user['id']);

		if (empty($client))
		{
			throw new \HttpNotFoundException();
		}

		$error = null;
		$name  = $client['name'];

		if (\Input::method() === 'POST')
		{
			if ( ! \Security::check_token())
			{
				throw new \HttpNotFoundException();
			}

			$name = trim(\Input::post('name', ''));

			if ($name === '')
			{
				$error = 'クライアント名を入力してください。';
			}
			elseif (mb_strlen($name) > 100)
			{
				$error = 'クライアント名は100文字以内で入力してください。';
			}
			else
			{
				\Model_Client::update($id, $this->login_user['id'], array(
					'name' => $name,
				));

				\Response::redirect('clients');
			}
		}

		$this->template->title   = 'クライアント編集';
		$this->template->content = \View::forge('client/edit', array(
			'error'  => $error,
			'name'   => $name,
			'client' => $client,
		));
	}

	/**
	 * クライアント削除
	 */
	public function action_delete($id = null)
	{
		if (empty($id))
		{
			throw new \HttpNotFoundException();
		}

		$client = \Model_Client::find_by_id($id, $this->login_user['id']);

		if (empty($client))
		{
			throw new \HttpNotFoundException();
		}

		if (\Input::method() === 'POST')
		{
			if ( ! \Security::check_token())
			{
				throw new \HttpNotFoundException();
			}

			\Model_Client::delete($id, $this->login_user['id']);

			\Response::redirect('clients');
		}

		$this->template->title   = 'クライアント削除';
		$this->template->content = \View::forge('client/delete', array(
			'client' => $client,
		));
	}
}