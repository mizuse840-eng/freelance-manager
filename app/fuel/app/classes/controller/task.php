<?php

class Controller_Task extends Controller_Base
{
	/**
	 * タスク一覧
	 */
	public function action_index($project_id = null)
	{
		if (empty($project_id))
		{
			throw new \HttpNotFoundException();
		}

		// 案件の所有確認（他人の案件IDを弾く）
		$project = \Model_Project::find_by_id($project_id, $this->login_user['id']);

		if (empty($project))
		{
			throw new \HttpNotFoundException();
		}

		$tasks    = \Model_Task::find_by_project($project_id, $this->login_user['id']);
		$statuses = \Model_Task::find_all_statuses();

		$this->template->title   = $project['name'].'のタスク一覧';
		$this->template->content = \View::forge('task/index', array(
			'project'  => $project,
			'tasks'    => $tasks,
			'statuses' => $statuses,
		), false);
	}

	/**
	 * タスク登録
	 */
	public function action_create($project_id = null)
	{
		if (empty($project_id))
		{
			throw new \HttpNotFoundException();
		}

		$project = \Model_Project::find_by_id($project_id, $this->login_user['id']);

		if (empty($project))
		{
			throw new \HttpNotFoundException();
		}

		$statuses = \Model_Task::find_all_statuses();

		$error  = null;
		$name   = '';
		$due    = '';
		$status = '';
		$memo   = '';

		if (\Input::method() === 'POST')
		{
			$name   = trim(\Input::post('name', ''));
			$due    = trim(\Input::post('due_date', ''));
			$status = \Input::post('task_status_id', '');
			$memo   = trim(\Input::post('memo', ''));

			if ( ! \Security::check_token())
			{
				$error = 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。';
			}
			elseif ($name === '')
			{
				$error = 'タスク名を入力してください。';
			}
			elseif (mb_strlen($name) > 150)
			{
				$error = 'タスク名は150文字以内で入力してください。';
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
				\Model_Task::create(array(
					'project_id'     => $project_id,
					'task_status_id' => $status,
					'name'           => $name,
					'due_date'       => $due,
					'memo'           => $memo,
				));

				\Response::redirect('projects/'.$project_id.'/tasks');
			}
		}

		$this->template->title   = 'タスク登録';
		$this->template->content = \View::forge('task/create', array(
			'project'   => $project,
			'statuses'  => $statuses,
			'error'     => $error,
			'name'      => $name,
			'due_date'  => $due,
			'status_id' => $status,
			'memo'      => $memo,
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
	 * タスク編集
	 */
	public function action_edit($id = null)
	{
		if (empty($id))
		{
			throw new \HttpNotFoundException();
		}

		$task = \Model_Task::find_by_id($id, $this->login_user['id']);

		if (empty($task))
		{
			throw new \HttpNotFoundException();
		}

		$statuses = \Model_Task::find_all_statuses();

		$error  = null;
		$name   = $task['name'];
		$due    = $task['due_date'];
		$status = $task['task_status_id'];
		$memo   = $task['memo'];

		if (\Input::method() === 'POST')
		{
			$name   = trim(\Input::post('name', ''));
			$due    = trim(\Input::post('due_date', ''));
			$status = \Input::post('task_status_id', '');
			$memo   = trim(\Input::post('memo', ''));

			if ( ! \Security::check_token())
			{
				$error = 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。';
			}
			elseif ($name === '')
			{
				$error = 'タスク名を入力してください。';
			}
			elseif (mb_strlen($name) > 150)
			{
				$error = 'タスク名は150文字以内で入力してください。';
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
				\Model_Task::update($id, $this->login_user['id'], array(
					'task_status_id' => $status,
					'name'           => $name,
					'due_date'       => $due,
					'memo'           => $memo,
				));

				\Response::redirect('projects/'.$task['project_id'].'/tasks');
			}
		}

		$this->template->title   = 'タスク編集';
		$this->template->content = \View::forge('task/edit', array(
			'task'      => $task,
			'statuses'  => $statuses,
			'error'     => $error,
			'name'      => $name,
			'due_date'  => $due,
			'status_id' => $status,
			'memo'      => $memo,
		), false);
	}

	/**
	 * タスク削除
	 */
	public function action_delete($id = null)
	{
		if (empty($id))
		{
			throw new \HttpNotFoundException();
		}

		$task = \Model_Task::find_by_id($id, $this->login_user['id']);

		if (empty($task))
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
				\Model_Task::delete($id, $this->login_user['id']);

				\Response::redirect('projects/'.$task['project_id'].'/tasks');
			}
		}

		$this->template->title   = 'タスク削除';
		$this->template->content = \View::forge('task/delete', array(
			'task'  => $task,
			'error' => $error,
		), false);
	}

	/**
	 * タスクステータスの更新（非同期用API）
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

		$id             = \Input::post('id');
		$task_status_id = \Input::post('task_status_id');

		if (empty($id))
		{
			return $this->json_response(array('success' => false, 'message' => 'IDが指定されていません。'), 400);
		}

		$task = \Model_Task::find_by_id($id, $this->login_user['id']);

		if (empty($task))
		{
			return $this->json_response(array('success' => false, 'message' => '対象のタスクが見つかりません。'), 404);
		}

		$statuses = \Model_Task::find_all_statuses();

		if ( ! static::valid_status($task_status_id, $statuses))
		{
			return $this->json_response(array('success' => false, 'message' => 'ステータスを選択してください。'), 400);
		}

		// name / due_date / memo は既存の値をそのまま渡す（空で上書きされるのを防ぐ）
		\Model_Task::update($id, $this->login_user['id'], array(
			'task_status_id' => $task_status_id,
			'name'           => $task['name'],
			'due_date'       => $task['due_date'],
			'memo'           => $task['memo'] !== null ? $task['memo'] : '',
		));

		$status_name = '';

		foreach ($statuses as $status)
		{
			if ((string) $status['id'] === (string) $task_status_id)
			{
				$status_name = $status['name'];
				break;
			}
		}

		return $this->json_response(array(
			'success' => true,
			'task'    => array(
				'id'             => (int) $id,
				'task_status_id' => (int) $task_status_id,
				'status_name'    => $status_name,
			),
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
