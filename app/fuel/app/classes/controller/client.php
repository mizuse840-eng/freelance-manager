<?php

class Controller_Client extends Controller_Base
{
	/**
	 * クライアント一覧
	 */
	public function action_index()
	{
		$clients = \Model_Client::find_all($this->login_user['id']);

		$this->template->title   = 'クライアント一覧';
		$this->template->content = \View::forge('client/index', array(
			'clients' => $clients,
		));
	}
}