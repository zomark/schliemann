<?php
	require 'dbpdo.php';
	require 'authenticate.php';
	$user = sc_get_user($_SERVER);
	if(!$user) {
		die("Authentication failed");
	}
	header('Content-Type: application/vnd.ms-excel');
	header("Content-Disposition: attachment; filename=schliemann_correspondence.xml");
	try {
		$db = new MyDB();
		if(!$db){
			echo "<p>Error</p>";

			echo $db->lastErrorMsg();
		} 
		else {
			//Build SELECT statement
			$sql = "select items.id, items.type, "
				."items.org_id as \"Gennadios ID\", items.date_sort as SortDate, "
				."items.day||' '||items.month||' '||items.year as Date, "
				."s.full_name as Sender, r.full_name as Recipient, places.full_name as Place, "
				."case(type) when 'out' then 'Copybook '||copybook||coalesce(', p'||page, '')||coalesce(', #'||item, '') else code end as Location ";
			$sql .= "from names s, names r, items, places ";
			$where = $db->where($_GET);
			if($where != "") {
				$sql .= " where ".$where;
			}
			$sql .= " order by items.date_sort";
			
			error_log("download_items: ".$sql);


			// $sql = "";
			// $where = "";
			// $nameConstrained = isset($_GET["term"]) && $_GET["term"] != "";
			// $datesConstrained = (isset($_GET["from"]) && $_GET["from"] != "")
				// || (isset($_GET["to"]) && $_GET["to"] != "");
			// $placeConstrained = isset($_GET["place"]) && $_GET["place"] != "";
			// $cbConstrained = isset($_GET["copybook"]) && $_GET["copybook"] != "" && $_GET["copybook"] != "0";
			// $boxConstrained = isset($_GET["box"]) && $_GET["box"] != "" && $_GET["box"] != "0";
			
			// if($datesConstrained) {
				// if(isset($_GET["from"]) && $_GET["from"] != "") {
					// $where .= " items.date_sort >= '".$_GET["from"]."' ";
				// }
				// if(isset($_GET["to"]) && $_GET["to"] != "") {
					// if($where != "") {
						// $where .= "and ";
					// }
					// $where .= "items.date_sort <= '".$_GET["to"]."' ";
				// }
			// }

			// if($nameConstrained) {
				// if($where != "") {
					// $where .= "and ";
				// }
				// if(strpos($_GET["term"], "*")) {
					// $term = str_replace("*", "%", $_GET["term"]);
					// $where .= "(r.full_name like '${term}' or s.full_name like '${term}') ";
				// }
				// else {
					// $where .= "(r.full_name = '".$_GET["term"]."' or s.full_name = '".$_GET["term"]."') ";
				// }
			// }

			// if($placeConstrained) {
				// if($where != "") {
					// $where .= "and ";
				// }
				// if(strpos($_GET["place"], "*")) {
					// $place = str_replace("*", "%", $_GET["place"]);
					// $where .= "p.full_name like '${place}' ";
				// }
				// else {
					// $where .= "p.full_name = '".$_GET["place"]."' ";
				// }
			// }

			// if($cbConstrained) {
				// if($where != "") {
					// $where .= "and ";
				// }
				// $where .= "items.copybook = ".$_GET["copybook"];
			// }

			// if($boxConstrained) {
				// if($where != "") {
					// $where .= "and ";
				// }
				// $where .= "items.box = ".$_GET["box"];
			// }

			// $sql = "select items.id, items.type, "
					// ."items.org_id as \"Gennadios ID\", items.date_sort as SortDate, "
					// ."items.day||' '||items.month||' '||items.year as Date, "
					// ."s.full_name as Sender, r.full_name as Recipient, p.full_name as Place, "
					// ."case(type) when 'out' then 'Copybook '||copybook||coalesce(', p'||page, '')||coalesce(', #'||item, '') else code end as Location "
					// ."from names s, names r, items, places p "
					// ."where s.id = items.sender_id and r.id = items.receiver_id "
					// ."and p.id = items.place_id ";
				
			//Type
			// switch($_GET["type"]) {
				// case "out":
					// $sql .= "and items.type = 'out' ";
					// break;
				// case "in":
					// $sql .= "and items.type <> 'out' ";
					// break;
			// }
				
			// if($where != "") {
				// $sql .= "and ".$where;
			// }
			// $sql .= " order by items.date_sort";
			
//			echo "<p>".$sql."</p>";

			echo <<<EOT
<?xml version="1.0" encoding="utf-8"?>
 <ss:Workbook xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
     <ss:Worksheet ss:Name="Sheet1">
        <ss:Table>
EOT;
			$result = $db->query($sql);
			if($result) {
				//Build results
				echo "<ss:Row>";
				for($i = 2; $i < $result->columnCount(); $i++) {
					echo "<ss:Cell><ss:Data ss:Type=\"String\">".$result->getColumnMeta($i)["name"]."</ss:Data></ss:Cell>";
				}
				echo "</ss:Row>";
				foreach ($result as $row) {
					echo "<ss:Row>";
					for($i = 2; $i < $result->columnCount(); $i++) {
						switch($i) {
							case 2:
								echo "<ss:Cell><ss:Data ss:Type=\"Number\">".$row[$i]."</ss:Data></ss:Cell>";
								break;
							default:
								echo "<ss:Cell><ss:Data ss:Type=\"String\">".htmlspecialchars($row[$i])."</ss:Data></ss:Cell>";
						}
					}
					echo "</ss:Row>";
				}
				$result->closeCursor();
			} 
			echo <<<EOT
		</ss:Table>
    </ss:Worksheet>
</ss:Workbook>
EOT;
		} 
	}
	catch(Exception $e) {
	}
?>