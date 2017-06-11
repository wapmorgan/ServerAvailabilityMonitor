<?php
namespace wapmorgan\ServerAvailabilityMonitor;

class MysqlServer extends BaseServer {
	const DEFAULT_PORT = '3306';
	public $username;
	public $password;

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
			})
		];
	}

	public function checkAvailability() {
		if (extension_loaded('pdo')) {
			return $this->checkPdo();
		} else if (extension_loaded('mysqli')) {
			return $this->checkMysqli();
		}
		return new \RuntimeException('No available mysql connectors found.');
	}

	protected function checkPdo() {
		try {
			$pdo = new \PDO('mysql:host='.$this->hostname.';port='.$this->port, $this->username, $this->password);
		} catch (\PDOException $e) {
			return new \RuntimeException($e->getMessage());
		}
		return true;
	}

	protected function checkMysqli() {
		$mysqli = new \mysqli($this->hostname, $this->username, $this->password, null, $this->port);
		if ($mysqli->connect_error) {
			return new \RuntimeException($mysqli->connect_error);
		}
		return true;
	}
}
