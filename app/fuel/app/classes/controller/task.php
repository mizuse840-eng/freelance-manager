<?php

class Controller_Task extends Controller_Base
{
	/**
	 * タスク一覧
	 */
	public function action_index($project_id = null)
	{
		$project = $this->find_project_or_404($project_id);

		$tasks = \Model_Task::find_by_project($project['id'], $this->login_user['id']);

		$this->template->title   = $project['name'].'のタスク一覧';
		// tasks / statuses は json_encode() で <script> 内のJSに埋め込むため、
		// HTMLエスケープ用のoutput_filter（Security::htmlentities）は適用しない。
		// 代わりにjson_encode側でJSコンテキスト向けのエスケープを行う。
		$this->template->content = \View::forge('task/index', array(
			'project'  => $project,
			'tasks'    => static::format_for_list($tasks),
			'statuses' => static::statuses(),
		), false);
	}

	/**
	 * 一覧画面のJSに渡す形にデータを整形する
	 * 残り日数の色分け・メモのHTML化はUI設計時のルールのまま維持する
	 */
	private static function format_for_list($tasks)
	{
		$formatted = array();

		foreach ($tasks as $task)
		{
			$deadline = \Deadline::calculate($task['due_date']);

			$formatted[] = array(
				'id'             => (int) $task['id'],
				'name'           => $task['name'],
				'due_date'       => $task['due_date'],
				'diff_class'     => $deadline['class'],
				'diff_label'     => $deadline['label'],
				'task_status_id' => (int) $task['task_status_id'],
				// memoはhtmlバインドで描画するため、ここでエスケープしてから改行をbrに変換する
				'memo_html'      => nl2br(e($task['memo'] !== null ? $task['memo'] : '')),
			);
		}

		return $formatted;
	}

	/**
	 * タスク登録フォームの表示
	 */
	public function get_create($project_id = null)
	{
		$project = $this->find_project_or_404($project_id);

		$this->render_create($project, array(
			'name'           => '',
			'due_date'       => '',
			'task_status_id' => '',
			'memo'           => '',
		));
	}

