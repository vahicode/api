<?php
/** EyeFu\Controllers\AdminController class */
namespace EyeFu\Controllers;

use \EyeFu\Objects\User as User;
use \EyeFu\Tools\Utilities as Utilities;

/**
 * Holds admin methods.
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2018 Joost De Cock
 * @license MIT
 */
class AdminController 
{
    protected $container;

    // constructor receives container instance
    public function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    public function init($request, $response, $args) 
    {
        $initUser = 'joost';
        $initPassword = 'test';

        // Get an admin instance from the container
        $admin = $this->container->get('Admin');
        
        // Check whether username if free among admins
        if(!$admin->usernameIsAvailable($initUser)) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'admin_exists', 
                ], 400, $this->container['settings']['app']['origin']);

        }

        $admin->create($initUser, $initPassword, 'superadmin');
        
        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'reason' => 'admin_created', 
            'id' => $admin->getId(),
            'username' => $admin->getUsername(),
            'role' => $admin->getRole(),
            'userid' => $admin->getUserid(),
        ], 200, $this->container['settings']['app']['origin']);

    }

    /** Admin login */
    public function login($request, $response, $args) {
        // Handle request data 
        $data = $request->getParsedBody();
        $login_data = [ 
            'username' => Utilities::scrub($request, 'username', 'string'), 
            'password' => Utilities::scrub($request, 'password')
        ];
        
        // Get an admin instance from the container
        $admin = clone $this->container->get('Admin');
        $admin->loadFromUsername($login_data['username']);
        if($admin->getId() == '') {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'login_failed', 
            ], 400, $this->container['settings']['app']['origin']);
        }
        
        if(substr($admin->getRole(), -5) !== 'admin') {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'account_blocked', 
            ], 400, $this->container['settings']['app']['origin']);
        }

        if(!$admin->checkPassword($login_data['password'])) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'login_failed', 
            ], 400, $this->container['settings']['app']['origin']);
        }

        // Get the token kit from the container
        $TokenKit = $this->container->get('TokenKit');
        
        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'reason' => 'login_success', 
            'token' => $TokenKit->create($admin->getId())
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Get admin profile */
    public function getProfile($request, $response, $args) 
    {
        // Get ID from authentication middleware
        $id = $request->getAttribute("jwt")->user;
        
        // Get an admin instance from the container
        $admin = clone $this->container->get('Admin');
        $admin->loadFromId($id);
        
        if($admin->getRole() === 'superadmin') $superadmin = true;
        else $superadmin = false;

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'id' => $admin->getUserid(),
            'adminid' => $admin->getId(),
            'username' => $admin->getUsername(),
            'role' => $admin->getRole(),
            'isAdmin' => true,
            'isSuperadmin' => $superadmin
        ], 200, $this->container['settings']['app']['origin']);
    }

    public function addAdmin($request, $response, $args) 
    {
        // Get ID from authentication middleware
        $id = $request->getAttribute("jwt")->user;
        $initUser = 'joost';
        
        // Get an admin instance from the container
        $admin = clone $this->container->get('Admin');
        $admin->loadFromId($id);
        
        if($admin->getRole() !== 'superadmin') {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);
        }

        $data = $request->getParsedBody();
        $username = Utilities::scrub($request, 'username', 'string');
        $password = Utilities::scrub($request, 'password');
        if(strlen($username) < 2 || strlen($password) < 5) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'too_short', 
            ], 400, $this->container['settings']['app']['origin']);
        }

        // Get an admin instance from the container
        $newAdmin = clone $this->container->get('Admin');
        
        // Check whether username if free among admins
        if(!$admin->usernameIsAvailable($username)) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'admin_exists', 
                ], 400, $this->container['settings']['app']['origin']);

        }

        $newAdmin->create($username, $password, 'admin');
        
        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'reason' => 'admin_created', 
            'id' => $newAdmin->getId(),
            'username' => $newAdmin->getUsername(),
            'role' => $newAdmin->getRole(),
            'userid' => $newAdmin->getUserid(),
        ], 200, $this->container['settings']['app']['origin']);

    }
}
