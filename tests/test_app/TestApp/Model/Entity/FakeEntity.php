<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

/**
 * Lives under the `\Model\Entity\` namespace but is NOT an EntityInterface.
 *
 * Used to prove the resolver discriminates by interface, not by namespace
 * substring: a matching FakeEntityPolicy exists, so a substring-based lookup
 * would wrongly resolve it, while the interface check rejects it.
 */
class FakeEntity
{
}
