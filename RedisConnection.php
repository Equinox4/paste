<?php
declare(strict_types = 1);

class RedisConnection {
	private Redis $redis;
	private static RedisConnection $redis_connection;

	public const DEFAULT_HOST = '127.0.0.1';
	public const DEFAULT_PORT = 6379;

	private function __construct(private ?string $host = null, private ?int $port = null) {
		$this->host = $this->host ?? self::DEFAULT_HOST;
		$this->port = $this->port ?? self::DEFAULT_PORT;

		$this->redis = new Redis();
	}

	public static function getInstance(?string $host = null, ?int $port = null): RedisConnection {
		if (!isset(self::$redis_connection)) {
			self::$redis_connection = new RedisConnection($host, $port);
		}

		return self::$redis_connection;
	}

	public function getRedis(): Redis {
		return $this->redis;
	}

	public function setCredentials(string $username, ?string $password): void {
		$this->redis->auth([ $username, $password ]);
	}

	public function connect(): Redis {
		$can_connect = $this->redis->connect($this->host, $this->port);

		if (!$can_connect) {
			echo 'Can\'t connect to redis.';
			exit(0);
		}

		return $this->redis;
	}
}
