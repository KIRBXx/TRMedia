<?php
class ICDB {
	var $server = "";
	var $db = "";
	var $user = "";
	var $password = "";
	var $prefix = "";
	var $insert_id;
	
	var $link;

	function __construct($_server, $_db, $_user, $_password, $_prefix) {
		$this->server = $_server;
		$this->db = $_db;
		$this->user = $_user;
		$this->password = $_password;
		$this->prefix = $_prefix;
		$this->link = mysql_connect($this->server, $this->user, $this->password) or die("Could not connect: " . mysql_error());
		mysql_select_db($this->db, $this->link) or die ('Can\'t use database : ' . mysql_error());
	}
	
	function get_row($_sql) {
		$result = mysql_query($_sql) or die("Invalid query: " . mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		mysql_free_result($result);
		return $row;
	}
	
	function get_rows($_sql) {
		$rows = array();
		$result = mysql_query($_sql) or die("Invalid query: " . mysql_error());
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$rows[] = $row;
		}
		mysql_free_result($result);
		return $rows;
	}

	function get_var($_sql) {
		$result = mysql_query($_sql) or die("Invalid query: " . mysql_error());
		$row = mysql_fetch_array($result, MYSQL_NUM);
		mysql_free_result($result);
		if ($row && is_array($row)) return $row[0];
		return false;
	}
	
	function query($_sql) {
		$result = mysql_query($_sql) or die("Invalid query: " . mysql_error());
		$this->insert_id = mysql_insert_id();
		return $result;
	}

}
?>