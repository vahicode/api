<?php
/** Vahi\Objects\Eye class */
namespace Vahi\Objects;

/**
 * The eye class.
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2018 Joost De Cock
 * @license MIT
 */
class Eye 
{
    /** @var \Slim\Container $container The container instance */
    protected $container;

    /** @var int $id Unique id of the eye */
    private $id;

    /** @var string $notes Notes by admin */
    private $notes;

    /** @var int $admin ID of the admin who created this eye */
    private $admin;

    /** @var bool $active Whether this eye can be rated or not */
    private $active;

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

    public function getNotes() 
    {
        return $this->notes;
    } 

    public function getAdmin() 
    {
        return $this->admin;
    } 

    public function getActive() 
    {
        if($this->active) return true;
        return false;
    } 

    // Setters
    public function setNotes($notes) 
    {
        $this->notes = $notes;
    } 

    public function setAdmin($id) 
    {
        $this->admin = $id;
    } 

    public function setActive($active) 
    {
        if($active) $this->active = true;
        else $this->active = false;
    } 


    /**
     * Loads an eye based on its id
     *
     * @param int $id   The id of the eye 
     *
     * @return object|false A plain user object or false if eye does not exist
     */
    public function load($id) 
    {
        $db = $this->container->get('db');
        $sql = "SELECT * from `eyes` WHERE `id` =".$db->quote($id);
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
     * Creates a new eye and stores it in database
     *
     * @param string $admin The ID of the admin who created the eye
     *
     * @return int The id of the newly created eye
     */
    public function create($admin) 
    {
        // Store in database
        $db = $this->container->get('db');
        $sql = "INSERT into `eyes`(
            `admin`,
            `active`
             ) VALUES (
             ".$db->quote($admin).",
             0
            );";
        $db->exec($sql);

        // Retrieve eye ID
        $id = $db->lastInsertId();

        // Update instance from database
        $this->load($id);

        return $id;
    }

    /** Saves the eye to the database */
    public function save() 
    {
        $db = $this->container->get('db');
        $sql = "UPDATE `eyes` set 
               `notes` = ".$db->quote($this->getNotes()).",
              `active` = ".$db->quote($this->getActive()).",
               `admin` = ".$db->quote($this->getAdmin())."
            WHERE 
                  `id` = ".$db->quote($this->getId());
        $result = $db->exec($sql);
        $db = null;

        return $result;
    }
    
    /** Removes the eye */
    public function remove() 
    {
        $db = $this->container->get('db');
        $sql = "
            DELETE from `eyes` WHERE `id` = ".$db->quote($this->getId()).";
        ";

        $result = $db->exec($sql);
        $db = null;

        return $result;
    }
}
