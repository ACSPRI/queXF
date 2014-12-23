<?php

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


include("../config.inc.php");
include("../functions/functions.ocr.php");
include("../functions/functions.image.php");

global $db;

session_start();


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>OCR Training</title>
<style type='text/css'>
div.float {
  float: left;
 margin: 0 0 10px 10px;
 background-color: #00cc00;
 padding: 10px;
 border: 1px solid #666;

}
</style>

<script type='text/javascript'>
function toggle(eid)
{
	d = document.getElementById('div_' + eid);
	i = document.getElementById('input_' + eid);

	if (i.disabled == "")
	{
		i.disabled = "disabled";
		d.style.backgroundColor = "#cc0000";
	}
	else
	{
		i.disabled = "";
		d.style.backgroundColor = "#00cc00";
	}
}

</script>
</head>
<body>



<?php

if (isset($_POST['submit']))
{
	$cpid = "";
	$cfid = "";
	foreach($_POST as $p => $val)
	{
		$a = explode("_",$p);
		if (count($a) == 6)
		{
			$pid = $a[0];
			$fid = $a[1];
			$box = array('tlx'=>$a[2],'tly'=>$a[3],'brx'=>$a[4],'bry'=>$a[5]);
			
			if ($cpid != $pid || $cfid != $fid)
			{
				$cpid = $pid;
				$cfid = $fid;

				$sql = "SELECT image
					FROM formpages
					WHERE pid = $pid and fid = $fid";
		
				$row = $db->GetRow($sql);
      
        if ($row['filename'] == '')
        {
          $im = imagecreatefromstring($row['image']);
        }
        else
        {
          $im = imagecreatefrompng(IMAGES_DIRECTORY . $row['filename']);
        }
			}

			$image = st_ocr($im,$box);
			ob_start();
			imagegd2($image);
			$image = ob_get_contents();
			ob_end_clean();

			$sql = "INSERT INTO ocrtrainst (val,fid,image)
				VALUES ('$val','$fid','". addslashes($image) . "')";

			$db->Execute($sql);

		}
	}


}

if (isset($_GET['delete']))
{
	$fid = intval($_GET['delete']);

	$sql = "DELETE FROM ocrtrainst
		WHERE fid = '$fid'";

	$db->Execute($sql);

}




if (isset($_GET['fid']) && isset($_GET['qid']) && isset($_GET['vid']))
{
	$fid = intval($_GET['fid']);
	$qid = intval($_GET['qid']);
	$vid = intval($_GET['vid']);

	$sql = "SELECT b.bid as bid, (b.tlx + f.offx) as tlx, (b.tly + f.offy)  as tly, (b.brx + f.offx) as brx, (b.bry + f.offy) as bry, c.val as val, b.pid as pid, bg.btid as btid, b.bgid as bgid
			FROM boxes AS b
			JOIN boxgroupstype as bg ON (bg.bgid = b.bgid)
			JOIN pages as p ON (p.pid = b.pid)
			LEFT JOIN formboxverifychar AS c ON c.fid = '$fid'
			JOIN formpages as f on (f.fid = '$fid' and f.pid = b.pid)
			AND c.vid = '$vid'
			AND c.bid = b.bid
			WHERE (bg.btid = 3 or bg.btid = 4)
			AND p.qid = '$qid'
			AND c.val IS NOT NULL
			AND c.val != ' '
			ORDER BY p.pid ASC, bg.sortorder ASC";

	$rs = $db->GetAll($sql);

	print "<div><a href='?'>Back to index</a></div>";
	print "<p>Make sure the letter in the box matches the image. If you do not want to import a box, click on it to disable it (click again to enable).</p>";
	print "<form action='?' method='post'><div>";
	foreach($rs as $r)
	{
		$pid = $r['pid'];
		$bid = $r['bid'];
		$tlx = $r['tlx'];
		$tly = $r['tly'];
		$brx = $r['brx'];
		$bry = $r['bry'];


		print "<div class='float' id='div_".$pid."_".$fid."_".$tlx."_".$tly."_".$brx."_".$bry."' onclick=\"toggle('".$pid."_".$fid."_".$tlx."_".$tly."_".$brx."_".$bry."')\"><img alt='ocrimage' src='../showpage.php?pid=$pid&amp;bid=$bid&amp;fid=$fid'/><br/><p><input id='input_".$pid."_".$fid."_".$tlx."_".$tly."_".$brx."_".$bry."' name='".$pid."_".$fid."_".$tlx."_".$tly."_".$brx."_".$bry."' type='text' value='{$r['val']}' size='3'/></p></div>";

	}
	print "</div><p><input name='submit' id='submit' type='submit'/></p></form>";


	//display the $_SESSION['count']th variable


}
else
{
	//select a form to do

	$sql = "SELECT f.fid, f.qid, f.assigned_vid AS vid, f.description AS fd, v.description AS vd, q.description AS qd, o.c AS ocr
		FROM forms AS f
		JOIN (verifiers AS v, questionnaires AS q) ON ( f.done = '1' AND f.assigned_vid = v.vid AND f.qid = q.qid )
		LEFT JOIN (
			SELECT count( * ) AS c, fid
			FROM ocrtrainst
			GROUP BY fid
			) AS o ON ( o.fid = f.fid )
		ORDER BY f.fid ASC";

	$rs = $db->GetAll($sql);	

	foreach ($rs as $r)
	{
		$ocr = $r['ocr'];
		if (empty($ocr))
			print "<div><a href='?fid=".$r['fid']."&amp;qid=".$r['qid']."&amp;vid=".$r['vid']."'>".$r['qd']." form: ".$r['fd']." by: ".$r['vd']." - Not Trained</a></div>";
		else
			print "<div>".$r['qd']." form: ".$r['fd']." by: ".$r['vd']." - $ocr boxes trained <a href='?delete=".$r['fid']."'>Delete from training database</a></div>";
	}

}




?>


</body></html>
