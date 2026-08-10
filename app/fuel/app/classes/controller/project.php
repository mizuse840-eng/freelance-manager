<?php

class Controller_Project extends Controller_Base
{
	/**
	 * 案件一覧
	 */
	public function action_index($client_id = null)
	{
		$client = $this->find_client_or_404($client_id);

		$projects = \Model_Project::find_by_client($client['id'], $this->login_user['id']);

		$this->template->title   = $client['name'].'の案件一覧';
		// projects / statuses は json_encode() で <script> 内のJSに埋め込むため、
		// HTMLエスケープ用のoutput_filter（Security::htmlentities）は適用しない。
		// 代わりにjson_encode側でJSコンテキスト向けのエスケープを行う。
		$this->template->content = \View::forge('project/index', array(
			'client'   => $client,
			'projects' => static::format_for_list($projects),
			'statuses' => static::statuses(),
		), false);
	}

	/**
	 * 一覧画面のJSに渡す形にデータを整形する
	 * 残り日数の色分けはUI設計時のルールのまま維持する
	 */
	private static function format_for_list($projects)
	{
		$formatted = array();

		foreach ($projects as $project)
		{
			$deadline = \Deadline::calculate($project['due_date']);

			$formatted[] = array(
				'id'                => (int) $project['id'],
				'name'              => $project['name'],
				'url'               => $project['url'],
				'due_date'          => $project['due_date'],
				'diff_class'        => $deadline['class'],
				'diff_label'        => $deadline['label'],
				'project_status_id' => (int) $project['project_status_id'],
				'status_name'       => $project['status_name'],
			);
		}

		return $formatted;
	}

	/**
	 * 案件登録フォームの表示
	 */
	public function get_create($client_id = null)
	{
		$client = $this->find_client_or_404($client_id);

		$this->render_create($client, array(
			'name'              => '',
			'url'               => '',
			'due_date'          => '',
			'project_status_id' => '',
		));
	}

