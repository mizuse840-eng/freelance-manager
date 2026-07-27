<?php

class Controller_Project extends Controller_Base
{
	/**
	 * 案件一覧
	 */
	public function action_index($client_id = null)
	{
		if (empty($client_id))
		{
			throw new \HttpNotFoundException();
		}

		// クライアントの所有確認（他人のクライアントIDを弾く）
		$client = \Model_Client::find_by_id($client_id, $this->login_user['id']);

		if (empty($client))
		{
			throw new \HttpNotFoundException();
		}

		$projects = \Model_Project::find_by_client($client_id, $this->login_user['id']);

		$this->template->title   = $client['name'].'の案件一覧';
		$this->template->content = \View::forge('project/index', array(
			'client'   => $client,
			'projects' => $projects,
		), false);
	}
    /**
	 * 案件登録
	 */
	public function action_create($client_id = null)
	{
		if (empty($client_id))
		{
			throw new \HttpNotFoundException();
		}

		$client = \Model_Client::find_by_id($client_id, $this->login_user['id']);

		if (empty($client))
		{
			throw new \HttpNotFoundException();
		}

		$statuses = \Model_Project::find_all_statuses();

		$error  = null;
		$name   = '';
		$due    = '';
		$status = '';

		if (\Input::method() === 'POST')
		{
			$name   = trim(\Input::post('name', ''));
			$due    = trim(\Input::post('due_date', ''));
			$status = \Input::post('project_status_id', '');

			if ( ! \Security::check_token())
			{
				$error = 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。';
			}
			elseif ($name === '')
			{
				$error = '案件名を入力してください。';
			}
			elseif (mb_strlen($name) > 150)
			{
				$error = '案件名は150文字以内で入力してください。';
			}
			elseif ($due === '')
			{
				$error = '期限を入力してください。';
			}
			elseif ( ! preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $due))
			{
				$error = '期限の形式が正しくありません。';
			}
			elseif ( ! static::valid_status($status, $statuses))
			{
				$error = 'ステータスを選択してください。';
			}
			else
			{
				\Model_Project::create(array(
					'client_id'         => $client_id,
					'project_status_id' => $status,
					'name'              => $name,
					'due_date'          => $due,
				));

				\Response::redirect('clients/'.$client_id.'/projects');
			}
		}

		$this->template->title   = '案件登録';
		$this->template->content = \View::forge('project/create', array(
			'client'   => $client,
			'statuses' => $statuses,
			'error'    => $error,
			'name'     => $name,
			'due_date' => $due,
			'status_id' => $status,
		), false);
	}

	/**
	 * 送信されたステータスIDが実在するものか検証する
	 */
	private static function valid_status($status_id, $statuses)
	{
		foreach ($statuses as $status)
		{
			if ((string) $status['id'] === (string) $status_id)
			{
				return true;
			}
		}

		return false;
	}
    /**
	 * 案件編集
	 */
	public function action_edit($id = null)
	{
		if (empty($id))
		{
			throw new \HttpNotFoundException();
		}

		$project = \Model_Project::find_by_id($id, $this->login_user['id']);

		if (empty($project))
		{
			throw new \HttpNotFoundException();
		}

		$statuses = \Model_Project::find_all_statuses();

		$error  = null;
		$name   = $project['name'];
		$due    = $project['due_date'];
		$status = $project['project_status_id'];

		if (\Input::method() === 'POST')
		{
			$name   = trim(\Input::post('name', ''));
			$due    = trim(\Input::post('due_date', ''));
			$status = \Input::post('project_status_id', '');

			if ( ! \Security::check_token())
			{
				$error = 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。';
			}
			elseif ($name === '')
			{
				$error = '案件名を入力してください。';
			}
			elseif (mb_strlen($name) > 150)
			{
				$error = '案件名は150文字以内で入力してください。';
			}
			elseif ($due === '')
			{
				$error = '期限を入力してください。';
			}
			elseif ( ! preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $due))
			{
				$error = '期限の形式が正しくありません。';
			}
			elseif ( ! static::valid_status($status, $statuses))
			{
				$error = 'ステータスを選択してください。';
			}
			else
			{
				\Model_Project::update($id, $this->login_user['id'], array(
					'project_status_id' => $status,
					'name'              => $name,
					'due_date'          => $due,
				));

				\Response::redirect('clients/'.$project['client_id'].'/projects');
			}
		}

		$this->template->title   = '案件編集';
		$this->template->content = \View::forge('project/edit', array(
			'project'   => $project,
			'statuses'  => $statuses,
			'error'     => $error,
			'name'      => $name,
			'due_date'  => $due,
			'status_id' => $status,
		), false);
	}

	/**
	 * 案件削除
	 */
	public function action_delete($id = null)
	{
		if (empty($id))
		{
			throw new \HttpNotFoundException();
		}

		$project = \Model_Project::find_by_id($id, $this->login_user['id']);

		if (empty($project))
		{
			throw new \HttpNotFoundException();
		}

		$error = null;

		if (\Input::method() === 'POST')
		{
			if ( ! \Security::check_token())
			{
				$error = 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。';
			}
			else
			{
				\Model_Project::delete($id, $this->login_user['id']);

				\Response::redirect('clients/'.$project['client_id'].'/projects');
			}
		}

		$this->template->title   = '案件削除';
		$this->template->content = \View::forge('project/delete', array(
			'project' => $project,
			'error'   => $error,
		), false);
	}
}