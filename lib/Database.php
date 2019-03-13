<?php

class Database extends Library {

	private $mysql;
	private $lastQuery = null;

	public function __construct($dbConfig) {
		parent::__construct($dbConfig);
		$this->getConnection();
	}

	public function getConnection() {
		if (is_null($this->mysql)) {
			$this->connect();
			$this->mysql->set_charset('utf8');
		}
		return $this->mysql;
	}

	public function prepare($query) {
		$this->lastQuery = $query;
		return $this->getConnection()->prepare($query);
	}

	public function getLastQuery() { return $this->lastQuery; }

	public function connect() {

		$this->mysql = new mysqli($this->config["host"], $this->config["user"], $this->config["password"], $this->config["dbname"]);
		if ($this->mysql->connect_errno) {
			echo "Failed to connect to MySQL: (" . $this->mysql->connect_errno . ") " . $this->mysql->connect_error;
		}
		else {
		//	echo("<!-- Connected to mySQL -->");
		}
		$this->mysql->set_charset('utf8');
		return $this->mysql;
	}

	public function escape($str) {
		return $this->mysql->real_escape_string($str);
	}

	public function close() {
		if (!is_null($this->mysql))
			@$this->mysql->close();
	}

	public function __destruct() {
		$this->close();
	}

	public function beginTransaction() {
		$this->startTransaction();
	}

	public function startTransaction() {
		$this->getConnection();
		$this->mysql->query("begin;") or die($this->mysql->error);
	}
	public function commitTransaction() {
		$this->getConnection();
		$this->mysql->query("commit;") or die($this->mysql->error);
	}
	public function rollbackTransaction() {
		$this->getConnection();
		$this->mysql->query("rollback;") or die ($this->mysql->error);
	}

}

?>
