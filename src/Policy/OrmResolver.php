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
namespace Authorization\Policy;

use Authorization\Policy\Exception\MissingPolicyException;
use Cake\Core\App;
use Cake\Core\ContainerInterface;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\RepositoryInterface;
use RuntimeException;

/**
 * Policy resolver that applies conventions based policy classes
 * for CakePHP ORM Tables, Entities and Queries.
 */
class OrmResolver implements ResolverInterface
{
    /**
     * Application namespace.
     */
    protected string $appNamespace = 'App';

    /**
     * Plugin name overrides.
     *
     * @var array<string, string>
     */
    protected array $overrides = [];

    /**
     * The DIC instance from the application
     */
    protected ?ContainerInterface $container;

    /**
     * Constructor
     *
     * @param string $appNamespace The application namespace
     * @param array<string, string> $overrides A list of plugin name overrides.
     * @param \Cake\Core\ContainerInterface|null $container The DIC instance from the application
     */
    public function __construct(
        string $appNamespace = 'App',
        array $overrides = [],
        ?ContainerInterface $container = null,
    ) {
        $this->appNamespace = $appNamespace;
        $this->overrides = $overrides;
        $this->container = $container;
    }

    /**
     * Get a policy for an ORM Table, Entity, Query or class name string.
     *
     * Accepting an entity/table fully-qualified class name as the resource allows checks
     * like `$user->can('add', Article::class)` where no instance is on hand
     * (e.g. menu rendering before a `newEmptyEntity()`).
     *
     * @param mixed $resource The resource.
     * @return mixed
     * @throws \Authorization\Policy\Exception\MissingPolicyException When a policy for the
     *   resource has not been defined or cannot be resolved.
     */
    public function getPolicy(mixed $resource): mixed
    {
        if ($resource instanceof EntityInterface) {
            return $this->getEntityPolicy($resource::class);
        }
        if ($resource instanceof RepositoryInterface) {
            return $this->getRepositoryPolicy($resource::class);
        }
        if ($resource instanceof QueryInterface) {
            $repo = $resource->getRepository();
            if (!$repo instanceof RepositoryInterface) {
                throw new RuntimeException('No repository set for the query.');
            }

            return $this->getRepositoryPolicy($repo::class);
        }
        if (is_string($resource) && class_exists($resource)) {
            if (is_subclass_of($resource, EntityInterface::class)) {
                return $this->getEntityPolicy($resource);
            }
            if (is_subclass_of($resource, RepositoryInterface::class)) {
                return $this->getRepositoryPolicy($resource);
            }

            throw new MissingPolicyException([$resource]);
        }

        throw new MissingPolicyException([get_debug_type($resource)]);
    }

    /**
     * Get a policy for an entity class.
     *
     * @param string $class The entity class name to get a policy for.
     * @return mixed
     */
    protected function getEntityPolicy(string $class): mixed
    {
        $entityNamespace = '\Model\Entity\\';
        $namespace = str_replace('\\', '/', substr($class, 0, (int)strpos($class, $entityNamespace)));
        $name = str_replace('\\', '/', substr($class, (int)strpos($class, $entityNamespace) + strlen($entityNamespace)));

        return $this->findPolicy($class, $name, $namespace);
    }

    /**
     * Get a policy for a table/repository class.
     *
     * @param string $class The table/repository class name to get a policy for.
     * @return mixed
     */
    protected function getRepositoryPolicy(string $class): mixed
    {
        $tableNamespace = '\Model\Table\\';
        $namespace = str_replace('\\', '/', substr($class, 0, (int)strpos($class, $tableNamespace)));
        $name = str_replace('\\', '/', substr($class, (int)strpos($class, $tableNamespace) + strlen($tableNamespace)));

        return $this->findPolicy($class, $name, $namespace);
    }

    /**
     * Locate a policy class using conventions
     *
     * @param string $class The full class name.
     * @param string $name The name suffix of the resource.
     * @param string $namespace The namespace to find the policy in.
     * @throws \Authorization\Policy\Exception\MissingPolicyException When a policy for the
     *   resource has not been defined.
     * @return mixed
     */
    protected function findPolicy(string $class, string $name, string $namespace): mixed
    {
        $namespace = $this->getNamespace($namespace);
        $policyClass = null;

        // plugin entities can have application overrides defined.
        if ($namespace !== $this->appNamespace) {
            $policyClass = App::className($name, 'Policy\\' . $namespace, 'Policy');
        }

        // Check the application/plugin.
        if ($policyClass === null) {
            $policyClass = App::className($namespace . '.' . $name, 'Policy', 'Policy');
        }

        if ($policyClass === null) {
            throw new MissingPolicyException([$class]);
        }

        if ($this->container instanceof ContainerInterface && $this->container->has($policyClass)) {
            return $this->container->get($policyClass);
        }

        return new $policyClass();
    }

    /**
     * Returns plugin namespace override if exists.
     *
     * @param string $namespace The namespace to find the policy in.
     * @return string
     */
    protected function getNamespace(string $namespace): string
    {
        return $this->overrides[$namespace] ?? $namespace;
    }
}
