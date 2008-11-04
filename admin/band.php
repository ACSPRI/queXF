<?

/*	Copyright Deakin University 2007,2008
 *	Written by Adam Zammit - adam.zammit@deakin.edu.au
 *	For the Deakin Computer Assisted Research Facility: http://www.deakin.edu.au/dcarf/
 *	
 *	This file is part of queXF
 *	
 *	queXF is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *	
 *	queXF is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *	
 *	You should have received a copy of the GNU General Public License
 *	along with queXF; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */


//BAND a form


include_once('../config.inc.php');
include("../functions/functions.database.php");

//given a pageid, print an HTML image map for each box
function pidtomap($pid,$zoom = 1,$mapname = "boxes")
{
	global $db;

	$sql = "SELECT tlx,tly,brx,bry,bid,btid
		FROM boxesgroupstypes
		WHERE pid = $pid";

	$boxes = $db->GetAll($sql);

	if (empty($boxes)) return;

	foreach($boxes as $box)
	{
		$colour = "red";
		if ($box['btid'] == 1) $colour = "green"; 
		if ($box['btid'] == 2) $colour = "blue"; 
		if ($box['btid'] == 3) $colour = "purple"; 
		if ($box['btid'] == 4) $colour = "orange"; 
		if ($box['btid'] == 5) $colour = "brown"; 
		if ($box['btid'] == 6) $colour = "pink"; 
	
		print "<div id=\"modbox" . $box['bid'] . "\" style=\"position:absolute; top:" . $box['tly'] / $zoom . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px; background-color: $colour;opacity:.60; -moz-opacity: 0.60;\" onclick=\"window.open('../modifybox.php?bid={$box['bid']}')\"></div>";

	}




}

/* Create a box group in the DB
 */
function createboxgroup($boxes,$width,$varname,$pid,$btid = 0)
{
	global $db;

	$db->StartTrans();

	//insert into boxgroupstype
	$sql = "INSERT INTO boxgroupstype (bgid, btid, width, pid, varname)
		VALUES (NULL,'$btid','$width','$pid','$varname')";

	$db->Execute($sql);

	$bgid = $db->Insert_ID();

	//insert boxes
	foreach ($boxes as $box)
	{
		$sql = "INSERT INTO boxes (bid, tlx, tly, brx, bry, pid, bgid)
			VALUES (NULL,'{$box['tlx']}','{$box['tly']}','{$box['brx']}','{$box['bry']}','$pid','$bgid')";

		$db->Execute($sql);

		$bid = $db->Insert_ID();

		$sql = "INSERT INTO boxgroups (bgid, bid)
			VALUES ('$bgid','$bid')";

		$db->Execute($sql);

	}

	$db->CompleteTrans();

	return $bgid;
}


session_start();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>Band</title>

<script type="text/javascript">
function init()
{
<?
	

	if (isset($_GET['start']))
	{
		$start = $_GET['start'];
		$start = substr($start,1);
		$scoords = explode(",",$start);
		$x = $scoords[0];
		$y = $scoords[1];
		//print "document.body.scrollLeft = $x;\n";
		//print "document.body.scrollTop = $y;\n";
		print "window.scroll($x,$y);";
		//print "document.getElementById('content').scrollLeft = 1000;\n";
		//print "document.getElementById('content').scrollTop = 1500;\n";
	}
		
?>

}

function al()
{
 var scrOfX = 0, scrOfY = 0;
  if( typeof( window.pageYOffset ) == 'number' ) {
    //Netscape compliant
    scrOfY = window.pageYOffset;
    scrOfX = window.pageXOffset;
  } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
    //DOM compliant
    scrOfY = document.body.scrollTop;
    scrOfX = document.body.scrollLeft;
  } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
    scrOfX = document.documentElement.scrollLeft;
  }
	alert(scrOfY  + scrOfX);


}


window.onload = init;

</script>
</head>		
<body>
<div id="content">
<?


