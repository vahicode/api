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
    
    /** Nuxt middleware authentication */
    public function account($request, $response, $args)
    {
        // Get the token kit from the container
        $TokenKit = $this->container->get('TokenKit');

        // Get info from authentication middleware
        $id = $request->getAttribute("jwt")->user;
        $isAdmin = $request->getAttribute("jwt")->isAdmin;

        if($isAdmin) {
            // Get an admin instance from the container
            $admin = clone $this->container->get('Admin');
            $admin->loadFromId($id);

            if($admin->getId() === null || $admin->getId() === false) {
                return Utilities::prepResponse($response, [
                    'result' => 'error', 
                    'reason' => 'unknown_admin',
                ], 400, $this->container['settings']['app']['origin']);
            } 
            
            return Utilities::prepResponse($response, [
                'result' => 'ok', 
                'reason' => 'login_success', 
                'isAdmin' => true,
                'admin' => $admin->getId(),
                'superadmin' => $admin->isSuperAdmin(),
                'token' => $TokenKit->create($admin->getId(), true)
            ], 200, $this->container['settings']['app']['origin']);
        }
        
        // Get a user instance from the container
        $user = clone $this->container->get('User');
        $user->loadFromId($id);

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
        
        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'isAdmin' => false,
            'token' => $TokenKit->create($user->getId()),
            'id' => $user->getId(),
        ], 200, $this->container['settings']['app']['origin']);
    }

}
