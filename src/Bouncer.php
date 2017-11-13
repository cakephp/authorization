<?php
namespace Authorization;

use RuntimeException;

/**
 * Bouncer
 */
class Bouncer implements BouncerInterface
{

    protected $identity;

    protected static $identityResolver;

    protected $policies = [];

    protected $beforeCallbacks = [];

    protected $afterCallBacks = [];

    public function __construct(callable $identityResolver = null, array $policies = [])
    {
        $this->policies = $policies;
        static::$identityResolver = $identityResolver;
    }

    public function addAfterCallback(callable $callback)
    {
        $this->afterCallbacks[$callback];
    }

    public function addBeforeCallback($callback)
    {
        $this->beforeCallbacks[$callback];
    }

    /**
     * Raw authorization check
     *
     * @param string Ability string
     * @param array $arguments Arguments to pass to the policy
     * @return bool
     */
    public function allows($ability, array $arguments = [])
    {
        return $this->raw($ability, $arguments);
    }

    /**
     * Raw authorization check
     *
     * @param string Ability string
     * @param array $arguments Arguments to pass to the policy
     * @return bool
     */
    public function denies($ability, array $arguments = [])
    {
        return !$this->raw($ability, $arguments);
    }

    /**
     * Raw authorization check
     *
     * @param string Ability string
     * @param array $arguments Arguments to pass to the policy
     * @return bool
     */
    public function raw($ability, array $arguments = [])
    {
        $identity = $this->resolveIdentity();

        if (empty($identity)) {
            return false;
        }

        return $this->callAuthCallback($identity, $ability, $arguments);
    }

    /**
     * Resolves and calls the callbacks
     *
     * @param array $identity Identity
     * @param string $ability Ability
     * @param array $arguments Arguments passed to the callback
     * @return bool
     */
    protected function callAuthCallback($identity, $ability, array $arguments)
    {
        $callback = $this->resolveAuthCallback($identity, $ability, $arguments);

        return $callback($identity, ...$arguments);
    }

    /**
     * Resolve the callable for the given ability and arguments.
     *
     * @param array $identity
     * @param string $ability
     * @param array $arguments
     * @return callable
     */
    protected function resolveAuthCallback($identity, $ability, array $arguments)
    {
        if (isset($arguments[0])) {
            if (!is_null($policy = $this->getPolicyFor($arguments[0]))) {
                return $this->resolvePolicyCallback($identity, $ability, $arguments, $policy);
            }
        }

        return function () {
            return false;
        };
    }

    /**
     * Resolve the callback for a policy check.
     *
     * @param array $identity Identity
     * @param string  $ability Ability
     * @param array  $arguments Arguments
     * @param mixed  $policy Policy
     * @return callable
     */
    protected function resolvePolicyCallback($identity, $ability, array $arguments, $policy)
    {
        return function () use ($identity, $ability, $arguments, $policy) {
            // If this first argument is a string, that means they are passing a class name
            // to the policy. We will remove the first argument from this argument array
            // because this policy already knows what type of models it can authorize.
            if (isset($arguments[0]) && is_string($arguments[0])) {
                array_shift($arguments);
            }

            return is_callable([$policy, $ability])
                ? $policy->{$ability}($identity, ...$arguments)
                : false;
        };
    }

    /**
     * Add a policy for an object
     *
     * @param string|object $object Object
     * @param string $policy Policy class
     * @return $this
     */
    public function addPolicyFor($object, $policy)
    {
        if (is_object($object)) {
            $object = get_class($object);
        }

        if (!is_string($object) || class_exists($object)) {
            throw new RuntimeException('Invalid object given');
        }

        $this->policies[$object] = $policy;

        return $this;
    }

    /**
     * Returns the policy object for another object
     *
     * @param object $object Any instance
     * @return object
     */
    public function getPolicyFor($object)
    {
        $class = get_class($object);

        if (!isset($this->policies[$class])) {
            throw new RuntimeException(sprintf(
                'There is no policy defined for `%s`',
                $class
            ));
        }

        $policy = $this->policies[$class];
        if (is_string($policy)) {
            $this->policies[$class] = new $policy();
        }

        return $this->policies[$class];
    }

    /**
     * Set an identity resolver callback
     *
     * @param callable
     * @return void
     */
    public static function setIdentityResolver(callable $resolver)
    {
        static::$identityResolver = $resolver;
    }

    /**
     * Returns the current logged in identity
     *
     * @return array|EntityInterface|null
     */
    public function resolveIdentity()
    {
        $callable = static::$identityResolver;

        return $callable();
    }

    /**
     * Get a gate instance for the given user.
     *
     * @param array
     * @return static
     */
    public function forUser($identity)
    {
        $callback = function () use ($identity) {
            return $identity;
        };

        return new static($callback, $this->policies);
    }

}
