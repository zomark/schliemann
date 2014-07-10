<?php
	$dbtype = "sqlite";
	$dbparams = "db/schliemann.db";
	$dbuser = null;
	$dbpwd = null;
	
	//Encapsulated database connection. Uses persistent connections, if possible.
	class MyDB extends PDO {
		function __construct() {
			global $dbtype, $dbparams,	$dbuser, $dbpwd;
			parent::__construct("$dbtype:$dbparams", $dbuser, $dbpwd, array(
				PDO::ATTR_PERSISTENT => true
			));
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		
		//Assemble a WHERE clause from the current $_GET parameters
		function where($params) {
			$clauses = array();
			
			$datesConstrained = 
				(isset($params["from"]) && $params["from"] != "")
				|| (isset($params["to"]) && $params["to"] != "");
			$typeConstrained = isset($params["type"]) && $params["from"] != "both";
			$cbConstrained = isset($params["copybook"]) && $params["copybook"] != "" && $params["copybook"] != "0";
			$boxConstrained = isset($params["box"]) && $params["box"] != "" && $params["box"] != "0";
			$nameConstrained = isset($params["term"]) && $params["term"] != "" && $params["term"] != "*"; 
			$placeConstrained = isset($params["place"]) && $params["place"] != "" && $params["place"] != "*";
			
			//Dates
			if(isset($params["from"]) && $params["from"] != "") {
				$clauses[] = "items.date_sort >= '{$params['from']}'";
			}
			if(isset($params["to"]) && $params["to"] != "") {
				$clauses[] = "items.date_sort <= '{$params['to']}'";
			}
			
			//Type
			switch($params["type"]) {
			case "out":
				$clauses[] = "items.type = 'out'";
				break;
			case "in":
				$clauses[] = "items.type <> 'out'";
				break;
			}
			
			//Copybook
			if($cbConstrained) {
				$clauses[] = "items.copybook = {$params['copybook']}";
			}
			//Box
			if($boxConstrained) {
				$clauses[] = "items.box = {$params['box']}";
			}
			
			//Place
			if($placeConstrained) {
				if(strpos($params["place"], "*")) {
					$place = str_replace("*", "%", $params["place"]);
					$clauses[] = "places.full_name like '$place'";
				}
				else {
					$clauses[] = "places.full_name = '{$params['place']}'";	
				}
			}
			
			//Name
			if($nameConstrained) {
				$term = str_replace("*", "%", $params["term"]);
				$c = strpos($term, "%") ? "like '$term'" : "= '$term'";
				switch($params["type"]) {
					case "out":
						$clauses[] = "items.type = 'out'";
						$clauses[] = "r.full_name $c";
					break;
					case "in":
						$clauses[] = "items.type <> 'out'";
						$clauses[] = "s.full_name $c";
						break;
					default:
						$clauses[] = "(r.full_name $c or s.full_name $c)";
				}
			}
			
			//Media
			if(isset($params["withMedia"]) && $params["withMedia"] == "true") {
				$clauses[] = "items.media_count > 0";
			}
			
			//Joins
			$clauses[] = "s.id = items.sender_id";
			$clauses[] = "r.id = items.receiver_id";
			$clauses[] = "places.id = items.place_id";

			//error_log(print_r($clauses, true));
				
			//Assemble
			$retval = implode(" and ", $clauses);
			//error_log("Returning $retval");
			return $retval;
		}

		//Assemble a WHERE clause from the current $_GET parameters
		function whereName($params) {
			$clauses = array();
			
			$datesConstrained = 
				(isset($params["from"]) && $params["from"] != "")
				|| (isset($params["to"]) && $params["to"] != "");
			$typeConstrained = isset($params["type"]) && $params["from"] != "both";
			$cbConstrained = isset($params["copybook"]) && $params["copybook"] != "" && $params["copybook"] != "0";
			$boxConstrained = isset($params["box"]) && $params["box"] != "" && $params["box"] != "0";
			$nameConstrained = isset($params["term"]) && $params["term"] != "" && $params["term"] != "*"; 
			$placeConstrained = isset($params["place"]) && $params["place"] != "" && $params["place"] != "*";
			
			//Dates
			if(isset($params["from"]) && $params["from"] != "") {
				$clauses[] = "items.date_sort >= '{$params['from']}'";
			}
			if(isset($params["to"]) && $params["to"] != "") {
				$clauses[] = "items.date_sort <= '{$params['to']}'";
			}
			
			//Type
			switch($params["type"]) {
			case "out":
				$clauses[] = "items.type = 'out'";
				break;
			case "in":
				$clauses[] = "items.type <> 'out'";
				break;
			}
			
			//Copybook
			if($cbConstrained) {
				$clauses[] = "items.copybook = {$params['copybook']}";
			}
			//Box
			if($boxConstrained) {
				$clauses[] = "items.box = {$params['box']}";
			}
			
			//Place
			if($placeConstrained) {
				if(strpos($params["place"], "*")) {
					$place = str_replace("*", "%", $params["place"]);
					$clauses[] = "places.full_name like '$place'";
				}
				else {
					$clauses[] = "places.full_name = '{$params['place']}'";	
				}
			}
			
			//Name
			if($nameConstrained) {
				$term = str_replace("*", "%", $params["term"]);
				if(substr($term, -1) != "%") {
					$term .= "%";
				}
				$clauses[] = "names.full_name like '$term'";
			}
			
			//Media
			if(isset($params["withMedia"]) && $params["withMedia"] == "true") {
				$clauses[] = "items.media_count > 0";
			}
			
			//Joins
			$clauses[] = "names.id in (items.sender_id, items.receiver_id)";
			$clauses[] = "places.id = items.place_id";

			//Assemble
			$retval = implode(" and ", $clauses);
			return $retval;
		}

		//Assemble a WHERE clause from the current $_GET parameters
		function wherePlace($params) {
			$clauses = array();
			
			$datesConstrained = 
				(isset($params["from"]) && $params["from"] != "")
				|| (isset($params["to"]) && $params["to"] != "");
			$typeConstrained = isset($params["type"]) && $params["from"] != "both";
			$cbConstrained = isset($params["copybook"]) && $params["copybook"] != "" && $params["copybook"] != "0";
			$boxConstrained = isset($params["box"]) && $params["box"] != "" && $params["box"] != "0";
			$nameConstrained = isset($params["term"]) && $params["term"] != "" && $params["term"] != "*"; 
			$placeConstrained = isset($params["place"]) && $params["place"] != "" && $params["place"] != "*";
			
			//Dates
			if(isset($params["from"]) && $params["from"] != "") {
				$clauses[] = "items.date_sort >= '{$params['from']}'";
			}
			if(isset($params["to"]) && $params["to"] != "") {
				$clauses[] = "items.date_sort <= '{$params['to']}'";
			}
			
			//Type
			switch($params["type"]) {
			case "out":
				$clauses[] = "items.type = 'out'";
				break;
			case "in":
				$clauses[] = "items.type <> 'out'";
				break;
			}
			
			//Copybook
			if($cbConstrained) {
				$clauses[] = "items.copybook = {$params['copybook']}";
			}
			//Box
			if($boxConstrained) {
				$clauses[] = "items.box = {$params['box']}";
			}
			
			//Name
			if($nameConstrained) {
				$term = str_replace("*", "%", $params["term"]);
				$c = strpos($term, "%") ? "like '$term'" : "= '$term'";
				switch($params["type"]) {
					case "out":
						$clauses[] = "items.type = 'out'";
						$clauses[] = "r.full_name $c";
					break;
					case "in":
						$clauses[] = "items.type <> 'out'";
						$clauses[] = "s.full_name $c";
						break;
					default:
						$clauses[] = "(r.full_name $c or s.full_name $c)";
				}
			}
			
			//Media
			if(isset($params["withMedia"]) && $params["withMedia"] == "true") {
				$clauses[] = "items.media_count > 0";
			}
			
			//Place
			if($placeConstrained) {
				$place = str_replace("*", "%", $params["place"]);
				if(substr($place, -1) != "%") {
					$place .= "%";
				}
				$clauses[] = "places.full_name like '$place'";
			}
			
			//Joins
			$clauses[] = "s.id = items.sender_id";
			$clauses[] = "r.id = items.receiver_id";
			$clauses[] = "places.id = items.place_id";

			//Assemble
			$retval = implode(" and ", $clauses);
			return $retval;
		}
		
		//Assemble a copybook WHERE clause from the current $_GET parameters
		function whereCopybook($params) {
			$datesConstrained = (isset($params["from"]) && $params["from"] != "")
				|| (isset($params["to"]) && $params["to"] != "");
			$typeConstrained = isset($params["type"]) && $params["from"] != "both";
			$nameConstrained = isset($params["term"]) && $params["term"] != "";
			$placeConstrained = isset($params["place"]) && $params["place"] != "";
			
			$clauses = array();
			
			//Constraints
			if($datesConstrained || $typeConstrained || $nameConstrained || $placeConstrained) {

				//Dates
				if(isset($params["from"]) && $params["from"] != "") {
					$clauses[] = "items.date_sort >= '{$params['from']}'";
				}
				if(isset($params["to"]) && $params["to"] != "") {
					$clauses[] = "items.date_sort <= '{$params['to']}'";
				}
				
				//Type
				switch($params["type"]) {
				case "out":
					$clauses[] = "items.type = 'out'";
					break;
				case "in":
					$clauses[] = "items.type <> 'out'";
					break;
				}
				
				//Name
				if($nameConstrained) {
					if(strpos($params["term"], "*")) {
						$term = str_replace("*", "%", $params["term"]);
						$clauses[] = "names.full_name like '${term}'";
					}
					else {
						$clauses[] = "names.full_name = '{$params['term']}'";
					}
				}

				//Place
				if($placeConstrained) {
					if(strpos($params["place"], "*")) {
						$place = str_replace("*", "%", $params["place"]);
						$clauses[] = "places.full_name like '${place}'";
					}
					else {
						$clauses[] = "places.full_name = '{$params['place']}'";
					}
				}
				
				//Media
				if(isset($params["withMedia"]) && $params["withMedia"] == "true") {
					$clauses[] = "items.media_count > 0";
				}

			}
				
			//Joins
			$clauses[] = "not items.copybook is null";
			$clauses[] = "places.id = items.place_id";
			$clauses[] = "names.id in (items.sender_id, items.receiver_id)";
			
			//Assemble
			$retval = implode(" and ", $clauses);
			return $retval;

		}

		//Assemble a box WHERE clause from the current $_GET parameters
		function whereBox($params) {
			$datesConstrained = (isset($params["from"]) && $params["from"] != "")
				|| (isset($params["to"]) && $params["to"] != "");
			$typeConstrained = isset($params["type"]) && $params["from"] != "both";
			$nameConstrained = isset($params["term"]) && $params["term"] != "";
			$placeConstrained = isset($params["place"]) && $params["place"] != "";
			
			$clauses = array();
			
			//Constraints
			if($datesConstrained || $typeConstrained || $nameConstrained || $placeConstrained) {

				//Dates
				if(isset($params["from"]) && $params["from"] != "") {
					$clauses[] = "items.date_sort >= '{$params['from']}'";
				}
				if(isset($params["to"]) && $params["to"] != "") {
					$clauses[] = "items.date_sort <= '{$params['to']}'";
				}
				
				//Type
				switch($params["type"]) {
				case "out":
					$clauses[] = "items.type = 'out'";
					break;
				case "in":
					$clauses[] = "items.type <> 'out'";
					break;
				}
				
				//Name
				if($nameConstrained) {
					if(strpos($params["term"], "*")) {
						$term = str_replace("*", "%", $params["term"]);
						$clauses[] = "names.full_name like '${term}'";
					}
					else {
						$clauses[] = "names.full_name = '{$params['term']}'";
					}
				}

				//Place
				if($placeConstrained) {
					if(strpos($params["place"], "*")) {
						$place = str_replace("*", "%", $params["place"]);
						$clauses[] = "places.full_name like '${place}'";
					}
					else {
						$clauses[] = "places.full_name = '{$params['place']}'";
					}
				}
				//Media
				if(isset($params["withMedia"]) && $params["withMedia"] == "true") {
					$clauses[] = "items.media_count > 0";
				}
			}
				
			//Joins
			$clauses[] = "not items.box is null";
			$clauses[] = "places.id = items.place_id";
			$clauses[] = "names.id in (items.sender_id, items.receiver_id)";
			
			//Assemble
			$retval = implode(" and ", $clauses);
			return $retval;

		}

	}
?>