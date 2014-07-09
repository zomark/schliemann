<?php
	require 'dbpdo.php';
	header('Content-Type: text/html');
	$sql = "";
	try {
		$db = new MyDB();
		if(!$db){
			echo "<p>Error</p>";
			echo $db->lastErrorMsg();
		} 
		else {
			//Build SELECT statement
			$sql = "select items.id, items.type, items.date_sort as SortDate, "
					."items.org_id as ID, "
					."items.day||' '||items.month||' '||items.year as Date, "
					."s.full_name as Sender, r.full_name as Recipient, places.full_name as Place, "
					."case(type) when 'out' then 'Copybook '||copybook||coalesce(', p'||page, '')||coalesce(', #'||item, '') else code end as Location "
					."from names s, names r, items, places";
			$where = $db->where($_GET);
			if($where != "") {
				$sql .= " where ".$where;
			}
			$sql .= " order by items.date_sort";
			
//			error_log("query_items: ".$sql);
			$result = $db->query($sql);
			if($result) {
				//Build result table
				echo "<table id='resultstable'>";
				echo "<thead><tr>";
				for($i = 3; $i < $result->columnCount(); $i++) {
					echo "<th><div class='headerStatus'/><div class='columnLabel'>".$result->getColumnMeta($i)["name"]."</div></th>";
				}
				echo "</tr></thead>";
				echo "<tbody>";
				foreach ($result as $row) {
					echo "<tr class='item-".$row[1]."'>";
					for($i = 3; $i < $result->columnCount(); $i++) {
						switch($i) {
							case 3:
								echo "<td><a class='show-details' href='javascript:void' id='".$row[0]."'>".$row[$i]."</a></td>";
								break;
							case 4:
								echo "<td><span class='sortKey'>".$row[2]."</span>".$row[$i]."</td>";
								break;
							default:
								echo "<td>".htmlspecialchars($row[$i])."</td>";
						}
					}
					echo "</tr>";
				}
				echo "</tbody>";
				echo "</table>";
				$result->closeCursor();
			} 
			else {
				echo "<p>[No match]:</p>"
					."<p>$sql</p>";
			}
		} 
	}
	catch(Exception $e) {
		echo "<p>Error:".$e->getMessage()."</p>"
		."<p>$sql</p>";
	}
?>