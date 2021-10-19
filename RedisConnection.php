<?php
declare(strict_types = 1);

class RedisConnection {
    private Redis $redis_instance;
    private static RedisConnection $redis_connection_instance;

    public const DEFAULT_HOST = '127.0.0.1';
    public const DEFAULT_PORT = 6379;

    private function __construct(
        private ?string $host,
        private ?int $port,
        private ?string $username,
        private ?string $password,
    ) {
        $this->host = $this->host ?? self::DEFAULT_HOST;
        $this->port = $this->port ?? self::DEFAULT_PORT;

        $this->redis_instance = new Redis();
        $this->init();
    }

    private function init(): void {
        $can_connect = $this->redis_instance->connect($this->host, $this->port);

        if (!$can_connect) {
            echo 'Sorry, can\'t connect to Redis :('; // faire un système de gestion d'erreurs
            exit(0);
        }

        if ($this->username) {
            $this->redis_instance->auth([ $this->username, $this->password ]);
        }
    }

    public static function getInstance(string $host = null, int $port = null, string $username = null, string $password = null): RedisConnection {
        if (!isset(self::$redis_connection_instance)) {
            self::$redis_connection_instance = new RedisConnection($host, $port, $username, $password);
        }

        return self::$redis_connection_instance;
    }

    public function getRedis(): Redis {
        return $this->redis_instance;
    }
}
