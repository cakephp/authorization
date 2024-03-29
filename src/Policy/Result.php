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
 * @since         1.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authorization\Policy;

/**
 * Policy check result
 */
class Result implements ResultInterface
{
    /**
     * Check status.
     *
     * @var bool
     */
    protected bool $status;

    /**
     * Failure reason.
     *
     * @var string|null
     */
    protected ?string $reason = null;

    /**
     * Constructor
     *
     * @param bool $status Check status.
     * @param string|null $reason Failure reason.
     */
    public function __construct(bool $status, ?string $reason = null)
    {
        $this->status = $status;
        if ($reason !== null) {
            $this->reason = $reason;
        }
    }

    /**
     * @inheritDoc
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): bool
    {
        return $this->status;
    }
}
