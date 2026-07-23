<?php

namespace Fuel\Migrations;

class Create_clients
{
	public static function up()
	{
		\DBUtil::create_table('clients', array(
			'id' => array('type' => 'int', 'unsigned' => true, 'null' => false, 'auto_increment' => true, 'constraint' => '11'),
			'user_id' => array('constraint' => '11', 'null' => false, 'type' => 'int', 'unsigned' => true),
			'name' => array('constraint' => 100, 'null' => false, 'type' => 'varchar'),
			'created_at' => array('null' => false, 'type' => 'datetime'),
			'updated_at' => array('null' => false, 'type' => 'datetime'),
		), array('id'), true, false, null, array(
			array(
				'key' => 'user_id',
				'reference' => array(
					'table' => 'users',
					'column' => 'id',
				),
			),
		));
	}

	public static function down()
	{
		\DBUtil::drop_table('clients');
	}
}
