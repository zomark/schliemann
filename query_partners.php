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
			$sql = "select names.full_name, count(names.full_name) as cnt "
					."from names, items, places where ";
			$sql .= $db->whereName($_GET);
			
			$sql .= " group by names.full_name "
				."order by cnt desc, names.full_name";
			
//			error_log("query_partners: ".$sql);
			$result = $db->query($sql);
			if($result) {
				$i = 0;
				foreach ($result as $row) {
					if($row[0] != "Schliemann, Heinrich") {
						$data[$i++] = array(
							"label" => $row[0],
							"value" => $row[0],
							"count" => $row[1]
						);
					}
				}	
			}
			$result->closeCursor();
			echo json_encode($data);
		} 
	}
	catch(Exception $e) {
		echo json_encode(array("Error:", $e->getMessage()));
	}
	
?>