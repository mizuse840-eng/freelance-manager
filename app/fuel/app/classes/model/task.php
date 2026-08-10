<?php

class Model_Task
{
	/**
	 * 指定案件のタスク一覧を取得（期限の昇順）
	 * ステータス名も含めて取得する
	 * projects・clientsとJOINし、user_idで絞り込むことで他ユーザーのタスクへのアクセスを防ぐ
	 */
	public static function find_by_project($project_id, $user_id)
	{
		return \DB::select(
				'tasks.id',
				'tasks.project_id',
				'tasks.task_status_id',
				'tasks.name',
				'tasks.due_date',
				'tasks.memo',
				'tasks.created_at',
				'tasks.updated_at',
				array('task_statuses.name', 'status_name')
			)
			->from('tasks')
			->join('task_statuses')->on('tasks.task_status_id', '=', 'task_statuses.id')
			->join('projects')->on('tasks.project_id', '=', 'projects.id')
			->join('clients')->on('projects.client_id', '=', 'clients.id')
			->where('tasks.project_id', $project_id)
			->where('clients.user_id', $user_id)
			->order_by('tasks.due_date', 'asc')
			->execute()
			->as_array();
	}

	/**
	 * ID・user_idの両方で絞り込んで1件取得
	 * ステータス名も含めて取得する
	 * projects・clientsとJOINし、user_idで絞り込むことで他ユーザーのタスクへのアクセスを防ぐ
	 */
	public static function find_by_id($id, $user_id)
	{
		return \DB::select(
				'tasks.id',
				'tasks.project_id',
				'tasks.task_status_id',
				'tasks.name',
				'tasks.due_date',
				'tasks.memo',
				'tasks.created_at',
				'tasks.updated_at',
				array('task_statuses.name', 'status_name')
			)
			->from('tasks')
			->join('task_statuses')->on('tasks.task_status_id', '=', 'task_statuses.id')
			->join('projects')->on('tasks.project_id', '=', 'projects.id')
			->join('clients')->on('projects.client_id', '=', 'clients.id')
			->where('tasks.id', $id)
			->where('clients.user_id', $user_id)
			->execute()
			->current();
	}

	/**
	 * 新規登録。created_at/updated_atは自動でセットする
	 *
	 * @return int 挿入されたID
	 */
	public static function create($data)
	{
		$now = date('Y-m-d H:i:s');

		$insert_data = array(
			'project_id'     => $data['project_id'],
			'task_status_id' => $data['task_status_id'],
			'name'           => $data['name'],
			'due_date'       => $data['due_date'],
			'memo'           => $data['memo'] !== '' ? $data['memo'] : null,
			'created_at'     => $now,
			'updated_at'     => $now,
		);

		// executeは array(挿入されたID, 挿入件数) を返す
		list($id) = \DB::insert('tasks')
			->set($insert_data)
			->execute();

		return $id;
	}

	/**
	 * 更新。updated_atは自動でセットする
	 * project_idは更新対象に含めない（案件を移す機能は作らないため）
	 * tasksテーブルにはuser_idが無くJOINしたUPDATEもできないため、
	 * 先にfind_by_idでuser_idによる所有確認を行ってから更新する
	 */
	public static function update($id, $user_id, $data)
	{
		if ( ! static::find_by_id($id, $user_id))
		{
			return false;
		}

		$update_data = array(
			'task_status_id' => $data['task_status_id'],
			'name'           => $data['name'],
			'due_date'       => $data['due_date'],
			'memo'           => $data['memo'] !== '' ? $data['memo'] : null,
			'updated_at'     => date('Y-m-d H:i:s'),
		);

		return \DB::update('tasks')
			->set($update_data)
			->where('id', $id)
			->execute();
	}

	/**
	 * 削除。先にfind_by_idでuser_idによる所有確認を行ってから削除する
	 */
	public static function delete($id, $user_id)
	{
		if ( ! static::find_by_id($id, $user_id))
		{
			return false;
		}

		return \DB::delete('tasks')
			->where('id', $id)
			->execute();
	}

	/**
	 * タスクステータス一覧を取得（sort_orderの昇順）
	 * フォームのプルダウン用
	 */
	public static function find_all_statuses()
	{
		return \DB::select('id', 'name', 'sort_order')
			->from('task_statuses')
			->order_by('sort_order', 'asc')
			->execute()
			->as_array();
	}
}