if (isset($_GET['qid']))
{
	$qid = intval($_GET['qid']);

	$zoom = 1;
	if (isset($_GET['zoom'])) $zoom = intval($_GET['zoom']);

	if (isset($_GET['reorder'])) 
	{
		sort_order_pageid_box($qid);	
	}

	if (isset($_GET['pid']))
	{
		$pid = intval($_GET['pid']);

		$zoomup = $zoom - 1; if ($zoomup < 1) $zoomup = 1;
		$zoomdown = $zoom + 1;

		print " <a href=\"band.php?zoom=$zoom\">Choose another questionnaire</a> <a href=\"band.php?zoom=$zoomup&amp;qid=$qid&amp;pid=$pid\">Increase zoom</a> <a href=\"band.php?zoom=$zoomdown&amp;qid=$qid&amp;pid=$pid\">Decrease zoom</a><br/> ";

		//print all available pages as a link google style
		$sql = "SELECT pid
			FROM pages
			WHERE qid = $qid";

		$pages = $db->GetAll($sql);

		$p = 1;
		foreach($pages as $page)
		{
			if ($page['pid'] == $pid)
			{	
				print " <span style=\"font-size:150%;\">$p</span> ";
			}else
			{
				print " <a href=\"band.php?pid={$page['pid']}&amp;qid=$qid&amp;zoom=$zoom\">$p</a> ";
			}
			$p++;
		}
		print "<br/>";



		if (isset($_GET['start']))
		{
			$start = $_GET['start'];
			$start = substr($start,1);
			$scoords = explode(",",$start);
		
			if (isset($_GET['map']))
			{
				//done now calculate map
				include("../functions/functions.boxdetection.php");
				include("../functions/functions.image.php");
				include("../functions/functions.barcode.php");

				$map = $_GET['map'];
				$map = substr($map,1);
				$coords = explode(",",$map);

				$x = $coords[0] * $zoom;
				$y = $coords[1] * $zoom;
	
				$sx = $scoords[0] * $zoom;
				$sy = $scoords[1] * $zoom;

				$sql = "SELECT image 
					FROM pages
					WHERE pid = $pid";
			
				$row = $db->GetRow($sql);
			
				if (empty($row)) exit;
			
				$image = imagecreatefromstring($row['image']);


				$barcode = crop($image,array("tlx" => $sx, "tly" => $sy, "brx" => $x, "bry" => $y));

				//check for barcode
				$barcodenum = barcode($barcode);
				if ($barcodenum)
				{
					$a = array();
					$a[] = array($sx);
					$a[] = array($sy);
					$a[] = array($x);
					$a[] = array($y);
					$barcodewidth = strlen($barcodenum);
				}
				else
				{

					$lw = lineWidth($sx,$sy,$x,$y,$image);
		
					$a = 0;
	
					//print_r($lw);
	
					$a = vasBoxDetection($lw);				
					if ($a == false)
					{
						if (($x - $sx) > ($y - $sy))
							$a = horiBoxDetection($lw);
						else
							$a = vertBoxDetection($lw);
					}
				}
	
				$boxes = count($a[0]);

				//convert to box format
				$boxes = array();
				for ($i = 0; $i < count($a[0]); $i++)
				{
					$box = array();
					$box['tlx'] = $a[0][$i];
					$box['tly'] = $a[1][$i];
					$box['brx'] = $a[2][$i];
					$box['bry'] = $a[3][$i];					
					$boxes[] = $box;
				}

				$crop = array();
				$crop['tlx'] = $sx;
				$crop['tly'] = $sy;
				$crop['brx'] = $x;
				$crop['bry'] = $y;


				if ($barcodenum) //create barcode box group
					$bgid = createboxgroup($boxes,$barcodewidth,'tmpbarcode',$pid,5);
				else 	//create temp box group
					$bgid = createboxgroup($boxes,1,'tmp',$pid,0);

				//display cropped image with selection
				//print "<img src=\"../showpage.php?bgid=$bgid\"/>";

				//display details to be submitted to create box group?

				//show image with no coords selected
				print "<div id=\"tmp\" style=\"position:relative;\">";
				pidtomap($pid,$zoom);
				print "<div id=\"boxGroup\" style=\"position:absolute; top:{$scoords[0]}px; width:1px; height:1px; background-color: white;opacity:.0;\"></div>";

				print "<a href=\"band.php?pid=$pid&amp;qid=$qid&amp;zoom=$zoom&amp;start=\">";
				$w = floor(2480 / $zoom);
				$h = floor(3508 / $zoom);
				print "<img id=\"sampleid\" src=\"../showpage.php?pid=$pid\" style=\"border:0\" width=\"$w\" height=\"$h\" ismap=\"ismap\"  alt=\"page image\"/>";
				print "</a>";
				print "</div>";


			}
			else
			{
				//show image with start coords selected
				print "<div id=\"tmp\" style=\"position:relative;\">";
				pidtomap($pid,$zoom);
				print "<div id=\"boxGroup\" style=\"position:absolute; top:{$scoords[0]}px; width:1px; height:1px; background-color: white;opacity:.0;\"></div>";
				print "<a href=\"band.php?pid=$pid&amp;qid=$qid&amp;zoom=$zoom&amp;start=?{$scoords[0]},{$scoords[1]}&amp;map=\">";
				$w = floor(2480 / $zoom);
				$h = floor(3508 / $zoom);
				print "<img id=\"sampleid\"  src=\"../showpage.php?pid=$pid\" style=\"border:0\" width=\"$w\" height=\"$h\" ismap=\"ismap\"  alt=\"page image\" />";
				print "</a>";
				print "</div>";


			}
		}
		else
		{
			//show image with no coords selected
			print "<div id=\"tmp\" style=\"position:relative;\">";
			pidtomap($pid,$zoom);
			print "<a href=\"band.php?pid=$pid&amp;qid=$qid&amp;zoom=$zoom&amp;start=\">";
			$w = floor(2480 / $zoom);
			$h = floor(3508 / $zoom);
			print "<img id=\"sampleid\"  src=\"../showpage.php?pid=$pid\" style=\"border:0\" width=\"$w\" height=\"$h\" ismap=\"ismap\" alt=\"page image\"/>";
			print "</a>";
			
			print "</div>";

		}

	}
	else
	{
		//print all available pages as a link google style
		$sql = "SELECT pid
			FROM pages
			WHERE qid = $qid";

		$pages = $db->GetAll($sql);

		$p = 1;
		foreach($pages as $page)
		{
			print " <a href=\"band.php?pid={$page['pid']}&amp;qid=$qid&amp;zoom=$zoom\">$p</a> ";
			$p++;
		}
		print "<br/>";

	}
}
else
{
	//print available questionnaires
	$sql = "SELECT qid,description
		FROM questionnaires";
	
	$qs = $db->GetAll($sql);

	$zoom = 1;
	if (isset($_GET['zoom'])) $zoom = intval($_GET['zoom']);


	foreach($qs as $q)
	{

		print "<a href=\"band.php?zoom=$zoom&amp;qid={$q['qid']}\">Band: {$q['description']}</a> <a href=\"band.php?reorder=reorder&amp;zoom=$zoom&amp;qid={$q['qid']}\">Reorder variables: {$q['description']}</a>";
		print "<br/>";
	}


}

?>


</div></body></html>

