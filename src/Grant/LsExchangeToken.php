<?php

namespace League\OAuth2\Client\Grant;

class LsExchangeToken extends AbstractGrant
{
    public function __toString()
    {
        return 'ls_exchange_token';
    }

    protected function getRequiredRequestParameters()
    {
        return [
            'ls_exchange_token',
        ];
    }

    protected function getName()
    {
        return 'ls_exchange_token';
    }
}
