<?php
declare(strict_types=1);

namespace TestApp\Policy;

/**
 * A policy that conventionally matches TestApp\Model\Entity\FakeEntity.
 *
 * Its existence is the trap: substring-based resolution would return it for a
 * non-entity class. The interface-based resolver must NOT reach this.
 */
class FakeEntityPolicy
{
}
