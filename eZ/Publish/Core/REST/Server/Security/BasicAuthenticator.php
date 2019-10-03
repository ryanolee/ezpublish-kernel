<?php

/**
 * File containing the SessionBasedFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use eZ\Publish\Core\MVC\Symfony\Security\User\APIUserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Acts as a pre authenticator for the eZ API using HTTP Basic auth
 */
class BasicAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface{
    
    /**
     * @var RestRepositoryAuthenticatorProvider
     */
    protected $restRepositoryAuthenticatorProvider;

    public function __construct(RestRepositoryAuthenticatorProvider $restRepositoryAuthenticatorProvider)
    {
        $this->restRepositoryAuthenticatorProvider = $restRepositoryAuthenticatorProvider;
    }

    public function createToken(Request $request, $providerKey){

        // In the event that we are not using basic auth fall back to standard session auth
        if(null === $username = $request->headers->get('PHP_AUTH_USER')){
            return null;   
        }

        $password = $request->headers->get('PHP_AUTH_PW');
        return new UsernamePasswordToken(
            $username,
            $password,
            $providerKey
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UsernamePasswordToken && $token->getProviderKey() === $providerKey;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey){

        if (!$userProvider instanceof APIUserProviderInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of RepositoryAuthenticationProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $user = $userProvider->loadUserByUsername($token->getUsername());
        $token->setUser($user);

        return $this->restRepositoryAuthenticatorProvider->authenticate($token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        //!$request->attributes->get('is_rest_request')
        // Fallback incase onKernelExceptionView does not fire

        return new Response(
            strtr($exception->getMessageKey(), $exception->getMessageData()),
            Response::HTTP_UNAUTHORIZED
        );
    }
}