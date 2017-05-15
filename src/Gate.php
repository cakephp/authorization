<?php
namespace Cake\Authorization;

use Authorization\GateInterface;

class Gate implements GateInterface {

	protected $identity;

	protected $policies = [];

	protected $abilities = [];

	public function __construct(array $abilities = [], array $policies = [])
	{
		$this->abilities = $abilities;
		$this->policies = $policies;
	}

	public function allows($ability, array $arguments = [])
	{

	}

	public function denies($ability, array $arguments = [])
	{

	}

	public function raw($ability, $arguments = [])
	{
		if ($this->identity === null) {
			return false;
		}
	}

	public function setIdentity($identity)
	{
		$this->identity = $identity;
	}

	public function define($ability, callable $callback)
	{
		$this->abilities[$ability] = $callback;

		return $this;
	}

	public function setPolicy()
    {

    }

	public function getPolicyFor($object)
	{
		$class = get_class($object);
		if (!isset($this->policies[$class])) {

		}

		$policy = $this->policies[$class];
		if (is_string($policy)) {

		}
	}
}
