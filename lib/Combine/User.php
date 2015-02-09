<?php
namespace Combine;

use Symfony\Component\Security\Core\User\UserInterface;

class User extends ProtoUser
{
    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * @param $department
     * @throws \InvalidArgumentException
     */
    public function setDepartment($department)
    {

        if (!$department || $this->validDepartment($department)) {
            $this->department = (string) $department;
        } else {
            throw new \InvalidArgumentException('Invalid department');
        }
    }
}