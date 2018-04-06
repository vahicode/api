<?php
/** EyeFu\Objects\Admin class */
namespace EyeFu\Objects;

/**
 * The admin class.
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2018 Joost De Cock
 * @license MIT
 */
class Admin 
{
    /** @var \Slim\Container $container The container instance */
    protected $container;

    /** @var int $id Unique id of the admin */
    private $id;

    /** @var string $username The username of the admin */
    private $username;

    /** @var string $password Password hash/salt/algo combo */
    private $password;

    /** @var int $userid The user linked to this admin */
    private $userid;

    // constructor receives container instance
    public function __construct(\Slim\Container $container) 
    {
        $this->container = $container;
    }

    // Getters
    public function getId() 
    {
        return $this->id;
    } 

    public function getUsername() 
    {
        return $this->username;
    } 

    private function getPassword() 
    {
        return $this->password;
    } 
    
    public function getUserid() 
    {
        return $this->userid;
    } 

    public function getRole() 
    {
        return $this->role;
    } 

    // Setters
    public function setUsername($username) 
    {
        $this->username = $username;
    } 

    public function setPassword($password) 
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function setUserid($id) 
    {
        $this->userid = $id;
    } 
     
    public function setRole($role) 
    {
        $this->role = $role;
    } 

    public function isAdmin() 
    {
        if($this->role === 'admin' || $this->isSuperAdmin()) return true;

        return false;
    }

    public function isSuperAdmin() 
    {
        if($this->role === 'superadmin') return true;

        return false;
    }

    /** Verifies the admin's password */
    public function checkPassword($password)
    {
        return(password_verify($password, $this->password));
    }
     
    /**
     * Loads an admin based on a unique identifier
     *
     * @param string $key   The unique column identifying the user. 
     *                      One of id/invite. Defaults to id
     * @param string $value The value to look for in the key column
     *
     * @return object|false A plain user object or false if user does not exist
     */
    private function load($value, $key='id') 
    {
        $db = $this->container->get('db');
        $sql = "SELECT * from `admins` WHERE `$key` =".$db->quote($value);
        
        $result = $db->query($sql)->fetch(\PDO::FETCH_OBJ);
        $db = null;
        if(!$result) return false;
        foreach($result as $key => $val) {
            $this->{$key} = $val;
        } 
    }
   
    /**
     * Loads an admin based on their id
     *
     * @param int $id
     *
     * @return object|false A plain admin object or false if the admin does not exist
     */
    public function loadFromId($id) 
    {
        return $this->load($id, 'id');
    }
   
    /**
     * Loads an admin based on their username
     *
     * @param string $handle
     *
     * @return object|false A plain admin object or false if the admin does not exist
     */
    public function loadFromUsername($code) 
    {
        return $this->load($code, 'username');
    }
   
    /**
     * Loads an admin based on their userid
     *
     * @param string $handle
     *
     * @return object|false A plain admin object or false if the admin does not exist
     */
    public function loadFromUserid($id) 
    {
        return $this->load($code, 'userid');
    }
   
    /**
     * Creates a new admin and stores it in database
     *
     * @param string $email The email of the new user
     * @param string $password The password of the new user
     *
     * @return int The id of the newly created user
     */
    public function create($username, $password, $role='admin') 
    {
        // Create a user for this admin
        $user = $this->container->get('User');
        $user->create("This user is linked to admin $username");

        // Set basic info    
        $this->setPassword($password);
        $this->setUsername($username);
        $this->setRole($role);
        $this->setUserid($user->getId());
        
        // Store in database
        $db = $this->container->get('db');
        $sql = "INSERT into `admins`(
            `username`,
            `password`,
            `role`,
            `userid`    
             ) VALUES (
            ".$db->quote($this->getUsername()).",
            ".$db->quote($this->getPassword()).",
            ".$db->quote($this->getRole()).",
            ".$db->quote($this->getUserid())."
            );";
        $db->exec($sql);

        // Retrieve admin ID
        $id = $db->lastInsertId();

        // Update instance from database
        $this->loadFromId($id);

        // Update user
        $user->setAdmin($id);
        $user->save();
    }

    /** Saves the admin to the database */
    public function save() 
    {
        $db = $this->container->get('db');
        $sql = "UPDATE `admins` set 
               `username` = ".$db->quote($this->getUsername()).",
               `password` = ".$db->quote($this->getPassword()).",
                   `role` = ".$db->quote($this->getRole()).",
                 `userid` = ".$db->quote($this->getUserid())."
            WHERE 
                  `id` = ".$db->quote($this->getId());
        $result = $db->exec($sql);
        $db = null;

        return $result;
    }
    
    /** Removes the admin */
    public function remove() 
    {
        $db = $this->container->get('db');
        $sql = "
            DELETE from `admins` WHERE `id` = ".$db->quote($this->getId()).";
        ";

        $result = $db->exec($sql);
        $db = null;

        return $result;
    }
    
    /** 
     * Checks whether an admin username is already taken
     *
     * @return bool true if it's free, false if not
     */
    public function usernameIsAvailable($username) 
    {
        $db = $this->container->get('db');
        $sql = 'SELECT `username` FROM `admins` WHERE  `username` = '.$db->quote($username).' LIMIT 1';
        
        $result = $db->query($sql)->fetch(\PDO::FETCH_OBJ);
        $db = null;
    
        if ($result) return false;
        else return true;
    }
} 
