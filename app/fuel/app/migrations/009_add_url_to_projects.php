<?php

namespace Fuel\Migrations;

class Add_url_to_projects
{
	public static function up()
	{
		\DBUtil::add_fields('projects', array(
			'url' => array('type' => 'varchar', 'constraint' => 500, 'null' => true, 'after' => 'name'),
		));
	}

	public static function down()
	{
		\DBUtil::drop_fields('projects', array('url'));
	}
}
