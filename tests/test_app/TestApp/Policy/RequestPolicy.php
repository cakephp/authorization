<?php
namespace TestApp\Policy;

/**
 * For testing request based policies
 */
class RequestPolicy
{
    public function canIndex($identity, $request)
    {
        return true;
    }

    public function canAdd($identity, $request)
    {
        return true;
    }

    public function canEdit($identity, $request)
    {
        return true;
    }

    public function canDelete($identity, $request)
    {
        return false;
    }
}
