<?php
	class MyDB extends SQLite3 {
		function __construct() {
			$this->open('db/schliemann.db', SQLITE3_OPEN_READONLY);
		}
	}
?>