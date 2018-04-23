<?php
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

// @codingStandardsIgnoreFile

use Cake\Core\Configure;
use Cake\Core\Plugin;
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

require_once 'vendor/cakephp/cakephp/src/basics.php';
require_once 'vendor/autoload.php';

define('ROOT', $root . DS . 'tests' . DS . 'test_app' . DS);
define('APP', ROOT . 'TestApp' . DS);
define('TMP', sys_get_temp_dir() . DS);
define('CONFIG', ROOT . DS . 'config'. DS);
define('CACHE', TMP . 'cache' . DS);

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'TestApp',
    'encoding' => 'UTF-8',
    'paths' => [
        'plugins' => [ROOT . 'Plugin' . DS],
        'templates' => [ROOT . 'TestApp' . DS . 'Template' . DS]
    ]
]);

if (!getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}
if(!getenv('PREFER_LOWEST')) {
    error_reporting(E_ALL ^ E_USER_DEPRECATED);
}

ConnectionManager::config('test', ['url' => getenv('db_dsn')]);

Plugin::load('Authorization', [
    'path' => dirname(dirname(__FILE__)) . DS,
]);
// For policy task tests.
Plugin::load('Bake');
