<?php
namespace Combine;

use Doctrine\DBAL\Connection;

class UserGateway
{
    const SELECT_SQL = 'SELECT * FROM user WHERE username = ?';
    const INSERT_SQL = 'INSERT INTO user (name, department, username) VALUES (?,?,?)';
    const UPDATE_SQL = 'UPDATE user SET name = ?, department = ? WHERE username = ?';

    /**
     * @var Doctrine\DBAL\Connection
     */
    protected $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @param $username
     * @return User|null
     */
    public function loadUser($username)
    {
        $user = null;
        $data = $this->conn->fetchAll(self::SELECT_SQL, array($username));

        if (count($data)) {
            $user = new User(
                $data[0]['username'],
                $data[0]['name'],
                $data[0]['department']
            );
        }
        return $user;
    }

    /**
     * @param ProtoUser $user
     * @return User
     */
    public function saveUser(ProtoUser $user)
    {
        $sql = $user instanceof User ? self::UPDATE_SQL : self::INSERT_SQL;
        $this->conn->executeUpdate($sql, array(
                $user->getName(),
                $user->getDepartment(),
                $user->getUsername(),
        ));
        $user = new User(
            $user->getUsername(),
            $user->getName(),
            $user->getDepartment()
        );

        return $user;
    }

} 