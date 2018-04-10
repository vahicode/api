<?php
/** EyeFu\Objects\Picture class */
namespace EyeFu\Objects;

use Imagick;

/**
 * The picture class.
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2018 Joost De Cock
 * @license MIT
 */
class Picture 
{
    /** @var \Slim\Container $container The container instance */
    protected $container;

    /** @var int $id Unique id of the picture */
    private $id;

    /** @var string $filename The original filename */
    private $filename;

    /** @var string $hash The hash of the picture */
    private $hash;

    /** @var int $eye ID of the eye this picture is linked to */
    private $eye;

    /** @var int $height The height of the picture in pixels */
    private $height;

    /** @var int $width The width of the picture in pixels */
    private $width;

    /** @var float $scale The scale used by the grading overlay for this picture */
    private $scale;

    /** @var int $x The x anchor value used by the grading overlay for this picture */
    private $x;

    /** @var int $y The y anchor value used by the grading overlay for this picture */
    private $y;

    /** @var string $zones The zones that are to be rated in this picture */
    private $zones;

    /** @var int $admin The ID of the admin who added this picture */
    private $admin;

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

    public function getFilename() 
    {
        return $this->filename;
    } 

    public function getHash() 
    {
        return $this->hash;
    } 

    public function getEye() 
    {
        return $this->eye;
    } 

    public function getHeight() 
    {
        return $this->height;
    } 

    public function getWidth() 
    {
        return $this->width;
    } 

    public function getScale() 
    {
        return $this->scale;
    } 

    public function getX() 
    {
        return $this->x;
    } 

    public function getY() 
    {
        return $this->y;
    } 

    public function getZones() 
    {
        return $this->zones;
    } 

    public function getAdmin() 
    {
        return $this->admin;
    } 

    // Setters
    public function setEye($eye) 
    {
        $this->eye = $eye;
    } 

    public function setScale($scale) 
    {
        $this->scale = $scale;
    } 

    public function setX($x) 
    {
        $this->x = $x;
    } 

    public function setY($y) 
    {
        $this->y = $y;
    } 

    public function setZones($zones) 
    {
        $this->zones = $zones;
    } 

    /**
     * Creates a new picture and stores it in database & on disk
     *
     * @param string $upload The uploade picture
     *
     * @return int The id of the newly created picture
     */
    public function create($upload, $admin) 
    {
        // Imagick instance with the user's picture
        $imagick = new Imagick();
        $handle = fopen($upload->file, 'r');
        $imagick->readImageFile($handle);
        fclose($handle);
        
        // Keep size reasonable
        $max = $this->container['settings']['storage']['max_size'];
        if($imagick->getImageWidth() > $max || $imagick->getImageHeight() > $max) {
            $imagick->thumbnailImage($max, $max, TRUE);
            $imagick->writeImage($upload->file);
        }
        
        // Set basic info    
        $this->filename = $upload->getClientFilename();
        $this->hash = sha1(file_get_contents($upload->file));

        // Before we continue, do we already have this picture?
        $new = $this->hashIsAvailable($this->getHash());
        if($new !== true) return $new;
        
        $this->width = $imagick->getImageWidth();
        $this->height = $imagick->getImageHeight();
        $this->admin = $admin;

        // Store in database
        $db = $this->container->get('db');
        $sql = "INSERT into `pictures`(
            `filename`,
            `hash`,
            `width`,
            `height`,
            `scale`,
            `x`,
            `y`,
            `admin`
             ) VALUES (
            ".$db->quote($this->getFilename()).",
            ".$db->quote($this->getHash()).",
            ".$db->quote($this->getWidth()).",
            ".$db->quote($this->getHeight()).",
            '0.5',
            '0.25',
            '0.15',
            ".$db->quote($this->getAdmin())."
            );";
        $db->exec($sql);

        // Move uploaded file
        $cmd = 'mv '.$upload->file.' '.$this->container['settings']['storage']['dir'].'/'.$this->getHash().'.jpg';
        shell_exec($cmd);
        $imagick->clear();

        // Update instance from database
        $id = $db->lastInsertId();
        $this->loadFromId($id);

        return TRUE;
    }

    /** Saves the picture to the database */
    public function save() 
    {
        $zones = $this->getZones();
        if (is_array($zones)) {
            $zones = serialize($zones);
        }
        $db = $this->container->get('db');
        $sql = "UPDATE `pictures` set 
                 `eye` = ".$db->quote($this->getEye()).",
               `scale` = ".$db->quote($this->getScale()).",
                   `x` = ".$db->quote($this->getX()).",
                   `y` = ".$db->quote($this->getY()).",
               `zones` = ".$db->quote($zones)."
            WHERE 
                  `id` = ".$db->quote($this->getId());
        $result = $db->exec($sql);
        $db = null;

        return $result;
    }
    
    /**
     * Loads a picture based on a unique identifier
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
        $sql = "SELECT * from `pictures` WHERE `$key` =".$db->quote($value);
        
        $result = $db->query($sql)->fetch(\PDO::FETCH_OBJ);
        $db = null;
        if(!$result) return false;
        else {
            foreach($result as $key => $val) {
                if($key === 'zones') {
                    if($val === 'all') $this->zones = 'all';
                    else $this->zones = unserialize($val);
                } else {
                    $this->{$key} = $val;
                }
            } 
        }
    }
   
    /**
     * Loads a picture based on their id
     *
     * @param int $id
     *
     * @return object|false A plain user object or false if user does not exist
     */
    public function loadFromId($id) 
    {
        return $this->load($id, 'id');
    }
   
    /** Removes the picture */
    public function remove() 
    {
        $db = $this->container->get('db');
        $sql = "
            DELETE from `pictures` WHERE `id` = ".$db->quote($this->getId()).";
        ";

        $result = $db->exec($sql);
        $db = null;

        return $result;
    }
    
    /** 
     * Checks whether a hash is already taken
     *
     * @return bool true if it's free, false if not
     */
    private function hashIsAvailable($hash) 
    {
        $db = $this->container->get('db');
        $sql = 'SELECT `id` FROM `pictures` WHERE  `hash` = '.$db->quote($hash).' LIMIT 1';
        
        $result = $db->query($sql)->fetch(\PDO::FETCH_OBJ);
        $db = null;
    
        if ($result) return $result->id;
        else return true;
    }
}
