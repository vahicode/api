<?php
/** Vahi\Objects\Rating class */
namespace Vahi\Objects;

/**
 * The rating class.
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2018 Joost De Cock
 * @license MIT
 */
class Rating 
{
    /** @var \Slim\Container $container The container instance */
    protected $container;

    /** @var int $id Unique id of the rating */
    private $id;

    /** @var int $user The ID of the user who created the rating */
    private $user;

    /** @var int $eye The ID of the eye that is rated */
    private $eye;

    /** @var datetime $time Time when the rating was submitted */
    private $time;

    /** @var array $vzones Per-zone rating of vascularity */
    private $vzones = [];

    /** @var array $hzones Per-zone rating of haze */
    private $hzones = [];

    /** @var array $izones Per-zone rating of integrity */
    private $izones = [];

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

    public function getUser() 
    {
        return $this->user;
    } 

    public function getEye() 
    {
        return $this->eye;
    } 

    public function getTime() 
    {
        return $this->time;
    } 

    public function getVzones() 
    {
        return $this->vzones;
    } 

    public function getHzones() 
    {
        return $this->hzones;
    } 

    public function getIzones() 
    {
        return $this->izones;
    } 

    private function getVzone($zone) 
    {
        return $this->vzones[$zone];
    } 

    private function getHzone($zone) 
    {
        return $this->hzones[$zone];
    } 

    private function getIzone($zone) 
    {
        return $this->izones[$zone];
    } 

    // Setters
    public function setUser($user) 
    {
        $this->user = $user;
    } 

    public function setEye($eye) 
    {
        $this->eye = $eye;
    } 

    public function setTime($time=false) 
    {
        if($time === false) $time = date('Y-m-d H:i:s');
        $this->time = $time;
    } 

    public function setVzones($vzones) 
    {
        $this->vzones = $vzones;
    } 

    public function setHzones($hzones) 
    {
        $this->hzones = $hzones;
    } 

    public function setIzones($izones) 
    {
        $this->izones = $izones;
    } 

    /**
     * Loads a rating based on its id
     *
     * @param int $id   The id of the rating 
     *
     * @return object|false A rating object or false if the rating does not exist
     */
    public function load($id) 
    {
        $db = $this->container->get('db');
        $sql = "SELECT * from `ratings` WHERE `id` =".$db->quote($id);
        
        $result = $db->query($sql)->fetch(\PDO::FETCH_OBJ);
        $db = null;
        if(!$result) return false;
        else {
            $this->id = $result->id;
            $this->user = $result->user;
            $this->eye = $result->eye;
            $vzones = [];
            $hzones = [];
            $izones = [];
            for($i=1;$i<14;$i++) {
                $vzones[$i] = $result->{"v$i"};
                $hzones[$i] = $result->{"h$i"};
                $izones[$i] = $result->{"i$i"};
            }
            $this->vzones = $vzones;
            $this->hzones = $hzones;
            $this->izones = $izones;
        }
    }
   
    /**
     * Creates a new rating and stores it in the database
     *
     * @param int $user The ID of the user who created the rating
     * @param int $eye The ID of the eye that is rated
     *
     * @return int The id of the newly created eye
     */
    public function create($user, $eye) 
    {
        // Store in database
        $db = $this->container->get('db');
        $sql = "INSERT into `ratings`(
            `user`,
            `eye`,
            `time`
             ) VALUES (
             ".$db->quote($user).",
             ".$db->quote($eye).",
             ".$db->quote(date('Y-m-d H:i:s'))."
            );";
        $db->exec($sql);

        // Retrieve eye ID
        $id = $db->lastInsertId();

        // Update instance from database
        $this->load($id);

        return $id;
    }

    /** Saves the rating to the database */
    public function save() 
    {
        $db = $this->container->get('db');
        $sql = "UPDATE `ratings` set 
               `user` = ".$db->quote($this->getUser()).",
                `eye` = ".$db->quote($this->getEye()).",
                `time` = ".$db->quote($this->getTime());
        for($i=1;$i<14;$i++) {
            $sql .= ", `v$i` = ".$db->quote($this->getVzone($i))."\n";
            $sql .= ", `h$i` = ".$db->quote($this->getHzone($i))."\n";
            $sql .= ", `i$i` = ".$db->quote($this->getIzone($i))."\n";
        }
        $sql .= "
            WHERE 
                  `id` = ".$db->quote($this->getId());
        $result = $db->exec($sql);
        $db = null;

        return $result;
    }
    
    /** Removes the rating */
    public function remove() 
    {
        $db = $this->container->get('db');
        $sql = "
            DELETE from `ratings` WHERE `id` = ".$db->quote($this->getId()).";
        ";

        $result = $db->exec($sql);
        $db = null;

        return $result;
    }
}
