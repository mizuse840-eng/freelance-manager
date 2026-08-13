<?php

class Model_Project extends Model_Base
{
	protected static $table = 'projects';

	/**
	 * 指定クライアントの案件一覧を取得（期限の昇順）
	 * ステータス名も含めて取得する
	 * clientsとJOINし、user_idで絞り込むことで他ユーザーの案件へのアクセスを防ぐ
	 */
	public static function find_by_client($client_id, $user_id)
	{
		return \DB::select(
				'projects.id',
				'projects.client_id',
				'projects.project_status_id',
				'projects.name',
				'projects.url',
				'projects.due_date',
				'projects.created_at',
				'projects.updated_at',
				array('project_statuses.name', 'status_name')
			)
			->from('projects')
			->join('project_statuses')->on('projects.project_status_id', '=', 'project_statuses.id')
			->join('clients')->on('projects.client_id', '=', 'clients.id')
			->where('projects.client_id', $client_id)
			->where('clients.user_id', $user_id)
			->order_by('projects.due_date', 'asc')
			->execute()
			->as_array();
	}

	/**
	 * ID・user_idの両方で絞り込んで1件取得
	 * ステータス名も含めて取得する
	 * clientsとJOINし、user_idで絞り込むことで他ユーザーの案件へのアクセスを防ぐ
	 */
	public static function find_by_id($id, $user_id)
	{
		return \DB::select(
				'projects.id',
				'projects.client_id',
				'projects.project_status_id',
				'projects.name',
				'projects.url',
				'projects.due_date',
				'projects.created_at',
				'projects.updated_at',
				array('project_statuses.name', 'status_name')
			)
			->from('projects')
			->join('project_statuses')->on('projects.project_status_id', '=', 'project_statuses.id')
			->join('clients')->on('projects.client_id', '=', 'clients.id')
			->where('projects.id', $id)
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
		return static::insert(array(
			'client_id'         => $data['client_id'],
			'project_status_id' => $data['project_status_id'],
			'name'              => $data['name'],
			'url'               => $data['url'],
			'due_date'          => $data['due_date'],
		));
	}

	/**
	 * 更新。updated_atは自動でセットする
	 * projectsテーブルにはuser_idが無くclientsとJOINしたUPDATEもできないため、
	 * 先にfind_by_idでuser_idによる所有確認を行ってから更新する
	 */
	public static function update($id, $user_id, $data)
	{
		if ( ! static::find_by_id($id, $user_id))
		{
			return false;
		}

		return static::update_by_id($id, array(
			'project_status_id' => $data['project_status_id'],
			'name'              => $data['name'],
			'url'               => $data['url'],
			'due_date'          => $data['due_date'],
		));
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

		return static::delete_by_id($id);
	}

	/**
	 * 指定案件に紐づくタスクの件数を取得
	 * clientsとJOINし、user_idで絞り込むことで他ユーザーの案件の件数が漏れないようにする
	 */
	public static function count_tasks($project_id, $user_id)
	{
		return \DB::select(array(\DB::expr('COUNT(*)'), 'cnt'))
			->from('tasks')
			->join('projects')->on('tasks.project_id', '=', 'projects.id')
			->join('clients')->on('projects.client_id', '=', 'clients.id')
			->where('tasks.project_id', $project_id)
			->where('clients.user_id', $user_id)
			->execute()
			->get('cnt');
	}

	/**
	 * 案件ステータス一覧を取得（sort_orderの昇順）
	 * フォームのプルダウン用
	 */
	public static function find_all_statuses()
	{
		return \DB::select('id', 'name', 'sort_order')
			->from('project_statuses')
			->order_by('sort_order', 'asc')
			->execute()
			->as_array();
	}
}
