<?php
namespace TestApp\Policy;

use Authorization\Policy\RequestPolicyInterface;
use Cake\Http\ServerRequest;

/**
 * For testing request based policies
 */
class RequestPolicy implements RequestPolicyInterface
{
    /**
     * Method to check if the request can be accessed
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
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

        // More checks here

        return false;
    }
}
