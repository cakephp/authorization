<?php
namespace Authorization;

use Cake\Core\Exception\Exception;

class AuthorizationException extends Exception {

    /**
     * @inheritDoc
     */
    protected $_messageTemplate = 'You don`t have the permission.';
}
