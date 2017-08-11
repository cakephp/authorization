<?php
namespace Authorization;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\ORM\Table;

/**
 * Policy Locator
 */
class PolicyLocator {

	/**
	 * Locates the policy class for an object
	 *
	 * @param object $object Object
	 * @return string|bool
	 */
    public function locate($object)
    {
        $objectClass = get_class($object);
        $appNamespace = Configure::read('App.namespace');
        $middlePart = substr($objectClass, 0, strripos($objectClass, '\\'));
        $lastPart = substr($objectClass, strripos($objectClass, '\\') + 1);

        if (substr($middlePart, 0, strlen($appNamespace))) {
            $middlePart = substr($middlePart, strlen($appNamespace));
        }

        $suffixes = [
            Table::class => 'Table',
            Controller::class => 'Controller',
        ];

        foreach ($suffixes as $class => $suffix) {
            if ($object instanceof $class) {
                $lastPart = substr($lastPart, 0, -strlen($suffix));
                break;
            }
        }

        $class = '\Policy' . $middlePart . '\\' . $lastPart;
        $appClass = $appNamespace . $class;

        if (class_exists($appClass)) {
            return $appClass;
        }

        $plugins = Plugin::loaded();
        foreach ($plugins as $plugin) {
            if (class_exists($plugin . $class)) {
                return $plugin . $class;
            }
        }

        return false;
    }
}
