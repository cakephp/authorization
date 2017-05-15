<?php
namespace Authorization;

use Authorization\PolicyInterface;

trait AuthorizationTrait {

	/**
	 * @var \Cake\Authorization\PolicyInterface
	 */
	protected $policy;

	public function setPolicy(PolicyInterface $policy)
	{
		$this->policy = $policy;

		return $this;
	}

	public function getPolicy()
	{
		if (!$this->policy instanceof PolicyInterface) {
			throw new \RuntimeException('Missing policy for class `%s`', get_class($this));
		}

		return $this->policy;
	}

	/**
	 * @return bool
	 */
	public function can($user, $do)
	{
		return $this->getPolicy()->can($user, $do);
	}
}


