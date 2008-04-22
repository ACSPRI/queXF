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
 background-color: #ddd;
 padding: 10px;
 border: 1px solid #666;

}
</style>
</head>
<body>

<?

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
		
				$im = imagecreatefromstring($row['image']);
			}

			$bound = get_bounding_box($im,$box);
			$boxes = get_25_boxes($im,$bound);

			//print "VAL: $val ";
			//print_r($boxes);
			//print "<br/>";
			$boxes['val'] = $val;

			$rs = $db->Execute("SELECT * from ocrtrain LIMIT 0");
			$sql = $db->GetInsertSQL($rs,$boxes);
			$db->Execute($sql);
			//quexf_ocr($boxes);
		}
	}


}


if (isset($_GET['fid']) && isset($_GET['qid']) && isset($_GET['vid']))
{
	$fid = intval($_GET['fid']);
	$qid = intval($_GET['qid']);
	$vid = intval($_GET['vid']);

	$sql = "SELECT b.bid as bid, (b.tlx + f.offx) as tlx, (b.tly + f.offy)  as tly, (b.brx + f.offx) as brx, (b.bry + f.offy) as bry, c.val as val, b.pid as pid, b.btid as btid, b.bgid as bgid
			FROM boxesgroupstypes AS b
			LEFT JOIN formboxverifychar AS c ON c.fid = '$fid'
			JOIN formpages as f on (f.fid = '$fid' and f.pid = b.pid)
			AND c.vid = '$vid'
			AND c.bid = b.bid
			WHERE (b.btid = 3 or b.btid = 4)
			AND b.qid = '$qid'
			AND c.val IS NOT NULL
			ORDER BY b.pid ASC, sortorder ASC";

	$rs = $db->GetAll($sql);

	print "<div><a href='?'>Back to index</a></div>";
	print "<form action='?' method='post'><div>";
	foreach($rs as $r)
	{
		$pid = $r['pid'];
		$bid = $r['bid'];
		$tlx = $r['tlx'];
		$tly = $r['tly'];
		$brx = $r['brx'];
		$bry = $r['bry'];


		print "<div class='float'><img alt='ocrimage' src='../showpage.php?pid=$pid&amp;bid=$bid&amp;fid=$fid'/><br/><p><input name='".$pid."_".$fid."_".$tlx."_".$tly."_".$brx."_".$bry."' type='text' value='{$r['val']}' size='3'/></p></div>";

	}
	print "</div><p><input name='submit' id='submit' type='submit'/></p></form>";


	//display the $_SESSION['count']th variable


}
else
{
	//select a form to do

	$sql = "SELECT f.fid,f.qid,f.assigned_vid as vid,f.description as fd,v.description as vd,q.description as qd
		FROM forms as f, verifiers as v, questionnaires as q
		WHERE f.done = '1'
		AND f.assigned_vid = v.vid
		AND f.qid = q.qid";

	$rs = $db->GetAll($sql);	

	foreach ($rs as $r)
	{
		print "<div><a href='?fid=".$r['fid']."&amp;qid=".$r['qid']."&amp;vid=".$r['vid']."'>".$r['qd']." form: ".$r['fd']." by: ".$r['vd']."</a></div>";
	}

}




?>


</body></html>
