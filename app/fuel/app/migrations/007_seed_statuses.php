<?php

namespace Fuel\Migrations;

class Seed_statuses
{
	public static function up()
	{
		$now = date('Y-m-d H:i:s');

		$statuses = array(
			array('name' => '未着手', 'sort_order' => 1),
			array('name' => '進行中', 'sort_order' => 2),
			array('name' => '完了',   'sort_order' => 3),
		);

		foreach (array('project_statuses', 'task_statuses') as $table)
		{
			foreach ($statuses as $status)
			{
				\DB::insert($table)->set(array(
					'name'       => $status['name'],
					'sort_order' => $status['sort_order'],
					'created_at' => $now,
					'updated_at' => $now,
				))->execute();
			}
		}
	}

	public static function down()
	{
		\DB::delete('project_statuses')->execute();
		\DB::delete('task_statuses')->execute();
	}
}