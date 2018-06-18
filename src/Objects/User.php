<?php
/** Vahi\Objects\User class */
namespace Vahi\Objects;

/**
 * The user class.
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2018 Joost De Cock
 * @license MIT
 */
class User 
{
    /** @var \Slim\Container $container The container instance */
    protected $container;

    /** @var int $id Unique id of the user */
    private $id;

    /** @var string $invite Invite code */
    private $invite;

    /** @var string $notes Notes by admin */
    private $notes;

    /** @var bool $active Whether or not the invite is active */
    private $active;

    /** @var datetime $login Most recent login for this user */
    private $login;

    /** @var int $admin ID of the admin who created this user */
    private $admin;

    // constructor receives container instance
    public function __construct(\Slim\Container $container) 
    {
        $this->container = $container;
    }

    // Getters
    public function getInvite() 
    {
        return $this->invite;
    } 

    public function getId() 
    {
        return $this->id;
    } 

    public function getNotes() 
    {
        return $this->notes;
    } 

    public function getActive() 
    {
        if($this->active) return true;
        return false;
    } 

    public function getLogin() 
    {
        return $this->login;
    } 

    public function getAdmin() 
    {
        return $this->admin;
    } 

    // Setters
    public function setInvite($invite) 
    {
        $this->invite = $invite;
    } 

    public function setNotes($notes) 
    {
        $this->notes = $notes;
    } 

    public function setActive($active) 
    {
        $this->active = $active;
    } 

    public function setLogin($time=false) 
    {
        if($time === false) $time = date('Y-m-d H:i:s');
        $this->login = $time;
    } 

    public function setAdmin($id) 
    {
        $this->admin = $id;
    } 

    /**
     * Loads a user based on a unique identifier
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
        $sql = "SELECT * from `users` WHERE `$key` =".$db->quote($value);
        
        $result = $db->query($sql)->fetch(\PDO::FETCH_OBJ);
        $db = null;
        if(!$result) return false;
        else {
            foreach($result as $key => $val) {
                $this->{$key} = $val;
            } 
        }
    }
   
    /**
     * Loads a user based on their id
     *
     * @param int $id
     *
     * @return object|false A plain user object or false if user does not exist
     */
    public function loadFromId($id) 
    {
        return $this->load($id, 'id');
    }
   
    /**
     * Loads a user based on their invite code
     *
     * @param string $handle
     *
     * @return object|false A plain user object or false if user does not exist
     */
    public function loadFromInvite($code) 
    {
        return $this->load($code, 'invite');
    }
   
    /**
     * Creates a new user and stores it in database
     *
     * @param string $email The email of the new user
     * @param string $password The password of the new user
     *
     * @return int The id of the newly created user
     */
    public function create($notes, $adminid=0) 
    {
        // Set basic info    
        $this->setNotes($notes);
        $this->setInvite($this->generateInvite());

        // Store in database
        $db = $this->container->get('db');
        $sql = "INSERT into `users`(
            `invite`,
            `notes`,
            `admin`,
            `active`
             ) VALUES (
            ".$db->quote($this->getInvite()).",
            ".$db->quote($this->getNotes()).",
            ".$db->quote($adminid).",
            1
            );";
        $db->exec($sql);

        // Retrieve user ID
        $id = $db->lastInsertId();

        // Update instance from database
        $this->loadFromId($id);
    }

    /** Saves the user to the database */
    public function save() 
    {
        $db = $this->container->get('db');
        $sql = "UPDATE `users` set 
               `notes` = ".$db->quote($this->getNotes()).",
              `invite` = ".$db->quote($this->getInvite()).",
              `active` = ".$db->quote($this->getActive()).",
              `login` = ".$db->quote($this->getLogin()).",
               `admin` = ".$db->quote($this->getAdmin())."
            WHERE 
                  `id` = ".$db->quote($this->getId());
        $result = $db->exec($sql);
        $db = null;

        return $result;
    }
    
    /** Removes the user */
    public function remove() 
    {
        $db = $this->container->get('db');
        $sql = "
            DELETE from `users` WHERE `id` = ".$db->quote($this->getId()).";
        ";

        $result = $db->exec($sql);
        $db = null;

        return $result;
    }
    
    /**
     * Loads all ratings for a given user id
     */
    public function getRatings() 
    {
        $db = $this->container->get('db');
        $sql = "SELECT * from `ratings` WHERE `user` =".$db->quote($this->getId());
        $result = $db->query($sql)->fetchAll(\PDO::FETCH_OBJ);
        $db = null;
        
        if(!$result) return false;
        else {
            foreach($result as $key => $val) {
                $ratings[$val->id] = $val;
            }
        } 
        return $ratings;
    }
    
    private function generateInvite()
    {
        $uniq = false;
        while(!$uniq) {
            $code = substr(str_shuffle("abcdefghkmnpqrstuvwxyz234578"), 0, 8);
            $uniq = $this->inviteIsAvailable($code);
        }
 
        return $code;
    }
    
    /** 
     * Checks whether an invite is already taken
     *
     * @return bool true if it's free, false if not
     */
    private function inviteIsAvailable($code) 
    {
        $db = $this->container->get('db');
        $sql = 'SELECT `invite` FROM `users` WHERE  `invite` = '.$db->quote($code).' LIMIT 1';
        
        $result = $db->query($sql)->fetch(\PDO::FETCH_OBJ);
        $db = null;
    
        if ($result) return false;
        else return true;
    }

}
