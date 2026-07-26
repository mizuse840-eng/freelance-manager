<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.8.2
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

return array(
	/**
	 * -------------------------------------------------------------------------
	 *  Default route
	 * -------------------------------------------------------------------------
	 *
	 */

	'_root_' => 'auth/login',

	/**
	 * -------------------------------------------------------------------------
	 *  Page not found
	 * -------------------------------------------------------------------------
	 *
	 */

	'_404_' => 'welcome/404',

	/**
	 * -------------------------------------------------------------------------
	 *  Example for Presenter
	 * -------------------------------------------------------------------------
	 *
	 *  A route for showing page using Presenter
	 *
	 */

	'login'   => 'auth/login',
	'logout'  => 'auth/logout',
	'clients' => 'client/index',
	'clients/(:num)/projects' => 'project/index/$1',
	'clients/(:num)/projects/create' => 'project/create/$1',
	'projects/edit/(:num)' => 'project/edit/$1',
	'projects/delete/(:num)' => 'project/delete/$1',
	'projects/(:num)/tasks' => 'task/index/$1',
	'projects/(:num)/tasks/create' => 'task/create/$1',
	'tasks/edit/(:num)' => 'task/edit/$1',
	'tasks/delete/(:num)' => 'task/delete/$1',
	'clients/(:any)' => 'client/$1',
);
