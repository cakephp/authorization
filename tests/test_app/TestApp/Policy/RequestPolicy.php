<?php
namespace TestApp\Policy;

use Cake\Http\ServerRequest;

/**
 * For testing request based policies
 */
class RequestPolicy
{
    /**
     * Method to check if the request can be accessed
     *
     * @param null|\Authorization\IdentityInterface Identity
     * @param \Cake\Http\ServerRequest $request Request
     * @return bool
     */
    public function canAccess($identity, ServerRequest $request)
    {
        if ($request->getParam('controller') === 'Articles'
            && $request->getParam('action') === 'index'
        ) {
            return true;
        }

        return false;
    }
}
