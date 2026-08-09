<?php

namespace Fuel\Migrations;

class Add_url_to_clients
{
	public static function up()
	{
		\DBUtil::add_fields('clients', array(
			'url' => array('type' => 'varchar', 'constraint' => 500, 'null' => true, 'after' => 'name'),
		));
	}

	public static function down()
	{
		\DBUtil::drop_columns('clients', array('url'));
	}
}
