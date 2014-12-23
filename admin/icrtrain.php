<?php

/*	Copyright Australian Consortium for Social and Political Research Incorporated (ACSPRI) 2011
 *	Written by Adam Zammit - adam.zammit@acspri.org.au
 *	For ACSPRI: http://www.acspri.org.au/
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
include("../db.inc.php");
include("../functions/functions.ocr.php");
include("../functions/functions.image.php");
include("../functions/functions.xhtml.php");

global $db;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title><?php print T_("ICR Training"); ?></title>
<link rel='stylesheet' href='../css/table.css' type='text/css'></link>
<style type='text/css'>
div.float {
  float: left;
 margin: 0 0 10px 10px;
 background-color: #00cc00;
 padding: 10px;
 border: 1px solid #666;
 height: 120px;
}
</style>

<script type='text/javascript'>

function prevdef(e)
{
  e.stopPropagation();
}

function toggle(eid,e)
{
  e.preventDefault();
  
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
	$count = 0;

	$kbd = $db->qstr($_POST['text']);

	//Generate KB from posted text
	$sql = "INSERT INTO ocrkb (kb,description)
		VALUES (NULL,$kbd)";

	$db->Execute($sql);

	$kb = $db->Insert_ID();

	foreach($_POST as $p => $val)
	{
		set_time_limit(60);
		$a = explode("_",$p);
		if (count($a) == 9)
		{
			$pid = $a[0];
			$fid = $a[1];
			$box = array('tlx'=>$a[2] + BOX_EDGE,'tly'=>$a[3] + BOX_EDGE,'brx'=>$a[4] - BOX_EDGE,'bry'=>$a[5] - BOX_EDGE);
			$vid = $a[6];
			$bid = $a[7];
//			$val = $a[8];

			if ($cpid != $pid || $cfid != $fid)
			{
				$cpid = $pid;
				$cfid = $fid;

				$sql = "SELECT *
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


			$sql = "SELECT count(*) as c FROM ocrtrain
				WHERE fid = '$fid' and vid = '$vid' and bid = '$bid'";

			$cc = $db->GetRow($sql);

			if ($cc['c'] > 0)
			{
				print T_("Found duplicate") . " $fid $vid $bid";
			}
			else
			{
				$row['width'] = imagesx($im);
				$row['height'] = imagesy($im);
				$image = crop($im,applytransforms($box,$row));
		
				$a1 = kfill_modified($image,5);
				$a2 = remove_boundary_noise($a1,2);
				$timage = resize_bounding($a2);
				$bimage = thinzs_np($timage);
				$t = sector_distance($bimage);

				$count++;

				$sql = "INSERT INTO ocrtrain (ocrtid,val,f1,f2,f3,f4,f5,f6,f7,f8,f9,f10,f11,f12,f13,f14,f15,f16,fid,vid,bid,kb)
					VALUES (NULL,'$val','{$t[0][1]}','{$t[0][2]}','{$t[0][3]}','{$t[0][4]}','{$t[0][5]}','{$t[0][6]}','{$t[0][7]}','{$t[0][8]}','{$t[0][9]}','{$t[0][10]}','{$t[0][11]}','{$t[0][12]}','{$t[1][1]}','{$t[1][2]}','{$t[1][3]}','{$t[1][4]}','$fid','$vid','$bid','$kb')";
	
				$db->Execute($sql);
			}

		}
	}

	print T_("Trained") . ": $count " . T_("characters");

	//generate kb
	generate_kb($kb);

	print T_("Generated KB");

}

if (isset($_GET['submit']))
{
	//run process in background
	$qid = intval($_GET['qid']);

	$verifiers = " AND (";
	$chars = " AND (";
	$ccs = "";
	$vv = 0;
	$cc = 0;
	foreach ($_GET as $key => $val)
	{
		if (substr($key,0,4) == 'vid_')
		{
			$vv = 1;
			$verifiers .= " fm.assigned_vid = $val OR";
		}
		else if (substr($key,0,5) == 'char_')
		{
			$cc = 1;
			$chars .= " c.val = '$val' OR";
			$ccs .= $val . ",";
		}
	}

	if ($vv)
		$verifiers = substr($verifiers,0,-3) . ")";
	else
		$verifiers = "";	

	if ($cc)
	{
		$chars = substr($chars,0,-3) . ")";
		$ccs = substr($ccs,0,-1);
	}
	else
		$chars = "";	

	$sql = "SELECT description FROM questionnaires WHERE qid = '$qid'";
	$qd = $db->GetRow($sql);

	$desc = T_("Chars") . " $ccs " . T_("from") . " " . $qd['description'];

	$db->StartTrans();

	//Generate KB from posted text
	$sql = "INSERT INTO ocrkb (kb,description)
		VALUES (NULL,'$desc')";

	$db->Execute($sql);

	$kb = $db->Insert_ID();

	$sql = "SELECT '$kb',b.bid as bid, c.val, f.fid, c.vid 
		FROM formboxverifychar as c
		JOIN boxes as b ON (b.bid = c.bid)
		JOIN formpages as f ON (f.fid = c.fid AND f.pid = b.pid)
		JOIN boxgroupstype as bg ON (bg.bgid = b.bgid and (bg.btid = 3 or bg.btid = 4))
		JOIN forms AS fm ON fm.fid = f.fid
		LEFT JOIN ocrtrain as oc ON (oc.fid = f.fid AND oc.vid = c.vid AND oc.bid = b.bid)
		WHERE fm.qid = '$qid'
		$verifiers
		$chars
		AND oc.ocrtid IS NULL";

	$rs = $db->GetAll($sql);

	foreach($rs as $r)
	{
		$sql = "INSERT INTO ocrprocess (kb,bid,val,fid,vid)
			VALUES ('$kb','{$r['bid']}','{$r['val']}','{$r['fid']}','{$r['vid']}')";

		$db->Execute($sql);

	}	

	$db->CompleteTrans();

	//Now start process and die
	include_once("../functions/functions.process.php");
	$pid = start_process(realpath(dirname(__FILE__) . "/processicr.php") . " $kb",2);

	print "<a href='icrmonitor.php?p='$pid'>" .  T_("Started training process") . "</a>";
	//link to monitor ICR process

}
else if (isset($_GET['char']))
{
	$char = ($_GET['char']);
	$qid = intval($_GET['qid']);

	$verifiers = " AND (";
	$vv = 0;
	foreach ($_GET as $key => $val)
	{
		if (substr($key,0,4) == 'vid_')
		{
			$vv = 1;
			$verifiers .= " fm.assigned_vid = $val OR";
		}
	}
	if ($vv)
		$verifiers = substr($verifiers,0,-3) . ")";
	else
		$verifiers = "";	


	$sql = "SELECT description FROM questionnaires WHERE qid = '$qid'";
	$qd = $db->GetRow($sql);

	$desc = T_("Character") . " $char " . T_("from") . " " . $qd['description'];

	$sql = "SELECT b.bid as bid, b.tlx as tlx, b.tly  as tly, b.brx  as brx, b.bry  as bry, c.val as val, b.pid as pid,b.bgid as bgid, f.fid, c.vid as vid
		FROM formboxverifychar as c
		JOIN boxes as b ON (b.bid = c.bid)
		JOIN formpages as f ON (f.fid = c.fid AND f.pid = b.pid)
		JOIN boxgroupstype as bg ON (bg.bgid = b.bgid and (bg.btid = 3 or bg.btid = 4))
		JOIN forms AS fm ON fm.fid = f.fid
		LEFT JOIN ocrtrain as oc ON (oc.fid = f.fid AND oc.vid = c.vid AND oc.bid = b.bid)
    WHERE c.val = '$char'
    AND c.vid != 0
		AND fm.qid = '$qid'
		$verifiers
		AND oc.ocrtid IS NULL
		ORDER BY fm.fid DESC
		LIMIT " . ICR_TRAIN_LIMIT;

	$rs = $db->GetAll($sql);

	$nr = count($rs);

	print "<p>" . T_("Make sure the letter in the box matches the image. If you do not want to import a box, click on it to disable it (click again to enable).") . "</p>";
	print "<form action='?' method='post'><p>";
	
	foreach($rs as $r)
	{
		$pid = $r['pid'];
		$vid = $r['vid'];
		$bid = $r['bid'];
		$tlx = $r['tlx'];
		$tly = $r['tly'];
		$brx = $r['brx'];
		$bry = $r['bry'];
		$fid = $r['fid'];

		print "<div class='float' id='div_".$pid."_".$fid."_".$tlx."_".$tly."_".$brx."_".$bry."_".$vid."_".$bid."_".$char."' onclick=\"toggle('".$pid."_".$fid."_".$tlx."_".$tly."_".$brx."_".$bry."_".$vid."_".$bid."_".$char."',event)\"><img alt='ocrimage' src='../showpage.php?pid=$pid&amp;bid=$bid&amp;fid=$fid'/><br/><p><input onclick='prevdef(event);' id='input_".$pid."_".$fid."_".$tlx."_".$tly."_".$brx."_".$bry."_".$vid."_".$bid."_".$char."' name='".$pid."_".$fid."_".$tlx."_".$tly."_".$brx."_".$bry."_".$vid."_".$bid."_".$char."' type='text' value='{$r['val']}' size='3' maxlength='1' /></p></div>";

	}
	print "</p><p><label for='text'>" . T_("Description for knowledge base:") . "</label><input name='text' id='text' type='text' value='$desc'/></p><p><input name='submit' id='submit' type='submit' value='" . T_("Train") . "'/></p></form>";

}
else if (isset($_GET['vid']))
{
	$verifiers = " AND (";
	$verif = "&amp;";
	$vv = 0;
	foreach ($_GET as $key => $val)
	{
		if (substr($key,0,4) == 'vid_')
		{
			$vv = 1;
			$verifiers .= " fm.assigned_vid = $val OR";
			$verif .= "$key=$val&amp;";
		}
	}
	if ($vv)
	{
		$verifiers = substr($verifiers,0,-3) . ")";
		$verif = substr($verif,0,-5);
	}
	else
	{
		$verifiers = "";	
		$verif = "";
	}

	//select a letter to do

	$qid = intval($_GET['qid']);

	$sql = "SELECT f.val,count(*) as c, CONCAT('<input type=\"checkbox\" name=\"char_', f.val, '\" value=\"',f.val,'\"/>') as checkbox,
		CONCAT('<a href=\"?qid=$qid&amp;char=', f.val ,'$verif\">" . T_("Manually train") . "</a>') as link
		FROM formboxverifychar as f
		JOIN boxes as b ON (b.bid = f.bid)
		JOIN boxgroupstype as bg ON (bg.bgid = b.bgid and (bg.btid = 3 or bg.btid = 4))
		JOIN forms as fm ON fm.fid = f.fid
		LEFT JOIN ocrtrain as oc ON (oc.fid = f.fid AND oc.vid = f.vid AND oc.bid = f.bid)
		WHERE (fm.qid = '$qid')
		AND f.val IS NOT NULL
		AND f.val != ' '
		AND oc.ocrtid IS NULL
		$verifiers
		GROUP BY val";

	$rs = $db->GetAll($sql);	

	print "<p>" . T_("Please choose which characters to include in training") . "</p>";

	print "<form action='?' method='get'>";
	xhtml_table($rs,array('val','c','checkbox','link'),array(T_("Character"),T_("Number of instances"),T_("Include in training?"),T_("Manually train")));
	print "<input type='hidden' name='qid' value='$qid'/>";
	foreach ($_GET as $key => $val)
	{
		if (substr($key,0,4) == 'vid_')
		{
			print "<input type='hidden' name='$key' value='$val'/>";
		}
	}
	print "<input type='hidden' name='vid' value='$qid'/>";
	print "<input type='submit' name='submit' value='" . T_("Start training process in background") . "'/>";
	print "</form>";

	
}
else if (isset($_GET['qid']))
{
	//select verifiers
	$qid = intval($_GET['qid']);	

	$sql = "SELECT v.vid, v.description, count( * ) AS c, CONCAT('<input type=\"checkbox\" name=\"vid_', v.vid, '\" value=\"',v.vid,'\"/>') as checkbox
		FROM `forms` AS f
		JOIN verifiers AS v ON ( v.vid = f.assigned_vid )
		WHERE f.qid = '$qid'
		GROUP BY f.assigned_vid";

	$rs = $db->GetAll($sql);

	print "<p>" . T_("Please choose which verifiers to include in training") . "</p>";

	print "<form action='?' method='get'>";
	xhtml_table($rs,array('description','c','checkbox'),array(T_("Verifier"),T_("Number of forms"),T_("Include in training?")));
	print "<input type='hidden' name='qid' value='$qid'/>";
	print "<input type='hidden' name='vid' value='$qid'/>";
	print "<input type='submit' name='submitc' value='" . T_("Continue training") . "'/>";
	print "</form>";
}
else
{
	//select a questionnaire
        //form to choose a questionnaire/form
        $sql = "SELECT qid,description
                FROM questionnaires
                ORDER BY qid DESC";

        $qs = $db->GetAll($sql);

        foreach($qs as $q)
        {
                print "<a href=\"?qid={$q['qid']}\">". T_("ICR Train") . ": {$q['description']}</a>";
                print "<br/>";
        }

}



?>
</body></html>
