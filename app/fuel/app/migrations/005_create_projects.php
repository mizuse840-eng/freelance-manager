<?php

namespace Fuel\Migrations;

class Create_projects
{
	public static function up()
	{
		\DBUtil::create_table('projects', array(
			'id' => array('type' => 'int', 'unsigned' => true, 'null' => false, 'auto_increment' => true, 'constraint' => '11'),
			'client_id' => array('constraint' => '11', 'null' => false, 'type' => 'int', 'unsigned' => true),
			'project_status_id' => array('constraint' => '11', 'null' => false, 'type' => 'int', 'unsigned' => true),
			'name' => array('constraint' => 150, 'null' => false, 'type' => 'varchar'),
			'due_date' => array('null' => false, 'type' => 'date'),
			'created_at' => array('null' => false, 'type' => 'datetime'),
			'updated_at' => array('null' => false, 'type' => 'datetime'),
		), array('id'), true, false, null, array(
			array(
				'key' => 'client_id',
				'reference' => array(
					'table' => 'clients',
					'column' => 'id',
				),
			),
			array(
				'key' => 'project_status_id',
				'reference' => array(
					'table' => 'project_statuses',
					'column' => 'id',
				),
			),
		));
	}

	public static function down()
	{
		\DBUtil::drop_table('projects');
	}
}
