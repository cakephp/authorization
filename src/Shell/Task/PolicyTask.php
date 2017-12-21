<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization\Shell\Task;

use Bake\Shell\Task\SimpleBakeTask;
use Cake\Utility\Inflector;

/**
 * Bake task for building policy classes
 */
class PolicyTask extends SimpleBakeTask
{
    /**
     * path to Policy directory
     *
     * @var string
     */
    public $pathFragment = 'Policy/';

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return 'policy';
    }

    /**
     * {@inheritDoc}
     */
    public function fileName($name)
    {
        if ($this->param('type') === 'table') {
            $name .= 'Table';
        }
        return $name . 'Policy.php';
    }

    /**
     * {@inheritDoc}
     */
    public function template()
    {
        return 'Authorization.policy';
    }

    /**
     * {@inheritDoc}
     */
    public function templateData()
    {
        $data = parent::templateData();

        $name = $this->_getName($this->args[0]);
        $type = $this->param('type');
        $suffix = '';
        if ($type === 'table') {
            $suffix = 'Table';
        }

        $variable = Inflector::variable($name);
        if ($variable == 'user') {
            $variable = 'resource';
        }

        $vars = [
            'name' => $name,
            'type' => $type,
            'suffix' => $suffix,
            'variable_name' => $variable,
        ];

        return array_merge($data, $vars);
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        return $parser
            ->description('Bake policy classes for various supported object types.')
            ->addOption('type', [
                'help' => 'The object type to bake a policy for. If only one argument is used, type will be object.',
                'default' => 'entity',
                'choices' => ['table', 'entity', 'object'],
                'required' => true
            ]);
    }

    /**
     * Do nothing (for now)
     *
     * @return void
     */
    public function bakeTest($className)
    {
        // TODO
    }
}
