<?php

namespace Fuel\Migrations;

class Create_users
{
	public static function up()
	{
		\DBUtil::create_table('users', array(
			'id' => array('type' => 'int', 'unsigned' => true, 'null' => false, 'auto_increment' => true, 'constraint' => '11'),
			'name' => array('constraint' => 100, 'null' => false, 'type' => 'varchar'),
			'email' => array('constraint' => 255, 'null' => false, 'type' => 'varchar'),
			'password' => array('constraint' => 255, 'null' => false, 'type' => 'varchar'),
			'created_at' => array('null' => false, 'type' => 'datetime'),
			'updated_at' => array('null' => false, 'type' => 'datetime'),
		), array('id'));

		\DBUtil::create_index('users', 'email', 'users_email_unique', 'unique');
	}

	public static function down()
	{
		\DBUtil::drop_table('users');
	}
}
