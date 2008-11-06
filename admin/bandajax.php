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


include_once("../config.inc.php");
include_once("../db.inc.php");
include("../functions/functions.xhtml.php");
include("../functions/functions.database.php");


//given a pageid, print XHTML for each box as an image overlay
function pidtomap($pid,$zoom = 1)
{
	global $db;

	$sql = "SELECT tlx,tly,brx,bry,bid,btid
		FROM boxesgroupstypes
		WHERE pid = $pid";

	$boxes = $db->GetAll($sql);

	if (empty($boxes)) return;

	$t = 1;

	foreach($boxes as $box)
	{
		$colour = TEMPORARY_COLOUR;
		if ($box['btid'] == 1) $colour = SINGLECHOICE_COLOUR; 
		if ($box['btid'] == 2) $colour = MULTIPLECHOICE_COLOUR; 
		if ($box['btid'] == 3) $colour = TEXT_COLOUR; 
		if ($box['btid'] == 4) $colour = NUMBER_COLOUR; 
		if ($box['btid'] == 5) $colour = BARCODE_COLOUR; 
		if ($box['btid'] == 6) $colour = LONGTEXT_COLOUR; 
	
		print "<div id=\"modbox$t\" style=\"position:absolute; top:" . $box['tly'] / $zoom . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px; background-color: $colour;opacity:" . BAND_OPACITY . "; -moz-opacity: " . BAND_OPACITY . "; z-index: 50;\" onclick=\"window.open('../modifybox.php?bid={$box['bid']}')\"></div>";

	print "<script type=\"text/javascript\">
new Proto.Menu({
  selector: '#modbox$t', 
  className: 'menu desktop',
  menuItems: [";
if (!($box['btid'] == 0)) print  "{    name: 'Disable this group',  className: 'edit',  callback: function() { updateBoxes(" . $box['bid'] . ",0);  }   },";
if (!($box['btid'] == 1)) print  "    {    name: 'Set to type: Single choice', className: 'edit',   callback: function() { updateBoxes(" . $box['bid'] . ",1); } },";
if (!($box['btid'] == 2)) print  "    {    name: 'Set to type: Multiple choice', className: 'copy', callback: function() {  updateBoxes(" . $box['bid'] . ",2); }   },";
if (!($box['btid'] == 3)) print  "    {    name: 'Set to type: Text and Numbers',  className: 'copy', callback: function() {  updateBoxes(" . $box['bid'] . ",3); }   },";
if (!($box['btid'] == 4)) print  "    {    name: 'Set to type: Numbers only', className: 'copy',  callback: function() { updateBoxes(" . $box['bid'] . ",4);  }  },";
if (!($box['btid'] == 5)) print  "    {    name: 'Set to type: Interleaved 2 of 5 Barcode', className: 'copy',  callback: function() { updateBoxes(" . $box['bid'] . ",5); } },";
if (!($box['btid'] == 6)) print  "    {    name: 'Set to type: Long text', className: 'copy',  callback: function() { updateBoxes(" . $box['bid'] . ",6);  } },";
print "    {    separator: true  },
    {    name: 'Delete this box',    className: 'save',    callback: function() {   deleteBox(" . $box['bid'] . ");    } }, 
    {    name: 'Delete this box group',    className: 'save',    callback: function() {      deleteBoxGroup(" . $box['bid'] . ");  }  }
] });
</script>";


		$t+= 1;
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


function createboxes($sx,$sy,$x,$y,$pid,$qid)
{
	//done now calculate map
	include("../functions/functions.boxdetection.php");
	include("../functions/functions.image.php");
	include("../functions/functions.barcode.php");

	global $db;

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

}


/* Create a box group in the DB
 */
function updateboxgroup($bid,$width,$varname,$btid)
{
	global $db;
	$db->StartTrans();

	$sql = "SELECT bgid
		FROM boxgroups
		WHERE bid = '$bid'";

	$rs = $db->GetRow($sql);

	$bgid = $rs['bgid'];


	$sql = "UPDATE boxgroupstype
		SET btid = '$btid', width = '$width', varname = '$varname'
		WHERE bgid = '$bgid'";

	$db->Execute($sql);

	$db->CompleteTrans();
}

/**
 * When boxes are evenly spaced, boxes are created inbetween
 * Delete the inbetween boxes
 */
function deleteinbetween($bgid)
{
	global $db;
	$db->StartTrans();

	$sql = "SELECT bid 
		FROM boxgroups 
		WHERE bgid = '$bgid'";

	$rows = $db->GetAll($sql);

	$rc = 1;
	foreach($rows as $row)
	{
		if (($rc % 2) == 0 && next($rows)) // if even and there is at least one more box
		{
			$sql = "DELETE
				FROM boxes
				WHERE bid = '{$row['bid']}'";
	
			$db->Execute($sql);

			$sql = "DELETE 
				FROM boxgroups
				WHERE bid = '{$row['bid']}'";
	
			$db->Execute($sql);
		}
		$rc++;
	}

	$db->CompleteTrans();

	return $bgid;

}


/**
 * Delete a box from a boxgroup
 */
function deletebox($bid)
{
	global $db;
	$db->StartTrans();

	$sql = "DELETE
		FROM boxes
		WHERE bid = '$bid'";
	
	$db->Execute($sql);

	$sql = "DELETE 
		FROM boxgroups
		WHERE bid = '$bid'";

	$db->Execute($sql);

	$db->CompleteTrans();

	return $bid;

}



/* Delete a box group in the DB
 */
function deleteboxgroup($bid)
{

	global $db;
	$db->StartTrans();
	
	$sql = "SELECT bgid
		FROM boxgroups
		WHERE bid = '$bid'";

	$rs = $db->GetRow($sql);

	$bgid = $rs['bgid'];


	$sql = "SELECT bid 
		FROM boxgroups 
		WHERE bgid = '$bgid'";

	$rows = $db->GetAll($sql);

	foreach($rows as $row)
	{
		$sql = "DELETE
			FROM boxes
			WHERE bid = '{$row['bid']}'";

		$db->Execute($sql);
	}

	$sql = "DELETE 
		FROM boxgroups
		WHERE bgid = '$bgid'";

	$db->Execute($sql);

	$sql = "DELETE
		FROM boxgroupstype
		WHERE bgid = '$bgid'";

	$db->Execute($sql);

	$db->CompleteTrans();

	return $bgid;
}

if (isset($_GET['pid']) && isset($_GET['qid']) && isset($_GET['zoom']))
{
	$pid = intval($_GET['pid']);
	$qid = intval($_GET['qid']);
	$zoom = intval($_GET['zoom']);

	if (isset($_GET['deletegroupbid']))
	{
		deleteboxgroup(intval($_GET['deletegroupbid']));
		pidtomap($pid,$zoom);
		exit();
	}
	
	if (isset($_GET['deletebid']))
	{
		deletebox(intval($_GET['deletebid']));
		pidtomap($pid,$zoom);
		exit();
	}
	
	
	if (isset($_GET['deleteinbetween']))
	{
		deleteinbetween(intval($_GET['deleteinbetween']));
		pidtomap($pid,$zoom);
		exit();
	}
	
	
	if (isset($_GET['bid']) && isset($_GET['btid']))
	{
		updateboxgroup(intval($_GET['bid']),1,'',intval($_GET['btid']));
		pidtomap($pid,$zoom);
		exit();
	}
	
	
	if (isset($_GET['x']) && isset($_GET['y']) && isset($_GET['w']) && isset($_GET['h']))
	{
		$x = intval($_GET['x']);
		$y = intval($_GET['y']);
		$w = intval($_GET['w']);
		$h = intval($_GET['h']);
	
		createboxes(($x * $zoom), ($y * $zoom), (($x + $w)*$zoom),(($y + $h)*$zoom), $pid, $qid);
		pidtomap($pid,$zoom);
		exit();
	}	
}

xhtml_head("Band",true,array("../css/proto.menu.0.6.css","../css/marker.css"),array("../js/prototype-1.6.0.2.js","../js/proto.menu.0.6.js","../js/rectmarquee.js","../js/band.js"));

print "<div id='content'>";


if (isset($_GET['qid']))
{
	$qid = intval($_GET['qid']);

	$zoom = 1;
	if (isset($_GET['zoom'])) $zoom = intval($_GET['zoom']);

	if (isset($_GET['reorder'])) 
	{
		sort_order_pageid_box($qid);	
	}

	$zoomup = $zoom - 1; if ($zoomup < 1) $zoomup = 1;
	$zoomdown = $zoom + 1;


	//print all available pages as a link google style
	$sql = "SELECT pid
		FROM pages
		WHERE qid = $qid";

	$pages = $db->GetAll($sql);

	$p = 1;
	foreach($pages as $page)
	{
		$pid = 0;
		if (isset($_GET['pid'])) $pid = intval($_GET['pid']);
		if ($page['pid'] == $pid)
		{	
			print " <span style=\"font-size:150%;\">$p</span> ";
		}else
		{
			print " <a href=\"?pid={$page['pid']}&amp;qid=$qid&amp;zoom=$zoom\">$p</a> ";
		}
		$p++;
	}
	print "<br/>";


	if (isset($_GET['pid']))
	{
		$pid = intval($_GET['pid']);

		print " <a href=\"?zoom=$zoom\">Choose another questionnaire</a> <a href=\"?zoom=$zoomup&amp;qid=$qid&amp;pid=$pid\">Increase zoom</a> <a href=\"?zoom=$zoomdown&amp;qid=$qid&amp;pid=$pid\">Decrease zoom</a><br/> ";


		//show image with no coords selected
		print "<div id=\"imagearea\" style=\"position:relative;\">";
		print "<div id=\"imageboxes\">";
		pidtomap($pid,$zoom);
		print "</div>";
		print "<div id=\"imageimage\">";
		$w = floor(PAGE_WIDTH / $zoom);
		$h = floor(PAGE_HEIGHT / $zoom);
		print "<img id=\"sampleid\" src=\"../showpage.php?pid=$pid\" style=\"border:0\" width=\"$w\" height=\"$h\" alt=\"page $pid image\"/>";
		print "</div>";
		print "</div>";

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
		print "<a href=\"?zoom=$zoom&amp;qid={$q['qid']}\">Band: {$q['description']}</a> <a href=\"?reorder=reorder&amp;zoom=$zoom&amp;qid={$q['qid']}\">Reorder variables: {$q['description']}</a>";
		print "<br/>";
	}
}

print "</div>";
xhtml_foot();

?>

