<?php

class Model_Client extends Model_Base
{
	protected static $table = 'clients';

	/**
	 * 指定ユーザーのクライアント一覧を取得（作成日の降順）
	 */
	public static function find_all($user_id)
	{
		return \DB::select()
			->from('clients')
			->where('user_id', $user_id)
			->order_by('created_at', 'desc')
			->execute()
			->as_array();
	}

	/**
	 * ID・user_idの両方で絞り込んで1件取得
	 * 他ユーザーのデータにアクセスできないようuser_idも条件に含める
	 */
	public static function find_by_id($id, $user_id)
	{
		return \DB::select('id', 'user_id', 'name', 'created_at', 'updated_at')
			->from('clients')
			->where('id', $id)
			->where('user_id', $user_id)
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
			'user_id' => $data['user_id'],
			'name'    => $data['name'],
		));
	}

	/**
	 * 更新。updated_atは自動でセットする
	 * clientsはuser_idを直接持つため、WHERE句に足して1クエリで所有者を絞り込む
	 */
	public static function update($id, $user_id, $data)
	{
		return static::update_by_id(
			$id,
			array('name' => $data['name']),
			array('user_id' => $user_id)
		);
	}

	/**
	 * 削除。updateと同じくuser_idも条件に含める
	 */
	public static function delete($id, $user_id)
	{
		return static::delete_by_id($id, array('user_id' => $user_id));
	}

	/**
	 * 指定クライアントに紐づく案件の件数を取得
	 * user_idも条件に含め、他ユーザーのクライアントの件数が漏れないようにする
	 */
	public static function count_projects($client_id, $user_id)
	{
		return \DB::select(array(\DB::expr('COUNT(*)'), 'cnt'))
			->from('projects')
			->join('clients')->on('projects.client_id', '=', 'clients.id')
			->where('projects.client_id', $client_id)
			->where('clients.user_id', $user_id)
			->execute()
			->get('cnt');
	}
}
