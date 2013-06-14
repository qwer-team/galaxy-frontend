<?php

namespace Galaxy\FrontendBundle\Security\Authentication\Provider;

 
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Galaxy\FrontendBundle\Security\Authentication\Token\GalaxyToken;

class GalaxyProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        $pass = $token->getPass();
        $currentDate = new \DateTime();
        $userLockedExpiresAt = $user->getLockedExpiresAt();
        if ($user->getPassword() == $pass && $userLockedExpiresAt < $currentDate) {
            $user->setPassword(null);
            $authenticatedToken = new GalaxyToken($user->getRoles());
            $authenticatedToken->setUser($user);
            return $authenticatedToken;
        }

        throw new AuthenticationException('The WSSE authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof GalaxyToken;
    }
}