<?php
namespace Combine;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class LdapUserProvider implements UserProviderInterface
{
    protected $gateway;

    public function __construct(UserGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->gateway->loadUser($username);
        if (!$user) {
            $user = new ProtoUser($username);
        }
        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    public function supportsClass($class)
    {
        $userClass = 'Combine\ProtoUser';
        return ($userClass === $class || is_subclass_of($class, $userClass));
    }
}