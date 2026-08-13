<?php

/**
 * Modelの共通処理
 *
 * 各Modelで同じ形になっていた「タイムスタンプを付けてINSERT / UPDATE / DELETE する」
 * 定型部分をまとめる。対象テーブルは継承先の $table で指定する。
 *
 * 検索系（find_by_id など）は共通化していない。
 * Model_Client は clients を単体で引くだけだが、Model_Project / Model_Task は
 * 所有者を判定するために2〜3テーブルをJOINしており、JOINの経路も取得カラムも
 * モデルごとに異なるため、無理にまとめると引数で分岐するだけの
 * 読みにくいメソッドになる。
 *
 * 所有確認の方法も共通化していない。clients は user_id を直接持つので
 * WHERE 句に足すだけで済むが、projects / tasks は user_id を持たないため
 * 事前に find_by_id で確認する必要がある。揃えると clients 側に
 * 不要なクエリが1回増える。
 */
abstract class Model_Base
{
	/**
	 * 対象テーブル名。継承先で必ず指定する
	 */
	protected static $table = null;

	/**
	 * created_at / updated_at を付けて1件登録する
	 *
	 * @param  array $data 登録するカラムと値
	 * @return int   挿入されたID
	 */
	protected static function insert(array $data)
	{
		$now = date('Y-m-d H:i:s');

		$data['created_at'] = $now;
		$data['updated_at'] = $now;

		// executeは array(挿入されたID, 挿入件数) を返す
		list($id) = \DB::insert(static::$table)
			->set($data)
			->execute();

		return $id;
	}

	/**
	 * updated_at を付けてID指定で更新する
	 *
	 * $conditions を渡すとWHERE句に追加する。user_idを直接持つテーブルは
	 * ここに渡すことで1クエリのまま所有者を絞り込める。
	 * 持たないテーブルは、呼び出す前に所有確認を済ませておくこと。
	 *
	 * @param  int   $id
	 * @param  array $data       更新するカラムと値
	 * @param  array $conditions 追加のWHERE条件（カラム名 => 値）
	 * @return int   更新件数
	 */
	protected static function update_by_id($id, array $data, array $conditions = array())
	{
		$data['updated_at'] = date('Y-m-d H:i:s');

		$query = \DB::update(static::$table)
			->set($data)
			->where('id', $id);

		foreach ($conditions as $column => $value)
		{
			$query->where($column, $value);
		}

		return $query->execute();
	}

	/**
	 * ID指定で削除する
	 *
	 * $conditions の扱いは update_by_id と同じ
	 *
	 * @param  int   $id
	 * @param  array $conditions 追加のWHERE条件（カラム名 => 値）
	 * @return int   削除件数
	 */
	protected static function delete_by_id($id, array $conditions = array())
	{
		$query = \DB::delete(static::$table)
			->where('id', $id);

		foreach ($conditions as $column => $value)
		{
			$query->where($column, $value);
		}

		return $query->execute();
	}
}
