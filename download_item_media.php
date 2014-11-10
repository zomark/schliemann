<?php
	/**
		Build a ZIP file containing all media items for the $_GET["id"] item
		and reurn it to the caller
	*/
	require 'dbpdo.php';
	require 'authenticate.php';
	require 'media.php';
	$user = sc_get_user($_SERVER);
	if(!$user) {
		die("Authentication failed");
	}
	try {
		$db = new MyDB();
		if(!$db){
			die("No database");
		} 
		else {
			//Build SELECT statement
			$sqlMedia = "select m.name, m.type, i.org_id, i.type, i.year_sort, i.month_sort, i.day_sort  "
					."from media m, items i "
					."where m.items_id = ".$_GET["id"]." and i.id=".$_GET["id"];
			$result = $db->query($sqlMedia);
			//Get all available hires files
			$media = array();
			$meta = "";
			foreach($result as $row) {
				//Check for hires file
				$pathHires = media_get_hires($row[0]);
				if(null != $pathHires) {
					$media[] = $pathHires;
					$meta = "$row[2]_$row[4]".str_pad($row[5], 2, "0", STR_PAD_LEFT).str_pad($row[6], 2, "0", STR_PAD_LEFT);
				}
			}
			$result->closeCursor();
			//Assemble ZIP
			if(0 < count($media)) {
				$zip = new ZipArchive();
				$zipname = tempnam(sys_get_temp_dir(), null);

				if (!$zipname || $zip->open($zipname, ZipArchive::CM_PKWARE_IMPLODE) !== TRUE) {
					die("cannot open <$zipname>");
				}
				
				//Create and add Readme
				$sql = "select items.id as ID, items.org_id as dbID, items.date_sort as SortDate, "
					."items.day||' '||items.month||' '||items.year as FullDate, "
					."s.full_name as Sender, r.full_name as Recipient, "
					."p.full_name as Place, "
					."case(type) when 'out' then 'Copybook '||copybook||coalesce(', p'||page, '')||coalesce(', #'||item, '') else code end as Location "
					."from names s, names r, items, places p "
					."where s.id = items.sender_id and r.id = items.receiver_id "
					."and p.id = items.place_id "
					."and items.id = ".$_GET["id"];
				$result = $db->query($sql);
				foreach($result as $row) {
					$readme = <<<EOT
#$row[1]
$row[4] to $row[5]
$row[6] $row[3]
EOT;
					$zip->addFromString("Readme.txt", $readme);
				}
				$result->closeCursor();
				
				foreach($media as $f) {
					$zip->addFile($f, basename($f));
				}
				if(!$zip->close()) {
					die("zip close() failed");
				}
				ob_clean();
				$fname = str_replace(array("/", " ", "."), array(""), $meta).".zip";
				header('Content-Type: application/zip');
				header("Content-Disposition: attachment; filename=$fname");
				header('Content-Length: '.filesize($zipname));
				readfile($zipname);
				ob_flush();
				unlink($zipname);
			}
			else {
				die("No media available");
			}
		} 
	}
	catch(Exception $e) {
	}
?>