<?php
/** Vahi\Controllers\RatingController class */
namespace Vahi\Controllers;

use \Vahi\Objects\User as User;
use \Vahi\Tools\Utilities as Utilities;

/**
 * Holds rating methods.
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2018 Joost De Cock
 * @license MIT
 */
class RatingController 
{
    protected $container;

    // constructor receives container instance
    public function __construct(\Slim\Container $container) 
    {
        $this->container = $container;
    }


    /** Retrieve the next eye to rate */
    public function next($request, $response, $args) 
    {
        // Get user from authentication middleware
        $user = clone $this->container->get('User');
        $user->loadFromId($request->getAttribute("jwt")->user);
        
        if($user->getId() == '') {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'invite_unknown', 
                'invite' => $invite,
            ], 400, $this->container['settings']['app']['origin']);
        }

        if(!$user->getActive()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'invite_inactive', 
            ], 400, $this->container['settings']['app']['origin']);
        }

        $eye = $this->loadNextEye($user->getId());
        if(!$eye) {
            return Utilities::prepResponse($response, [
                'result' => 'done',
                'reason' => 'all_eyes_rated', 
            ], 200, $this->container['settings']['app']['origin']);
        }

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'eye' => $eye,
        ], 200, $this->container['settings']['app']['origin']);
    }
    
    /** Loads the next eye to rate */
    private function loadNextEye($userid)
    {
        $db = $this->container->get('db');
         
        // All eyes 
        $sql = "SELECT `eyes`.`id` FROM `eyes` WHERE `eyes`.`active` = 1;";
        $eyes = [];
        foreach($db->query($sql)->fetchAll(\PDO::FETCH_ASSOC) as $eye) $eyes[$eye['id']] = $eye['id'];

        // Rated eyes
        $sql = "SELECT `ratings`.`eye` FROM `ratings` WHERE `ratings`.`user` = $userid";
        $ratings = [];
        foreach($db->query($sql)->fetchAll(\PDO::FETCH_ASSOC) as $rating) $ratings[$rating['eye']] = $rating['eye'];
        
        $total = count($eyes);
        $done = count($ratings); 
        foreach($ratings as $id) unset($eyes[$id]);

        if(count($eyes)<1) return false;
        else $eyeid = array_shift($eyes);
        $sql = "SELECT * FROM `pictures` 
            WHERE `pictures`.`eye` = $eyeid";
        $result = $db->query($sql)->fetchAll(\PDO::FETCH_OBJ);
        $db = null;
        
        $eye = new \stdClass();
        $eye->id = $eyeid;
        $eye->total = $total;
        $eye->done = $done;
        $eye->pictures = $result;

        return $eye;
    }


    public function addRating($request, $response, $args) 
    {
        $user = clone $this->container->get('User');
        $user->loadFromId($request->getAttribute("jwt")->user);

        if(!$user->getId()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);
        }

        $data = $request->getParsedBody();
        $rating = clone $this->container->get('Rating');
        $rating->create($user->getId(), $data['eye']); 
        
        $rating->setVzones($data['v']);
        $rating->setHzones($data['h']);
        $rating->setIzones($data['i']);
        $rating->save();

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
        ], 200, $this->container['settings']['app']['origin']);
    }

}
