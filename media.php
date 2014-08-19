<?php
//$result: name, type, item_id
function media_render_thumbnails($result) {
	$val = "";
	$pagenum = 0;
	foreach($result as $row) {
		$name = $row[0];
		$type = $row[1];
		$item_id = $row[2];
		switch($type) {
			case "image/jpeg":
				$val .= media_render_jpeg_thumbnail($name, $item_id, ++$pagenum);
				break;
			default:
				$val .= $row[1];
		}
	}
	return $val;
}

function media_render_jpeg_thumbnail($name, $item_id, $pagenum) {
	//Derive subfolder from filename
	$subfolder = implode("-", array_slice(explode("-", $name), 0, -1));
	//Filename
	$pathThumb = dirname(__FILE__)."/media/thumbnails/$subfolder/$name.jpg";
	//Hires filename
	$pathHires = dirname(__FILE__)."/media/hires/$subfolder/$name.jpg";
	//Check existence, write image and anchor
	$retval = "";
	if(is_readable($pathThumb)) {
		$hiresExists = is_readable($pathHires);
		if($hiresExists) {
//			$retval .= "<a href=\"media/hires/$subfolder/$name.jpg\">";
			$retval .= "<a href=\"item.php?id=$item_id&pagenum=$pagenum\">";
		}
		$retval .= "<img title=\"Page $pagenum\" class=\"thumbnail\" src=\"media/thumbnails/$subfolder/$name.jpg\" alt=\"$name\"/>";
		if($hiresExists) {
			$retval .= "</a>";
		}
	}
	return $retval;
}

?>