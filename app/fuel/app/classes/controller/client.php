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
	 * クライアント登録フォームの表示
	 */
	public function get_create()
	{
		$this->render_create();
	}

	/**
	 * クライアント登録
	 */
	public function post_create()
	{
		$name = trim(\Input::post('name', ''));

		// CSRFトークンの検証
		if ( ! \Security::check_token())
		{
			return $this->render_create('セッションの有効期限が切れました。お手数ですが、もう一度お試しください。', $name);
		}

		// バリデーション
		if ($name === '')
		{
			return $this->render_create('クライアント名を入力してください。', $name);
		}

		if (mb_strlen($name) > 100)
		{
			return $this->render_create('クライアント名は100文字以内で入力してください。', $name);
		}

		\Model_Client::create(array(
			'user_id' => $this->login_user['id'],
			'name'    => $name,
		));

		\Response::redirect('clients');
	}

	/**
	 * クライアント削除の確認画面
	 */
	public function get_delete($id = null)
	{
		$client = $this->find_client_or_404($id);

		$this->render_delete($client, $this->count_projects($client));
	}

	/**
	 * クライアント削除
	 */
	public function post_delete($id = null)
	{
		$client        = $this->find_client_or_404($id);
		$project_count = $this->count_projects($client);

		if ( ! \Security::check_token())
		{
			return $this->render_delete($client, $project_count, 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。');
		}

		if ($project_count > 0)
		{
			return $this->render_delete($client, $project_count, '案件が登録されているため削除できません。先に案件をすべて削除してください。');
		}

		\Model_Client::delete($client['id'], $this->login_user['id']);

		\Response::redirect('clients');
	}

	/**
	 * クライアント名の更新（非同期用API）
	 */
	public function post_api_update()
	{
		// JSONで返すのでテンプレートは使わない
		$this->auto_render = false;

		if ( ! \Security::check_token())
		{
			return $this->json_response(array('success' => false, 'message' => 'トークンが不正です。'), 400);
		}

		$id   = \Input::post('id');
		$name = trim(\Input::post('name', ''));

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

		$client = \Model_Client::find_by_id($id, $this->login_user['id']);

		if (empty($client))
		{
			return $this->json_response(array('success' => false, 'message' => '対象のクライアントが見つかりません。'), 404);
		}

		\Model_Client::update($id, $this->login_user['id'], array('name' => $name));

		return $this->json_response(array(
			'success' => true,
			'client'  => array('id' => (int) $id, 'name' => $name),
		));
	}

	/**
	 * URLで指定されたクライアントを取得する
	 * find_by_idはuser_idでも絞り込むため、他人のクライアントIDを指定した場合も404になる
	 */
	private function find_client_or_404($id)
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

		return $client;
	}

	/**
	 * 指定クライアントに紐づく案件の件数を取得する
	 */
	private function count_projects($client)
	{
		return \Model_Client::count_projects($client['id'], $this->login_user['id']);
	}

	/**
	 * クライアント登録画面を表示する
	 */
	private function render_create($error = null, $name = '')
	{
		$this->template->title   = 'クライアント登録';
		$this->template->content = \View::forge('client/create', array(
			'error' => $error,
			'name'  => $name,
		), false);
	}

	/**
	 * クライアント削除の確認画面を表示する
	 */
	private function render_delete($client, $project_count, $error = null)
	{
		$this->template->title   = 'クライアント削除';
		$this->template->content = \View::forge('client/delete', array(
			'client'        => $client,
			'error'         => $error,
			'project_count' => $project_count,
		));
	}
}
