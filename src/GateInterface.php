<?php
namespace Authorization;

interface GateInterface
{
	public function allows($ability, array $arguments = []);

	public function denies($ability, array $arguments = []);
}
