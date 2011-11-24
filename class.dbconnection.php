<?php
class DBConnection {
	private $_db;

	public function __construct($host, $db_name, $user, $pass) {
		try {
			$this->_db = new PDO('mysql:host='.$host.';dbname='.$db_name, $user, $pass);
		} catch (Exception $e) {
			$this->_db = null;		
		}
	}

	public function execute($sql, $args = null) {
		if ($this->_db == null) {
			return false;
		}
		$stmt = $this->_db->prepare($sql);
		$stmt->execute($args);
		if ($stmt->errorCode() != "00000") {
			return false;
			print_r($stmt->errorInfo());
			debug_print_backtrace();
			error_log(print_r($stmt->errorInfo(), true));
			$e = new Exception();
			error_log($e->getTraceAsString());
			exit;
		}
		return $stmt->fetchAll();
	}

	public function select($sql, $args = null) {
		return $this->execute($sql, $args);
	}

	public function selectRow($sql, $args = null, $ignore_errors = false) {
		$result = $this->execute($sql, $args);
		if (count($result) == 0) {
			if (!$ignore_errors) {
				error_log(print_r(debug_backtrace(), true));
			}
			return null;
		}
		return $result[0];
	}

	public function selectValue($sql, $args = null, $ignore_errors = false) {
		$result = $this->execute($sql, $args);
		if (count($result) == 0 || count($result[0]) == 0) {
			if (!$ignore_errors) {
				error_log(print_r(debug_backtrace(), true));
			}
			return null;
		}
		return $result[0][0];
	}

	public function simpleSelect($table, $filters) {
		$sql = "SELECT * FROM $table WHERE" . $this->keysToSql($filters, " AND");
		return $this->select($sql, $filters);
	}

	public function simpleSelectRow($table, $filters) {
		$sql = "SELECT * FROM $table WHERE" . $this->keysToSql($filters, " AND");
		return $this->selectRow($sql, $filters);
	}

	public function simpleSelectValue($var, $table, $filters) {
		$sql = "SELECT $var FROM $table WHERE" . $this->keysToSql($filters, " AND");
		return $this->selectValue($sql, $filters);
	}

	public function countRows($table, $filters) {
		return $this->simpleSelectValue("COUNT(*)", $table, $filters);
	}

	private function keysToSql($array, $seperator) {
		$list = array();
		foreach ($array as $key => $value) {
			$list[] = " $key = :$key";
		}
		return implode($seperator, $list);
	}

	public function update($table, $vars, $filters) {
		//TODO fix saa samme felt kan være i var og filter ved at tilf0je prefix til filter-keys
		$sql = "UPDATE $table SET" . $this->keysToSql($vars, ",") . " WHERE" . $this->keysToSql($filters, " AND");
		//print_r($sql);
		$this->execute($sql, array_merge($vars, $filters));
	}

	public function delete($table, $filters) {
		$sql = "DELETE FROM $table WHERE" . $this->keysToSql($filters, " AND");
		$this->execute($sql, $filters);
	}

	public function insert($table, $vars) {
		$this->createRow("INSERT", $table, $vars);
	}

	public function replace($table, $vars) {
		$this->createRow("REPLACE", $table, $vars);
	}

	private function createRow($type, $table, $vars) {
		$list = array();
		$colon_list = array();
		foreach ($vars as $key => $value) {
			$list[] = $key;
			$colon_list[] = ":$key";
		}
		$sql = "$type INTO $table(" . implode(", ", $list) . ") VALUES(" . implode(", ", $colon_list) . ")";
		$this->execute($sql, $vars);
	}

	public function lastInsertId($id) {
		return $this->_db->lastInsertId($id);
	}
}
?>
