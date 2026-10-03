<?php
declare(strict_types=1);

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
namespace Authorization\Command;

use Authorization\IdentityInterface;
use Bake\Command\SimpleBakeCommand;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Query\SelectQuery;
use Cake\Utility\Inflector;
use RuntimeException;

/**
 * Bake task for building policy classes
 */
class PolicyCommand extends SimpleBakeCommand
{
    /**
     * Path to Policy directory
     */
    public string $pathFragment = 'Policy/';

    protected string $type;

    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'policy';
    }

    /**
     * @inheritDoc
     */
    public function fileName(string $name): string
    {
        if ($this->type === 'table') {
            $name .= 'Table';
        }

        return $name . 'Policy.php';
    }

    /**
     * @inheritDoc
     */
    public function template(): string
    {
        return 'Authorization.policy';
    }

    /**
     * @inheritDoc
     */
    public function templateData(): array
    {
        $data = parent::templateData();

        $name = $this->args->getArgument('name');
        if (!$name) {
            throw new RuntimeException('You must specify name of policy to create.');
        }

        $name = $this->getNameWithoutPrefix($name);
        $this->type = (string)$this->args->getOption('type');
        $type = $this->type;

        $suffix = '';
        if ($type === 'table') {
            $suffix = 'Table';
        }

        $imports = [];
        $className = $data['namespace'] . '\\' . $name;
        if ($type === 'table') {
            $className = sprintf('%s\Model\Table\%s%s', $data['namespace'], $name, $suffix);
            $imports[] = SelectQuery::class;
        } elseif ($type === 'entity') {
            $className = sprintf('%s\Model\Entity\%s', $data['namespace'], $name);
            $imports[] = $className;
        }
        $imports[] = IdentityInterface::class;

        $variable = Inflector::variable($name);
        if ($variable === 'user') {
            $variable = 'resource';
        }

        $vars = [
            'name' => $name,
            'type' => $type,
            'suffix' => $suffix,
            'variable_name' => $variable,
            'classname' => $className,
            'imports' => $imports,
        ];

        return $vars + $data;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to update.
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = $this->setCommonOptions($parser);

        return $parser
            ->setDescription('Bake policy classes for various supported object types.')
            ->addArgument('name', [
                'help' => 'The name of the policy class to create.',
            ])
            ->addOption('type', [
                'help' => 'The object type to bake a policy for. If only one argument is used, type will be object.',
                'default' => 'entity',
                'choices' => ['table', 'entity', 'object'],
                'required' => true,
            ]);
    }

    /**
     * Do nothing (for now)
     *
     * @param string $className The class to bake a test for.
     * @return void
     */
    public function bakeTest(string $className): void
    {
    }
}
