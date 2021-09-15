<?php
// TODO php 8 -> types
class RedisConnection {
    private $redis_instance = null;
    private static $redis_connection_instance = null;

    public const DEFAULT_HOST = '127.0.0.1';
    public const DEFAULT_PORT = 6379;

    private $host, $port;
    private $username, $password;

    private function __construct($host = null, $port = null, $username = null, $password = null) {
        $this->host = $host ?? self::DEFAULT_HOST;
        $this->port = $host ?? self::DEFAULT_PORT;
        $this->username = $username;
        $this->password = $password;

        $this->redis_instance = new Redis();
        $this->init();
    }

    private function init() {
        $can_connect = $this->redis_instance->connect($this->host, $this->port);

        if (!$can_connect) {
            echo "Sorry, can't connect to Redis :("; // faire un système de gestion d'erreurs
            exit(0);
        }

        if ($this->username) {
            $this->redis_instance->auth([ $this->username, $this->password ]);
        }
    }

    public static function getInstance() {
        if (is_null(self::$redis_connection_instance)) {
            self::$redis_connection_instance = new RedisConnection();
        }

        return self::$redis_connection_instance;
    }

    public function getRedis() {
        return $this->redis_instance;
    }
}
