<?php

namespace eZ\Publish\Core\REST\Server\Security;

use eZ\Publish\Core\MVC\Symfony\Security\User\Provider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class RestApiUserProvider extends Provider {
    
    /**
     * A stub for the refreshing the user.
     * 
     * Given the Rest API user interface is stateless, meaning the user is always fetched
     * during authentication, users do not need to refreshed from a persistent source.
     * Calling this method will always throw an UnsupportedUserException.
     *
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     * 
     * @return void
     */
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException('Rest API user provider does not rely on session storage to supply users as it is stateless.');
    }
}