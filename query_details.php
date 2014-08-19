<?php
	header('Content-Type: text/html');
	require 'dbpdo.php';
	require 'media.php';
	require 'authenticate.php';
	$user = sc_get_user($_SERVER);
	if(!$user) {
		die("Authentication failed");
	}
	try {
		$db = new MyDB();
		if(!$db){
			echo "<p>".$db->lastErrorMsg()."</p>";
		} 
		else {
			$sql = "";
			$sql = "select items.id as ID, items.org_id as dbID, items.date_sort as SortDate, "
					."items.day||' '||items.month||' '||items.year as FullDate, "
					."s.full_name as Sender, r.full_name as Recipient, "
					."p.full_name as Place, "
					."case(type) when 'out' then 'Copybook '||copybook||coalesce(', p'||page, '')||coalesce(', #'||item, '') else code end as Location "
					."from names s, names r, items, places p "
					."where s.id = items.sender_id and r.id = items.receiver_id "
					."and p.id = items.place_id "
					."and items.id = ".$_GET["id"];
				
			$sqlMedia = "select name, type, items_id "
					."from media "
					."where items_id = ".$_GET["id"];
			
			
			$result = $db->query($sql);
			if($result) {
				//Build result
				echo "<table class='details'>";
				foreach($result as $row) {
					for($i = 0; $i < $result->columnCount(); $i++) {
						echo "<tr>";
						echo "<th>".$result->getColumnMeta($i)["name"]."</th>";
						echo "<td>".$row[$i]."</td>";
						echo "</tr>";
					}
				}	
				echo "</table>";
				$result->closeCursor();

				$resultMedia = $db->query($sqlMedia);
				if($resultMedia) {
					echo "<div class=\"thumbnails\">"
						.media_render_thumbnails($resultMedia)
						."</div>";
					$resultMedia->closeCursor();
				}
			} 
			else {
				$data[0] = "[No match]";
			}
//			echo $sql;
		} 
	}
	catch(Exception $e) {
		echo "<p>Error:".$e->getMessage()."</p>";
	}
?>