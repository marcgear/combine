<?php
namespace Combine;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class LdapUserToken extends AbstractToken
{
    public $password;

    public function __construct(array $roles = array())
    {
        parent::__construct($roles);
        $this->setAuthenticated(count($roles) > 0);
    }

    public function getCredentials()
    {
        return '';
    }
}