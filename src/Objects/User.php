<?php
/** EyeFu\Objects\User class */
namespace EyeFu\Objects;

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

    // constructor receives container instance
    public function __construct(\Slim\Container $container) 
    {
        $this->container = $container;
        $this->data = clone $this->container->get('JsonStore');
    }

    // Getters
    private function getInvite() 
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

    // Setters
    public function setInvite($invite) 
    {
        $this->invite = $invite;
    } 

    public function setNotes($notes) 
    {
        $this->invite = $notes;
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
            foreach($result as $f) {
                $this->{$f} = $result->{$f};
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
    public function create($notes) 
    {
        // Set basic info    
        $this->setNotes($notes);
        $this->setInvite($this->generateInvite());

        // Store in database
        $db = $this->container->get('db');
        $sql = "INSERT into `users`(
            `invite`,
            `notes`
             ) VALUES (
            ".$db->quote($this->getInvite()).",
            ".$db->quote($this->getNotes())."
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
               `notes` = ".$db->quote(Utilities::encrypt($this->getEmail(), $nonce)).",
              `invite` = ".$db->quote(Utilities::encrypt($this->getUsername(), $nonce))."
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
            DELETE from `ratings` WHERE `user` = ".$db->quote($this->getId()).";
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
            $uniq = $this->inviteIsAvailable($code, $type);
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