	/**
	 * タスク登録
	 */
	public function post_create($project_id = null)
	{
		$project = $this->find_project_or_404($project_id);
		$input   = static::post_input();

		if ( ! \Security::check_token())
		{
			return $this->render_create($project, $input, 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。');
		}

		// 登録時は過去日を弾く（編集時は登録済みデータの修正を妨げないため許容する）
		$error = static::validate($input, true);

		if ($error !== null)
		{
			return $this->render_create($project, $input, $error);
		}

		\Model_Task::create(array(
			'project_id'     => $project['id'],
			'task_status_id' => $input['task_status_id'],
			'name'           => $input['name'],
			'due_date'       => $input['due_date'],
			'memo'           => $input['memo'],
		));

		\Response::redirect('projects/'.$project['id'].'/tasks');
	}

	/**
	 * タスク編集フォームの表示
	 */
	public function get_edit($id = null)
	{
		$task = $this->find_task_or_404($id);

		$this->render_edit($task, array(
			'name'           => $task['name'],
			'due_date'       => $task['due_date'],
			'task_status_id' => $task['task_status_id'],
			'memo'           => $task['memo'],
		));
	}

	/**
	 * タスク編集
	 */
	public function post_edit($id = null)
	{
		$task  = $this->find_task_or_404($id);
		$input = static::post_input();

		if ( ! \Security::check_token())
		{
			return $this->render_edit($task, $input, 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。');
		}

		$error = static::validate($input, false);

		if ($error !== null)
		{
			return $this->render_edit($task, $input, $error);
		}

		\Model_Task::update($task['id'], $this->login_user['id'], array(
			'task_status_id' => $input['task_status_id'],
			'name'           => $input['name'],
			'due_date'       => $input['due_date'],
			'memo'           => $input['memo'],
		));

		\Response::redirect('projects/'.$task['project_id'].'/tasks');
	}

	/**
	 * タスク削除の確認画面
	 */
	public function get_delete($id = null)
	{
		$task = $this->find_task_or_404($id);

		$this->render_delete($task);
	}

	/**
	 * タスク削除
	 */
	public function post_delete($id = null)
	{
		$task = $this->find_task_or_404($id);

		if ( ! \Security::check_token())
		{
			return $this->render_delete($task, 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。');
		}

		\Model_Task::delete($task['id'], $this->login_user['id']);

		\Response::redirect('projects/'.$task['project_id'].'/tasks');
	}

	/**
	 * タスクステータスの更新（非同期用API）
	 */
	public function post_api_status()
	{
		// JSONで返すのでテンプレートは使わない
		$this->auto_render = false;

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

		$statuses = static::statuses();

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

		return $this->json_response(array(
			'success' => true,
			'task'    => array(
				'id'             => (int) $id,
				'task_status_id' => (int) $task_status_id,
			),
		));
	}

	/**
	 * POSTされた入力値をまとめて取得する
	 */
	private static function post_input()
	{
		return array(
			'name'           => trim(\Input::post('name', '')),
			'due_date'       => trim(\Input::post('due_date', '')),
			'task_status_id' => \Input::post('task_status_id', ''),
			'memo'           => trim(\Input::post('memo', '')),
		);
	}

	/**
	 * 入力値を検証し、最初に見つかったエラーメッセージを返す
	 * 問題が無ければ null を返す
	 *
	 * @param  array $input           post_input()で取得した入力値
	 * @param  bool  $reject_past_due 期限に過去日を許容しないか（登録時のみtrue）
	 * @return string|null
	 */
	private static function validate($input, $reject_past_due)
	{
		if ($input['name'] === '')
		{
			return 'タスク名を入力してください。';
		}

		if (mb_strlen($input['name']) > 150)
		{
			return 'タスク名は150文字以内で入力してください。';
		}

		if ($input['due_date'] === '')
		{
			return '期限を入力してください。';
		}

		if ( ! preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $input['due_date']))
		{
			return '期限の形式が正しくありません。';
		}

		if ($reject_past_due && $input['due_date'] < date('Y-m-d'))
		{
			return '期限には本日以降の日付を指定してください。';
		}

		if ( ! static::valid_status($input['task_status_id'], static::statuses()))
		{
			return 'ステータスを選択してください。';
		}

		return null;
	}

	/**
	 * タスクステータス一覧を取得する
	 * 1リクエスト内で検証・画面表示から何度も参照するため、取得結果を使い回す
	 */
	private static function statuses()
	{
		static $statuses = null;

		if ($statuses === null)
		{
			$statuses = \Model_Task::find_all_statuses();
		}

		return $statuses;
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
	 * URLで指定された案件を取得する
	 * find_by_idはclientsとJOINしてuser_idで絞り込むため、他人の案件IDを指定した場合も404になる
	 */
	private function find_project_or_404($project_id)
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

		return $project;
	}

	/**
	 * URLで指定されたタスクを取得する
	 * find_by_idはprojects・clientsとJOINしてuser_idで絞り込むため、
	 * 他人のタスクIDを指定した場合も404になる
	 */
	private function find_task_or_404($id)
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

		return $task;
	}

	/**
	 * タスク登録画面を表示する
	 */
	private function render_create($project, $input, $error = null)
	{
		$this->template->title   = 'タスク登録';
		$this->template->content = \View::forge('task/create', array(
			'project'   => $project,
			'statuses'  => static::statuses(),
			'error'     => $error,
			'name'      => $input['name'],
			'due_date'  => $input['due_date'],
			'status_id' => $input['task_status_id'],
			'memo'      => $input['memo'],
		), false);
	}

	/**
	 * タスク編集画面を表示する
	 */
	private function render_edit($task, $input, $error = null)
	{
		$this->template->title   = 'タスク編集';
		$this->template->content = \View::forge('task/edit', array(
			'task'      => $task,
			'statuses'  => static::statuses(),
			'error'     => $error,
			'name'      => $input['name'],
			'due_date'  => $input['due_date'],
			'status_id' => $input['task_status_id'],
			'memo'      => $input['memo'],
		), false);
	}

	/**
	 * タスク削除の確認画面を表示する
	 */
	private function render_delete($task, $error = null)
	{
		$this->template->title   = 'タスク削除';
		$this->template->content = \View::forge('task/delete', array(
			'task'  => $task,
			'error' => $error,
		), false);
	}
}
