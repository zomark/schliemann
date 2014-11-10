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