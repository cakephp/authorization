<?php
namespace Cake\Authorization;

use Cake\Controller\Controller;
use Cake\Core\App;

class ControllerGate {



	public static function createFromController(Controller $controller)
	{
		$request = $controller->request;

		$class = $request->getParam('controller');
		$plugin = $request->getParam('plugin');
		if ($plugin) {
			$class = $plugin . '.' . $class;
		}

		$class = App::className($class, 'Authorization/Policy', 'Policy');
		$gate = new $class();

		if (class_exists($class)) {
			$gate->addPolicy(get_class($controller), $class);
		}

		$gate = new self();

		$identity = $request->getAttribute('identity');
		if (!empty($identity)) {
			$gate->setIdentity();
		}

		return $gate;
	}
}
