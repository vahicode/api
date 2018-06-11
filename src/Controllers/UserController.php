<?php
/** Vahi\Controllers\UserController class */
namespace Vahi\Controllers;

use \Vahi\Objects\User as User;
use \Vahi\Tools\Utilities as Utilities;

/**
 * Holds user methods.
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2018 Joost De Cock
 * @license MIT
 */
class UserController 
{
    protected $container;

    // constructor receives container instance
    public function __construct(\Slim\Container $container) {
        $this->container = $container;
    }


    /** User login */
    public function login($request, $response, $args) {
        // Handle request data 
        $invite =  Utilities::scrub($request, 'invite'); 
        
        // Get a user instance from the container
        $user = clone $this->container->get('User');
        $user->loadFromInvite($invite);
        
        if($user->getId() == '') {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'invite_invalid', 
                'invite' => $invite,
            ], 400, $this->container['settings']['app']['origin']);
        }

        if(!$user->getActive()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'invite_inactive', 
            ], 400, $this->container['settings']['app']['origin']);
        }

        // Log login
        $user->setLogin();
        $user->save();
        
        // Get the token kit from the container
        $TokenKit = $this->container->get('TokenKit');
        
        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'token' => $TokenKit->create($user->getId()),
            'id' => $user->getId(),
        ], 200, $this->container['settings']['app']['origin']);
    }
}
