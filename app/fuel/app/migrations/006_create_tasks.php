<?php

namespace Fuel\Migrations;

class Create_tasks
{
	public static function up()
	{
		\DBUtil::create_table('tasks', array(
			'id' => array('type' => 'int', 'unsigned' => true, 'null' => false, 'auto_increment' => true, 'constraint' => '11'),
			'project_id' => array('constraint' => '11', 'null' => false, 'type' => 'int', 'unsigned' => true),
			'task_status_id' => array('constraint' => '11', 'null' => false, 'type' => 'int', 'unsigned' => true),
			'name' => array('constraint' => 150, 'null' => false, 'type' => 'varchar'),
			'due_date' => array('null' => false, 'type' => 'date'),
			'memo' => array('null' => true, 'type' => 'text'),
			'created_at' => array('null' => false, 'type' => 'datetime'),
			'updated_at' => array('null' => false, 'type' => 'datetime'),
		), array('id'), true, false, null, array(
			array(
				'key' => 'project_id',
				'reference' => array(
					'table' => 'projects',
					'column' => 'id',
				),
			),
			array(
				'key' => 'task_status_id',
				'reference' => array(
					'table' => 'task_statuses',
					'column' => 'id',
				),
			),
		));
	}

	public static function down()
	{
		\DBUtil::drop_table('tasks');
	}
}