	/**
	 * 案件登録
	 */
	public function post_create($client_id = null)
	{
		$client = $this->find_client_or_404($client_id);
		$input  = static::post_input();

		if ( ! \Security::check_token())
		{
			return $this->render_create($client, $input, 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。');
		}

		// 登録時は過去日を弾く（編集時は登録済みデータの修正を妨げないため許容する）
		$error = static::validate($input, true);

		if ($error !== null)
		{
			return $this->render_create($client, $input, $error);
		}

		\Model_Project::create(array(
			'client_id'         => $client['id'],
			'project_status_id' => $input['project_status_id'],
			'name'              => $input['name'],
			'url'               => $input['url'] !== '' ? $input['url'] : null,
			'due_date'          => $input['due_date'],
		));

		\Response::redirect('clients/'.$client['id'].'/projects');
	}

	/**
	 * 案件編集フォームの表示
	 */
	public function get_edit($id = null)
	{
		$project = $this->find_project_or_404($id);

		$this->render_edit($project, array(
			'name'              => $project['name'],
			'url'               => $project['url'] !== null ? $project['url'] : '',
			'due_date'          => $project['due_date'],
			'project_status_id' => $project['project_status_id'],
		));
	}

	/**
	 * 案件編集
	 */
	public function post_edit($id = null)
	{
		$project = $this->find_project_or_404($id);
		$input   = static::post_input();

		if ( ! \Security::check_token())
		{
			return $this->render_edit($project, $input, 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。');
		}

		$error = static::validate($input, false);

		if ($error !== null)
		{
			return $this->render_edit($project, $input, $error);
		}

		\Model_Project::update($project['id'], $this->login_user['id'], array(
			'project_status_id' => $input['project_status_id'],
			'name'              => $input['name'],
			'url'               => $input['url'] !== '' ? $input['url'] : null,
			'due_date'          => $input['due_date'],
		));

		\Response::redirect('clients/'.$project['client_id'].'/projects');
	}

	/**
	 * 案件削除の確認画面
	 */
	public function get_delete($id = null)
	{
		$project = $this->find_project_or_404($id);

		$this->render_delete($project, $this->count_tasks($project));
	}

	/**
	 * 案件削除
	 */
	public function post_delete($id = null)
	{
		$project    = $this->find_project_or_404($id);
		$task_count = $this->count_tasks($project);

		if ( ! \Security::check_token())
		{
			return $this->render_delete($project, $task_count, 'セッションの有効期限が切れました。お手数ですが、もう一度お試しください。');
		}

		if ($task_count > 0)
		{
			return $this->render_delete($project, $task_count, 'タスクが登録されているため削除できません。先にタスクをすべて削除してください。');
		}

		\Model_Project::delete($project['id'], $this->login_user['id']);

		\Response::redirect('clients/'.$project['client_id'].'/projects');
	}

	/**
	 * 案件ステータスの更新（非同期用API）
	 */
	public function post_api_status()
	{
		// JSONで返すのでテンプレートは使わない
		$this->auto_render = false;

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

		$statuses = static::statuses();

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

	/**
	 * POSTされた入力値をまとめて取得する
	 */
	private static function post_input()
	{
		return array(
			'name'              => trim(\Input::post('name', '')),
			'url'               => trim(\Input::post('url', '')),
			'due_date'          => trim(\Input::post('due_date', '')),
			'project_status_id' => \Input::post('project_status_id', ''),
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
			return '案件名を入力してください。';
		}

		if (mb_strlen($input['name']) > 150)
		{
			return '案件名は150文字以内で入力してください。';
		}

		// URLは任意項目。入力された場合のみ形式をチェックする
		if ($input['url'] !== '' && ! filter_var($input['url'], FILTER_VALIDATE_URL))
		{
			return 'URLの形式が正しくありません。';
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

		if ( ! static::valid_status($input['project_status_id'], static::statuses()))
		{
			return 'ステータスを選択してください。';
		}

		return null;
	}

	/**
	 * 案件ステータス一覧を取得する
	 * 1リクエスト内で検証・画面表示から何度も参照するため、取得結果を使い回す
	 */
	private static function statuses()
	{
		static $statuses = null;

		if ($statuses === null)
		{
			$statuses = \Model_Project::find_all_statuses();
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
	 * URLで指定されたクライアントを取得する
	 * find_by_idはuser_idでも絞り込むため、他人のクライアントIDを指定した場合も404になる
	 */
	private function find_client_or_404($client_id)
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

		return $client;
	}

	/**
	 * URLで指定された案件を取得する
	 * find_by_idはclientsとJOINしてuser_idで絞り込むため、他人の案件IDを指定した場合も404になる
	 */
	private function find_project_or_404($id)
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

		return $project;
	}

	/**
	 * 指定案件に紐づくタスクの件数を取得する
	 */
	private function count_tasks($project)
	{
		return \Model_Project::count_tasks($project['id'], $this->login_user['id']);
	}

	/**
	 * 案件登録画面を表示する
	 */
	private function render_create($client, $input, $error = null)
	{
		$this->template->title   = '案件登録';
		$this->template->content = \View::forge('project/create', array(
			'client'    => $client,
			'statuses'  => static::statuses(),
			'error'     => $error,
			'name'      => $input['name'],
			'url'       => $input['url'],
			'due_date'  => $input['due_date'],
			'status_id' => $input['project_status_id'],
		), false);
	}

	/**
	 * 案件編集画面を表示する
	 */
	private function render_edit($project, $input, $error = null)
	{
		$this->template->title   = '案件編集';
		$this->template->content = \View::forge('project/edit', array(
			'project'   => $project,
			'statuses'  => static::statuses(),
			'error'     => $error,
			'name'      => $input['name'],
			'url'       => $input['url'],
			'due_date'  => $input['due_date'],
			'status_id' => $input['project_status_id'],
		), false);
	}

	/**
	 * 案件削除の確認画面を表示する
	 */
	private function render_delete($project, $task_count, $error = null)
	{
		$this->template->title   = '案件削除';
		$this->template->content = \View::forge('project/delete', array(
			'project'    => $project,
			'error'      => $error,
			'task_count' => $task_count,
		), false);
	}
}
