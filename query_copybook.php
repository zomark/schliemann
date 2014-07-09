<?php
	require 'dbpdo.php';
	header('Content-Type: text/html');

	$sql = "";
	
	try {
		$db = new MyDB();
		if(!$db){
			echo $db->lastErrorMsg();
		} 
		else {
			//Build SELECT statement
			$data = array();
			
			$sql = "select distinct copybook from items, names, places ";
			$sql .= "where ".$db->whereCopybook($_GET);
			$sql .= " order by items.copybook";
			
			// $datesConstrained = (isset($_GET["from"]) && $_GET["from"] != "")
				// || (isset($_GET["to"]) && $_GET["to"] != "");
			// $typeConstrained = isset($_GET["type"]) && $_GET["from"] != "both";
			// $nameConstrained = isset($_GET["term"]) && $_GET["term"] != "";
			// $placeConstrained = isset($_GET["place"]) && $_GET["place"] != "";
			
			// if($datesConstrained || $typeConstrained || $nameConstrained || $placeConstrained) {
				// $sql = "select distinct copybook "
					// ."from names, places, items where ";

				// //Dates
				// $constraint = "";
				// if(isset($_GET["from"]) && $_GET["from"] != "") {
					// $constraint .= "and items.date_sort >= '".$_GET["from"]."' ";
				// }
				// if(isset($_GET["to"]) && $_GET["to"] != "") {
					// $constraint .= "and items.date_sort <= '".$_GET["to"]."' ";
				// }
				
				// //Type
				// switch($_GET["type"]) {
				// case "out":
					// $sql .= "items.type = 'out' and ";
					// break;
				// case "in":
					// $sql .= "items.type <> 'out' and ";
					// break;
				// }
				
				// //Name
				// if($nameConstrained) {
					// if(strpos($_GET["term"], "*")) {
						// $term = str_replace("*", "%", $_GET["term"]);
						// $sql .= "names.full_name like '${term}' and ";
					// }
					// else {
						// $sql .= "names.full_name like '".$_GET["term"]."%' and ";
					// }
				// }

				// //Place
				// if($placeConstrained) {
					// if(strpos($_GET["place"], "*")) {
						// $place = str_replace("*", "%", $_GET["place"]);
						// $sql .= "places.full_name like '${place}' and ";
					// }
					// else {
						// $sql .= "places.full_name like '".$_GET["place"]."%' and ";
					// }
				// }
				
				// $sql .=	"not items.copybook is null "
					// ."and places.id = items.place_id and names.id in (items.sender_id, items.receiver_id) "
					// .$constraint." "
					// ."order by items.copybook"; 
			// }
			// else {
				// $sql = "select distinct copybook from items ";
				// $sql .= "order by items.copybook";
			// }

			error_log("query_copybook: ".$sql);
			echo "<option value='0'>Any</option>";
			$result = $db->query($sql);
			if(isset($result)) {
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