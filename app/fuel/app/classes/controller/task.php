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

		$tasks = \Model_Task::find_by_project($project_id, $this->login_user['id']);

		$this->template->title   = $project['name'].'のタスク一覧';
		$this->template->content = \View::forge('task/index', array(
			'project' => $project,
			'tasks'   => $tasks,
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
			if ( ! \Security::check_token())
			{
				throw new \HttpNotFoundException();
			}

			$name   = trim(\Input::post('name', ''));
			$due    = trim(\Input::post('due_date', ''));
			$status = \Input::post('task_status_id', '');
			$memo   = trim(\Input::post('memo', ''));

			if ($name === '')
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
			if ( ! \Security::check_token())
			{
				throw new \HttpNotFoundException();
			}

			$name   = trim(\Input::post('name', ''));
			$due    = trim(\Input::post('due_date', ''));
			$status = \Input::post('task_status_id', '');
			$memo   = trim(\Input::post('memo', ''));

			if ($name === '')
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

		if (\Input::method() === 'POST')
		{
			if ( ! \Security::check_token())
			{
				throw new \HttpNotFoundException();
			}

			\Model_Task::delete($id, $this->login_user['id']);

			\Response::redirect('projects/'.$task['project_id'].'/tasks');
		}

		$this->template->title   = 'タスク削除';
		$this->template->content = \View::forge('task/delete', array(
			'task' => $task,
		), false);
	}
}
