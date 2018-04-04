<?php
/** EyeFu\Controllers\UserController class */
namespace EyeFu\Controllers;

use \EyeFu\Objects\User as User;

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

}
