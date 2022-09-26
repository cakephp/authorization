<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Configure;
use Cake\Core\Plugin as CorePlugin;
use Cake\Datasource\ConnectionManager;

$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);
    throw new Exception('Cannot find the root of the application, unable to run tests');
};
$root = $findRoot(__FILE__);
unset($findRoot);
chdir($root);

define('ROOT', $root);
define('APP', ROOT . '/tests/test_app/');
define('TMP', sys_get_temp_dir() . '/');
define('CONFIG', ROOT . '/tests/test_app/config/');
define('CACHE', TMP . 'cache' . DS);
define('CORE_PATH', ROOT . '/vendor/cakephp/cakephp/');
define('CAKE', CORE_PATH . 'src/');

require ROOT . '/vendor/autoload.php';
require CORE_PATH . 'config/bootstrap.php';

Configure::write('debug', true);
Configure::write('App', [
    'base' => '',
    'namespace' => 'TestApp',
    'encoding' => 'UTF-8',
    'paths' => [
        'plugins' => [ROOT . '/tests/test_app/Plugin/'],
    ],
]);

if (!getenv('DB_URL')) {
    putenv('DB_URL=sqlite:///:memory:');
}

ConnectionManager::setConfig('test', ['url' => getenv('DB_URL')]);

CorePlugin::getCollection()->add(new \Authorization\Plugin());

Configure::write('Error.ignoredDeprecationPaths', [
    'vendor/cakephp/cakephp/src/TestSuite/Fixture/FixtureInjector.php',
]);
