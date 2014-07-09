<?php
	require 'dbpdo.php';
	header('Content-Type: application/json');
	try {
		$db = new MyDB();
		if(!$db){
			echo "<p>Error</p>";

			echo $db->lastErrorMsg();
		} 
		else {
			//Build SELECT statement
			$sql = "select count(*) "
					."from names s, names r, items, places";
			$where = $db->where($_GET);
			if($where != "") {
				$sql .= " where ".$where;
			}
			
			//Run query
//			error_log("count_items: ".$sql);
			
			$result = $db->query($sql);
			if($result) {
				foreach ($result as $row) {
					echo json_encode($row[0]);
				} 
			}
			$result->closeCursor();
			
		} 
	}
	catch(Exception $e) {
		echo json_encode(0);
	}
?>