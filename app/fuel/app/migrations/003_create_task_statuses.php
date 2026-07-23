<?php

namespace Fuel\Migrations;

class Create_task_statuses
{
	public static function up()
	{
		\DBUtil::create_table('task_statuses', array(
			'id' => array('type' => 'int', 'unsigned' => true, 'null' => false, 'auto_increment' => true, 'constraint' => '11'),
			'name' => array('constraint' => 50, 'null' => false, 'type' => 'varchar'),
			'sort_order' => array('constraint' => '11', 'null' => false, 'type' => 'int', 'unsigned' => true, 'default' => 0),
			'created_at' => array('null' => false, 'type' => 'datetime'),
			'updated_at' => array('null' => false, 'type' => 'datetime'),
		), array('id'));
	}

	public static function down()
	{
		\DBUtil::drop_table('task_statuses');
	}
}
