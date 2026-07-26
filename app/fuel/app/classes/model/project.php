<?php

class Model_Project
{
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
		$now = date('Y-m-d H:i:s');

		$insert_data = array(
			'client_id'         => $data['client_id'],
			'project_status_id' => $data['project_status_id'],
			'name'              => $data['name'],
			'due_date'          => $data['due_date'],
			'created_at'        => $now,
			'updated_at'        => $now,
		);

		list($id, $affected_rows) = \DB::insert('projects')
			->set($insert_data)
			->execute();

		return $id;
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

		$update_data = array(
			
			'project_status_id' => $data['project_status_id'],
			'name'              => $data['name'],
			'due_date'          => $data['due_date'],
			'updated_at'        => date('Y-m-d H:i:s'),
		);

		return \DB::update('projects')
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

		return \DB::delete('projects')
			->where('id', $id)
			->execute();
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
