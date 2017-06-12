<?php
namespace wapmorgan\ServerAvailabilityMonitor;

class PostgreSqlServer extends BaseServer {
	const DEFAULT_PORT = '5432';
	public $username;
	public $password;
	public $database;

	public function getRules() {
		return parent::getRules() + [
			'username' => array('required', 'question', 'Username to access DB: ', null, function ($value) {
				$value = trim($value);
				if (empty($value))
					throw new \RuntimeException('A valid username should be a string');
				return $value;
			}),
			'password' => array('required', 'question', 'Password for username to access DB: ', null, function ($value) {
				$value = trim($value);
				if (empty($value))
					throw new \RuntimeException('A valid password should be a string');
				return $value;
			}),
			'database' => array('optional', 'question', 'If you need to connect to a database with a name different from username, specify name of database: ', null, function ($value) {
				$value = trim($value);
				if (empty($value))
					throw new \RuntimeException('A valid database should be a string');
				return $value;
			})
		];
	}

	public function checkAvailability() {
		if (extension_loaded('pdo')) {
			return $this->checkPdo();
		} else if (extension_loaded('pgsql')) {
			return $this->checkPgsql();
		}
		return new \RuntimeException('No available mysql connectors found.');
	}

	protected function checkPdo() {
		try {
			$pdo = new \PDO('pgsql:host='.$this->hostname.';port='.$this->port.';user='.$this->username.';password='.$this->password.(!empty($this->database) ? ';dbname='.$this->database : null));
		} catch (\PDOException $e) {
			return new \RuntimeException($e->getMessage());
		}
		return true;
	}

	protected function checkPgsql() {
		$result = pg_connect('host='.$this->hostname.' port='.$this->port.' user='.$this->username.' password='.$this->password.(!empty($this->database) ? ' dbname='.$this->database : null));
		if ($result === false) return new \RuntimeException('Memcache server is not available');
		return true;
	}
}
