<?php
	require 'dbpdo.php';
	header('Content-Type: application/json');

	try {
		$db = new MyDB();
		if(!$db){
			echo $db->lastErrorMsg();
		} 
		else {
			//Build SELECT statement
			$data = array();
			$sql = "select places.full_name, "
				."count(places.full_name) as cnt "
				."from names s, names r, places, items where ";
			$sql .= $db->wherePlace($_GET);
			$sql .= " group by places.full_name "
				."order by cnt desc, places.full_name"; 
			
//			error_log("query_places: ".$sql);

			$result = $db->query($sql);
			if($result) {
				$i = 0;
				foreach($result as $row) {
					$data[$i++] = array(
						"label" => $row[0],
						"value" => $row[0],
						"count" => $row[1]
					);
				}	
				$result->closeCursor();
			} 
			else {
				$data[0] = "[No match]";
			} 
			echo json_encode($data);
		} 
	}
	catch(Exception $e) {
		echo json_encode(array("Error:", $e->getMessage()));
	}
	
?>