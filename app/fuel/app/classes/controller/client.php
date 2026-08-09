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
		// clients は json_encode() で <script> 内のJSに埋め込むため、
		// HTMLエスケープ用のoutput_filter（Security::htmlentities）は適用しない。
		// 代わりにjson_encode側でJSコンテキスト向けのエスケープを行う。
		$this->template->content = \View::forge('client/index', array(
			'clients' => $clients,
		), false);
	}

	/**
	 * クライアント登録
	 */
	public function action_create()
	{
		$error = null;
		$name  = '';
		$url   = '';

		if (\Input::method() === 'POST')
		{
			$name = trim(\Input::post('name', ''));
			$url  = trim(\Input::post('url', ''));

			// CSRFトークンの検証
			if ( ! \Security::check_token())
			{
				$error = 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。';
			}
			// バリデーション
			elseif ($name === '')
			{
				$error = 'クライアント名を入力してください。';
			}
			elseif (mb_strlen($name) > 100)
			{
				$error = 'クライアント名は100文字以内で入力してください。';
			}
			// URLは任意項目。入力された場合のみ形式をチェックする
			elseif ($url !== '' && ! filter_var($url, FILTER_VALIDATE_URL))
			{
				$error = 'URLの形式が正しくありません。';
			}
			else
			{
				\Model_Client::create(array(
					'user_id' => $this->login_user['id'],
					'name'    => $name,
					'url'     => $url !== '' ? $url : null,
				));

				\Response::redirect('clients');
			}
		}

		$this->template->title   = 'クライアント登録';
		$this->template->content = \View::forge('client/create', array(
			'error' => $error,
			'name'  => $name,
			'url'   => $url,
		), false);
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
			$name = trim(\Input::post('name', ''));

			if ( ! \Security::check_token())
			{
				$error = 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。';
			}
			elseif ($name === '')
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

		$error = null;
		$project_count = \Model_Client::count_projects($id, $this->login_user['id']);

		if (\Input::method() === 'POST')
		{
			if ( ! \Security::check_token())
			{
				$error = 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。';
			}
			elseif ($project_count > 0)
			{
				$error = '案件が登録されているため削除できません。先に案件をすべて削除してください。';
			}
			else
			{
				\Model_Client::delete($id, $this->login_user['id']);

				\Response::redirect('clients');
			}
		}

		$this->template->title   = 'クライアント削除';
		$this->template->content = \View::forge('client/delete', array(
			'client'        => $client,
			'error'         => $error,
			'project_count' => $project_count,
		));
	}
	/**
	 * クライアント名の更新（非同期用API）
	 */
	public function action_api_update()
	{
		// JSONで返すのでテンプレートは使わない
		$this->auto_render = false;

		if (\Input::method() !== 'POST')
		{
			return $this->json_response(array('success' => false, 'message' => '不正なリクエストです。'), 400);
		}

		if ( ! \Security::check_token())
		{
			return $this->json_response(array('success' => false, 'message' => 'トークンが不正です。'), 400);
		}

		$id   = \Input::post('id');
		$name = trim(\Input::post('name', ''));
		$url  = trim(\Input::post('url', ''));

		if (empty($id))
		{
			return $this->json_response(array('success' => false, 'message' => 'IDが指定されていません。'), 400);
		}

		if ($name === '')
		{
			return $this->json_response(array('success' => false, 'message' => 'クライアント名を入力してください。'), 400);
		}

		if (mb_strlen($name) > 100)
		{
			return $this->json_response(array('success' => false, 'message' => 'クライアント名は100文字以内で入力してください。'), 400);
		}

		// URLは任意項目。入力された場合のみ形式をチェックする
		if ($url !== '' && ! filter_var($url, FILTER_VALIDATE_URL))
		{
			return $this->json_response(array('success' => false, 'message' => 'URLの形式が正しくありません。'), 400);
		}

		$client = \Model_Client::find_by_id($id, $this->login_user['id']);

		if (empty($client))
		{
			return $this->json_response(array('success' => false, 'message' => '対象のクライアントが見つかりません。'), 404);
		}

		$url = $url !== '' ? $url : null;

		\Model_Client::update($id, $this->login_user['id'], array(
			'name' => $name,
			'url'  => $url,
		));

		return $this->json_response(array(
			'success' => true,
			'client'  => array('id' => (int) $id, 'name' => $name, 'url' => $url),
		));
	}

	/**
	 * JSONレスポンスを返す
	 */
	private function json_response($data, $status = 200)
	{
		return new \Response(json_encode($data), $status, array(
			'Content-Type' => 'application/json; charset=utf-8',
		));
	}
}