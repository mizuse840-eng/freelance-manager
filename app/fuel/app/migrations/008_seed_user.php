<?php

namespace Fuel\Migrations;

class Seed_user
{
	public static function up()
	{
		$now = date('Y-m-d H:i:s');

		\DB::insert('users')->set(array(
			'name'       => 'テストユーザー',
			'email'      => 'test@example.com',
			'password'   => password_hash('password', PASSWORD_DEFAULT),
			'created_at' => $now,
			'updated_at' => $now,
		))->execute();
	}

	public static function down()
	{
		\DB::delete('users')
			->where('email', 'test@example.com')
			->execute();
	}
}