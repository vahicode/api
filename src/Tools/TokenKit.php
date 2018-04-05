<?php
/** EyeFu\Tools\TokenKit class */
namespace EyeFu\Tools;

use \Firebase\JWT\JWT;
use \Tuupola\Base62;

/**
 * The TokenKit class.
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2018 Joost De Cock
 * @license MIT
 */
class TokenKit 
{
    /** @var \Slim\Container $container The container instance */
    protected $container;

    // constructor receives container instance
    public function __construct(\Slim\Container $container) 
    {
        $this->container = $container;
    }
    
    /**
     * Returns a JWT
     *
     * @param string $type One of user,model,draft
     *
     * @return sring The token
     */
    public function create($userId) 
    {
        $now = new \DateTime();
        $exp = new \DateTime("now +".$this->container['settings']['jwt']['lifetime']);
        
        $base62 = new Base62();

        $secret = $this->container['settings']['jwt']['secret'];

        $payload = [
            'user' => $userId,
            'jti' => $base62->encode(random_bytes(16)),
            'iat' => $now->getTimeStamp(),
            'exp' => $exp->getTimeStamp()
        ];

        return JWT::encode($payload, $secret, "HS256");
    }
}
