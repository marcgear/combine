<?php
namespace Combine;

use Symfony\Component\Security\Core\User\UserInterface;

class ProtoUser implements UserInterface
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $department;

    /**
     * @var array
     */
    protected $departments = array(
        'Finance',
        'People',
        'Operations',
        'M4B',
        'Business Ops',
        'Project Delivery',
        'Product',
        'UX',
        'Technology',
        'Business Apps',
        'Tech Ops',
        'Creative',
        'Brand & Comms',
        'Marketing',
        'Exec',
    );

    /**
     * @param null $username
     * @param null $name
     * @param null $department
     */
    public function __construct($username = null, $name = null, $department = null)
    {
        $this->username   = $username;
        $this->name       = $name;
        if ($this->validDepartment($department)) {
            $this->department = $department;
        }
    }

    /**
     * @param $department
     * @return bool
     */
    protected function validDepartment($department)
    {
        return in_array($department, $this->departments);
    }

    /**
     * no salt - as auth should be being done by ldap
     *
     * @return empty string
     */
    public function getSalt()
    {
        return '';
    }

    /**
     * no password either - auth done by ldap
     *
     * @return empty string
     */
    public function getPassword()
    {
        return '';
    }

    /**
     * @return array|\Symfony\Component\Security\Core\Role\Role[]
     */
    public function getRoles()
    {
        return array('ROLE_USER');
    }

    /**
     * does nothing,
     */
    public function eraseCredentials()
    {
        return;
    }

    /**
     * @return null|string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @return array
     */
    public function getDepartments()
    {
        return $this->departments;
    }
}