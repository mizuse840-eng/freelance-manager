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
		$statuses = \Model_Project::find_all_statuses();

		$this->template->title   = $client['name'].'の案件一覧';
		$this->template->content = \View::forge('project/index', array(
			'client'   => $client,
			'projects' => $projects,
			'statuses' => $statuses,
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
		$url    = '';
		$due    = '';
		$status = '';

		if (\Input::method() === 'POST')
		{
			$name   = trim(\Input::post('name', ''));
			$url    = trim(\Input::post('url', ''));
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
			// URLは任意項目。入力された場合のみ形式をチェックする
			elseif ($url !== '' && ! filter_var($url, FILTER_VALIDATE_URL))
			{
				$error = 'URLの形式が正しくありません。';
			}
			elseif ($due === '')
			{
				$error = '期限を入力してください。';
			}
			elseif ( ! preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $due))
			{
				$error = '期限の形式が正しくありません。';
			}
			elseif ($due < date('Y-m-d'))
			{
				$error = '期限には本日以降の日付を指定してください。';
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
					'url'               => $url !== '' ? $url : null,
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
			'url'      => $url,
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
		$url    = $project['url'] !== null ? $project['url'] : '';
		$due    = $project['due_date'];
		$status = $project['project_status_id'];

		if (\Input::method() === 'POST')
		{
			$name   = trim(\Input::post('name', ''));
			$url    = trim(\Input::post('url', ''));
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
			// URLは任意項目。入力された場合のみ形式をチェックする
			elseif ($url !== '' && ! filter_var($url, FILTER_VALIDATE_URL))
			{
				$error = 'URLの形式が正しくありません。';
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
					'url'               => $url !== '' ? $url : null,
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
			'url'       => $url,
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
		$task_count = \Model_Project::count_tasks($id, $this->login_user['id']);

		if (\Input::method() === 'POST')
		{
			if ( ! \Security::check_token())
			{
				$error = 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。';
			}
			elseif ($task_count > 0)
			{
				$error = 'タスクが登録されているため削除できません。先にタスクをすべて削除してください。';
			}
			else
			{
				\Model_Project::delete($id, $this->login_user['id']);

				\Response::redirect('clients/'.$project['client_id'].'/projects');
			}
		}

		$this->template->title   = '案件削除';
		$this->template->content = \View::forge('project/delete', array(
			'project'    => $project,
			'error'      => $error,
			'task_count' => $task_count,
		), false);
	}

	/**
	 * 案件ステータスの更新（非同期用API）
	 */
	public function action_api_status()
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

		$id                = \Input::post('id');
		$project_status_id = \Input::post('project_status_id');

		if (empty($id))
		{
			return $this->json_response(array('success' => false, 'message' => 'IDが指定されていません。'), 400);
		}

		$project = \Model_Project::find_by_id($id, $this->login_user['id']);

		if (empty($project))
		{
			return $this->json_response(array('success' => false, 'message' => '対象の案件が見つかりません。'), 404);
		}

		$statuses = \Model_Project::find_all_statuses();

		if ( ! static::valid_status($project_status_id, $statuses))
		{
			return $this->json_response(array('success' => false, 'message' => 'ステータスを選択してください。'), 400);
		}

		// name / url / due_date は既存の値をそのまま渡す（空で上書きされるのを防ぐ）
		\Model_Project::update($id, $this->login_user['id'], array(
			'project_status_id' => $project_status_id,
			'name'              => $project['name'],
			'url'               => $project['url'],
			'due_date'          => $project['due_date'],
		));

		$status_name = '';

		foreach ($statuses as $status)
		{
			if ((string) $status['id'] === (string) $project_status_id)
			{
				$status_name = $status['name'];
				break;
			}
		}

		return $this->json_response(array(
			'success' => true,
			'project' => array(
				'id'                => (int) $id,
				'project_status_id' => (int) $project_status_id,
				'status_name'       => $status_name,
			),
		));
	}
}
