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
        return;
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

    public function addAdmin($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        if(!$me->isSuperAdmin()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);
        }

        $username = Utilities::scrub($request, 'username', 'string');
        $password = Utilities::scrub($request, 'password');
        if(strlen($username) < 2 || strlen($password) < 5) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'too_short', 
            ], 400, $this->container['settings']['app']['origin']);
        }

        // Get an admin instance from the container
        $admin = clone $this->container->get('Admin');

        // Check whether username if free among admins
        if(!$me->usernameIsAvailable($username)) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'admin_exists', 
            ], 400, $this->container['settings']['app']['origin']);

        }

        $admin->create($username, $password, 'admin');

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'reason' => 'admin_created', 
            'id' => $admin->getId(),
            'username' => $admin->getUsername(),
            'role' => $admin->getRole(),
            'userid' => $admin->getUserid()
        ], 200, $this->container['settings']['app']['origin']);
    }

    public function addUsers($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        if(!$me->isAdmin()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);
        }

        $count = (int) Utilities::scrub($request, 'count', 'integer');
        $notes = Utilities::scrub($request, 'notes');
        
        if(!is_int($count)) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'count_is_no_integer', 
            ], 400, $this->container['settings']['app']['origin']);
        }

        for($i=1; $i<=$count; $i++) {
            $user = clone $this->container->get('User');
            $user->create($notes, $me->getId());
            unset($user); 
        }

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
        ], 200, $this->container['settings']['app']['origin']);
    }

    public function addPictures($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        if(!$me->isAdmin()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);
        }

        foreach($request->getUploadedFiles() as $uploadedFile){
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) { 
                $pic = clone $this->container->get('Picture');
                $isNew = $pic->create($uploadedFile, $me->getId());
                if($isNew !== TRUE) {
                    return Utilities::prepResponse($response, [
                        'result' => 'error', 
                        'file' => $uploadedFile->getClientFilename(),
                        'exists' => $isNew 
                    ], 400, $this->container['settings']['app']['origin']);
                    
                }
                unset($pic);
           }
        }

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Load admin profile */
    public function getProfile($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'id' => $me->getUserid(),
            'adminid' => $me->getId(),
            'username' => $me->getUsername(),
            'role' => $me->getRole(),
            'isAdmin' => $me->isAdmin(),
            'isSuperadmin' => $me->isSuperAdmin()
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Load admin account */
    public function loadAdmin($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        if(!$me->isAdmin()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);

        }

        // Request data
        $id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
        $admin = clone $this->container->get('Admin');
        $admin->loadFromId($id);

        $user = clone $this->container->get('User');
        $user->loadFromId($admin->getUserid());

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'id' => $admin->getUserid(),
            'adminid' => $admin->getId(),
            'username' => $admin->getUsername(),
            'role' => $admin->getRole(),
            'isAdmin' => $admin->isAdmin(),
            'isSuperadmin' => $admin->isSuperAdmin(),
            'invite' => $user->getInvite(),
            'notes' => $user->getNotes()
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Load user account */
    public function loadUser($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        if(!$me->isAdmin()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);

        }

        // Request data
        $id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
        $user = clone $this->container->get('User');
        $user->loadFromId($id);

        $admin = clone $this->container->get('Admin');
        $admin->loadFromId($user->getAdmin());

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'id' => $user->getId(),
            'invite' => $user->getInvite(),
            'notes' => $user->getNotes(),
            'admin' => $user->getAdmin(),
            'adminUsername' => $admin->getUsername()
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Load eye data */
    public function loadEye($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        if(!$me->isAdmin()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);

        }

        // Request data
        $id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
        $eye = clone $this->container->get('Eye');
        $eye->load($id);
        
        $pictures = $this->loadEyePictures($id);

        $admin = clone $this->container->get('Admin');
        $admin->loadFromId($eye->getAdmin());

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'id' => $eye->getId(),
            'notes' => $eye->getNotes(),
            'admin' => $eye->getAdmin(),
            'adminUsername' => $admin->getUsername(),
            'active' => $eye->isActive(),
            'pictureCount' => count($pictures),
            'pictures' => $pictures
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Update admin account */
    public function updateAdmin($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        if(!$me->isSuperAdmin()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);

        }

        // Request data
        $id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
        $admin = clone $this->container->get('Admin');
        $admin->loadFromId($id);
        $data = [ 
            'username' => Utilities::scrub($request, 'username'), 
            'role' => Utilities::scrub($request, 'role'),
            'password' => Utilities::scrub($request, 'password')
        ];

        
        if($admin->usernameIsAvailable($data['username'])) {
            $admin->setUsername($data['username']);
        }
        if(strlen($data['password']) > 4) {
            $admin->setPassword($data['password']);
        }
        $admin->setRole($data['role']);
        $admin->save();

        return Utilities::prepResponse($response, [
            'result' => 'ok'
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Update user account */
    public function updateUser($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        if(!$me->isAdmin()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);

        }

        // Request data
        $id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
        $user = clone $this->container->get('User');
        $user->loadFromId($id);
        $data = [ 
            'invite' => Utilities::scrub($request, 'invite'), 
            'notes' => Utilities::scrub($request, 'notes')
        ];

        $user->setInvite($data['invite']);
        $user->setNotes($data['notes']);
        $user->save();

        return Utilities::prepResponse($response, [
            'result' => 'ok'
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Remove admin account */
    public function removeAdmin($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        if(!$me->isSuperAdmin()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);

        }

        // Request data
        $id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
        $admin = clone $this->container->get('Admin');
        $admin->loadFromId($id);
        $admin->remove();
        
        return Utilities::prepResponse($response, [
            'result' => 'ok'
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Remove user account */
    public function removeUser($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        if(!$me->isAdmin()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);

        }

        // Request data
        $id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
        $user = clone $this->container->get('User');
        $user->loadFromId($id);
        $user->remove();
        
        return Utilities::prepResponse($response, [
            'result' => 'ok'
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Get admin list */
    public function getAdminList($request, $response, $args) 
    {
        $admins = $this->loadAdmins(100);

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'count' => count($admins),
            'admins' => $admins,
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Get user list */
    public function getUserList($request, $response, $args) 
    {
        $users = $this->loadUsers(100);

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'count' => count($users),
            'users' => $users,
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Get eye list */
    public function getEyeList($request, $response, $args) 
    {
        $eyes = $this->loadEyes(100);
        foreach($eyes as $id => $eye) {
            $eye->pictures = $this->loadEyePictures($id);
            $eye->foo = bar;
            $eyes->{$id} = $eye;
        }

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'count' => count($eyes),
            'eyes' => $eyes,
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Get orphan pictures list */
    public function getOrphanPicturesList($request, $response, $args) 
    {
        $pics = $this->loadOrphanPictures(100);

        return Utilities::prepResponse($response, [
            'result' => 'ok', 
            'count' => count($pics),
            'pictures' => $pics,
        ], 200, $this->container['settings']['app']['origin']);
    }


    /** Bundle pictures to create eye */
    public function eyeFromPictureBundle($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        if(!$me->isAdmin()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);

        }

        $eye = clone $this->container->get('Eye');
        $eyeid = $eye->create($me->getId());
        foreach($request->getParsedBody()['pictures'] as $picid) {
          $picture = clone $this->container->get('Picture');
          $picture->loadFromId($picid);
          $picture->setEye($eyeid);
          $picture->save();
          unset($picture);
        }

        return Utilities::prepResponse($response, [
            'result' => 'ok'
        ], 200, $this->container['settings']['app']['origin']);
    }

    /** Add eyes for all orphan pictures */
    public function eyesFromOrphanPictures($request, $response, $args) 
    {
        $me = $this->loadMe($request);

        if(!$me->isAdmin()) {
            return Utilities::prepResponse($response, [
                'result' => 'error', 
                'reason' => 'access_denied', 
            ], 400, $this->container['settings']['app']['origin']);

        }

        foreach($request->getParsedBody()['pictures'] as $picid) {
          $eye = clone $this->container->get('Eye');
          $eyeid = $eye->create($me->getId());
          $picture = clone $this->container->get('Picture');
          $picture->loadFromId($picid);
          $picture->setEye($eyeid);
          $picture->save();
          unset($eye, $picture);
        }

        return Utilities::prepResponse($response, [
            'result' => 'ok'
        ], 200, $this->container['settings']['app']['origin']);
    }

    private function loadAdmins($count)
    {
        if(!is_numeric($count)) $count = 25;
        if($count > 100) $count = 100;

        $db = $this->container->get('db');
        $sql = "SELECT  *
            from `admins` 
            ORDER BY `admins`.`id` DESC LIMIT $count";
        $result = $db->query($sql)->fetchAll(\PDO::FETCH_OBJ);
        $db = null;

        if(!$result) return false;
        else {
            foreach($result as $key => $val) {
                $admins[$val->id] = $val;
            }
        } 

        return $admins;
    }

    private function loadUsers($count)
    {
        if(!is_numeric($count)) $count = 25;
        if($count > 100) $count = 100;

        $db = $this->container->get('db');
        $sql = "SELECT * from `users` 
            ORDER BY `users`.`id` DESC LIMIT $count";
        $result = $db->query($sql)->fetchAll(\PDO::FETCH_OBJ);
        $db = null;

        if(!$result) return false;
        else {
            foreach($result as $key => $val) {
                $users[$val->id] = $val;
            }
        } 

        return $users;
    }

    private function loadEyes($count)
    {
        if(!is_numeric($count)) $count = 100;
        if($count > 100) $count = 100;

        $db = $this->container->get('db');
        $sql = "SELECT * from `eyes` 
            ORDER BY `eyes`.`id` DESC LIMIT $count";
        $result = $db->query($sql)->fetchAll(\PDO::FETCH_OBJ);
        $db = null;

        if(!$result) return false;
        else {
            foreach($result as $key => $val) {
                $eyes[$val->id] = $val;
            }
        } 

        return $eyes;
    }

    private function loadEyePictures($id)
    {
        $db = $this->container->get('db');
        $sql = "SELECT * from `pictures` 
            WHERE `pictures`.`eye` = '$id'
            ORDER BY `pictures`.`id` DESC";
        $result = $db->query($sql)->fetchAll(\PDO::FETCH_OBJ);
        $db = null;

        if(!$result) return false;
        else {
            foreach($result as $key => $val) {
                $pictures[$val->id] = $val;
            }
        } 

        return $pictures;
    }

    private function loadOrphanPictures($count)
    {
        if(!is_numeric($count)) $count = 100;
        if($count > 100) $count = 100;

        $db = $this->container->get('db');
        $sql = "SELECT `pictures`.*, `admins`.`username` FROM `pictures`,`admins` 
            WHERE `admins`.`id` = `pictures`.`admin` AND `pictures`.`eye` IS NULL 
            ORDER BY `pictures`.`id` DESC LIMIT $count";
        $result = $db->query($sql)->fetchAll(\PDO::FETCH_OBJ);
        $db = null;

        if(!$result) return false;
        else {
            foreach($result as $key => $val) {
                $pics[$val->id] = $val;
            }
        } 

        return $pics;
    }

    private function loadMe($request)
    {
        $me = clone $this->container->get('Admin');
        $me->loadFromId($request->getAttribute("jwt")->user);

        return $me;
    }
}
