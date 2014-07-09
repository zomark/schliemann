<?php
	require 'dbpdo.php';
	require 'authenticate.php';
	$user = sc_get_user($_SERVER);
	if(!$user) {
		die("Authentication failed");
	}
	header('Content-Type: text/html');

	try {
		$db = new MyDB();
		if(!$db){
			echo $db->lastErrorMsg();
		} 
		else {
			//Build SELECT statement
			$data = array();
			
			$sql = "select distinct box from items, names, places ";
			$sql .= "where ".$db->whereBox($_GET);
			$sql .= " order by items.box";
//			error_log("query_copybook: ".$sql);

			echo "<option value='0'>Any</option>";
			$result = $db->query($sql);
			if($result) {
				foreach($result as $row) {
					echo "<option value='$row[0]'>$row[0]</option>";
				}	
				$result->closeCursor();
			} 
		} 
	}
	catch(Exception $e) {
		echo "<option value='-1'>"."Error: ".$e->getMessage()."</option>"
			."<option value='-2'>".$sql."</option>";
	}
	
?>