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
}
