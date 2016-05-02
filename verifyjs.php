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


//verifier

include_once("config.inc.php");
include_once("db.inc.php");
include("functions/functions.image.php");
include("functions/functions.xhtml.php");
include("functions/functions.database.php");
				

function bgidtocss($zoom = 1,$fid,$pid)
{
	global $db;

	$sql = "SELECT MIN(b.tlx) as tlx,MIN(b.tly) as tly,MAX(b.brx) as brx,MAX(b.bry) as bry, b.pid as pid, bg.btid as btid, b.bgid as bgid
		FROM boxes as b, boxgroupstype as bg
		WHERE b.pid = '$pid'
		AND bg.bgid = b.bgid
		AND bg.btid > 0
		GROUP BY bg.bgid
		ORDER BY bg.sortorder ASC";

	$boxgroups = $db->GetAll($sql);

	$sql = "SELECT offx,offy,centroidx,centroidy,costheta,sintheta,scalex,scaley,width,height
		FROM formpages as f
		WHERE f.pid = $pid and f.fid = $fid";
	
	$row = $db->GetRow($sql);

	$sql = "SELECT b.bid
		FROM boxes as b, boxgroupstype as bg
		WHERE b.pid = '$pid'
		AND bg.bgid = b.bgid
		AND bg.btid > 0
		ORDER BY bg.sortorder ASC, b.bid ASC";

	$boxes = $db->GetAll($sql);

	$vis = "visible";

	if (!isset($row['offx']) && !isset($row['offy']))
	{ 
		$row = array();
		$row['offx'] = 0;
		$row['offy'] = 0;
		$row['centroidx'] = PAGE_WIDTH / 2;
		$row['centroidy'] = PAGE_HEIGHT / 2;
		$row['costheta'] = 1;
		$row['sintheta'] = 0;
		$row['scalex'] = 1;
		$row['scaley'] = 1;
	}

	//fix for upgrades
	if ($row['width'] == 0) $row['width'] = PAGE_WIDTH;
	if ($row['height'] == 0) $row['height'] = PAGE_HEIGHT;

	print "<form method=\"post\" action=\"{$_SERVER['PHP_SELF']}\">";

	foreach ($boxgroups as $boxgroup)
	{
		$crop = applytransforms($boxgroup,$row);

		$bgid = $boxgroup['bgid'];

		//make box group display higher
		$ttop = ($crop['tly'] / $zoom) - DISPLAY_GAP;
		if ($ttop < 0) $ttop = 0;

		print "<div id=\"boxGroup_$bgid\" style=\"position:absolute; top:" . $ttop . "px; width:1px; height:1px; background-color: " . BOX_BACKGROUND_COLOUR . ";opacity:.0;\"></div>";


		print "<div id=\"boxGroupBox_$bgid\" onclick=\"groupChange('$bgid');\" style=\"position:absolute; top:" . $crop['tly'] / $zoom . "px; left:" . $crop['tlx'] / $zoom . "px; width:" . ($crop['brx'] - $crop['tlx'] ) / $zoom . "px; height:" . ($crop['bry'] - $crop['tly'] ) / $zoom . "px; background-color: " . BOX_GROUP_BACKGROUND_COLOUR . ";opacity:" .  BOX_GROUP_BACKGROUND_OPACITY . "; visibility: $vis;\"></div>";


		print "<div><input type=\"checkbox\" name=\"bgid$bgid\" id=\"bgid$bgid\" style=\"opacity:0.0; \"/></div>";

		$vis = "hidden";
	}


	foreach($boxes as $bi)
	{
		$bid = $bi['bid'];

		//if (!isset($_SESSION['boxes'][$bid])) break;

		$box = $_SESSION['boxes'][$bid];

		$val = $_SESSION['boxes'][$bid]['val'];
		$bbgid = $_SESSION['boxes'][$bid]['bgid'];
		$btid = $_SESSION['boxes'][$bid]['btid'];

		$box = applytransforms($box,$row);

		if ($btid == 1) //single
		{
				if ($val == 0) {$checked = ""; $colour = BOX_BACKGROUND_COLOUR; } else {$checked = "checked=\"checked\""; $colour = BOX_SELECT_COLOUR;}
				print "<div><input type=\"checkbox\" name=\"bid$bid\" id=\"checkBox$bid\" value=\"$bid\" style=\"position:absolute; top:" . $box['tly'] / $zoom . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px; opacity:0.0; \" onclick=\"radioUpdate('$bid','$bbgid'); \" $checked onkeypress=\"checkEnter(event,$bbgid,$bid)\"/></div>";
				print "<div id=\"checkImage$bid\" onkeypress=\"checkEnter(event,$bbgid,$bid)\" onclick=\"radioChange('$bid','$bbgid'); \" style=\"position:absolute; top:" . $box['tly'] / $zoom . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px; background-color: $colour;opacity:" .  BOX_OPACITY . "; \"></div>";
	
		}
		else if ($btid == 2) //multiple
		{
	
				if ($val == 0) {$checked = ""; $colour = BOX_BACKGROUND_COLOUR; } else {$checked = "checked=\"checked\""; $colour = BOX_SELECT_COLOUR;}
				print "<div><input type=\"checkbox\" name=\"bid$bid\" id=\"checkBox$bid\" value=\"$bid\" style=\"position:absolute; top:" . $box['tly'] / $zoom . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px; opacity:0.0; \" onclick=\"checkUpdate('$bid','$bbgid'); \" $checked onkeypress=\"checkEnter(event,$bbgid,$bid)\" /></div>";
				print "<div id=\"checkImage$bid\" onkeypress=\"checkEnter(event,$bbgid,$bid)\" onclick=\"checkChange('$bid','$bbgid'); \" style=\"position:absolute; top:" . $box['tly'] / $zoom . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px; background-color: $colour;opacity:" .  BOX_OPACITY . ";  \"></div>";

		}
		else if ($btid == 3 || $btid == 4) //text or number
		{
			$maxlength = "maxlength=\"1\"";
			$onkeypress = "onkeypress=\"textPress(this,event,$bbgid,$bid)\"";

			if ($btid == 4)
			{
				if (!is_numeric($val)) $val = "";
			}

			$val = htmlspecialchars($val);
	
			print "<div><input type=\"text\" name=\"bid$bid\" id=\"textBox$bid\" value=\"$val\" $maxlength style=\"z-index: 1; position:absolute; top:" . (($box['tly'] / $zoom) + (($box['bry'] - $box['tly'] ) / $zoom)) . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px;\" onclick=\"\" onfocus=\"select()\" $onkeypress /></div>";

		
			print "<div id=\"textImage$bid\" style=\"position:absolute; top:" . $box['tly'] / $zoom . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px; background-color: " . BOX_BACKGROUND_COLOUR . "; text-align:center; font-weight:bold;\" onclick=\"textClick('$bid','$bbgid');\">$val</div>";
		}
		else if ($btid == 6 || $btid == 5)
		{
			$val = htmlspecialchars($val);
	
			print "<div><textarea name=\"bid$bid\" id=\"textBox$bid\" style=\"z-index: 1; position:absolute; top:" . (($box['tly'] / $zoom) + (($box['bry'] - $box['tly'] ) / $zoom)) . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px;\" onclick=\"\" onfocus=\"select()\" rows=\"20\" cols=\"80\">$val</textarea></div>";

		
			print "<div id=\"textImage$bid\" style=\"position:absolute; top:" . $box['tly'] / $zoom . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px; background-color: " . BOX_BACKGROUND_COLOUR . "; text-align:center; font-weight:bold;\" onclick=\"textClick('$bid','$bbgid');\">$val</div>";


		}
	}
	print "<div><input type=\"hidden\" name=\"piddone\" value=\"$pid\"/></div>";
	print "</form>";


}

session_start();

$vid = get_vid();

if($vid == false){ print T_("Please log in"); exit;}

$fid = get_fid($vid);


if (isset($_GET['centre']) && isset($_GET['fid']) && isset($_GET['pid']) )
{
	$pid = $_GET['pid'];

	$sql = "UPDATE formpages
		SET offx = 0, offy = 0, costheta = 1, sintheta = 0, scalex = 1, scaley = 1, `centroidy` = (SELECT height / 2 FROM pages WHERE pid = '$pid'), `centroidx` = (SELECT width / 2 FROM pages WHERE pid = '$pid') 
		WHERE fid = '$fid'
		AND pid = '$pid'";

	$db->Execute($sql);
}

if (!empty($fid))
{
	$qid_desc = get_qid_description($fid);
	$qid = $qid_desc['qid'];
	$description = $qid_desc['description'];
}

if (isset($_POST['complete']) && isset($_SESSION['boxes']))
{

	
	foreach($_SESSION['boxes'] as $key => $box)
	{

		$sql = "";
		if ($box['btid'] == 1 || $box['btid'] == 2)
    {
      //delete old data
      if (DELETE_ON_VERIFICATION)
      {
        $db->Execute("DELETE FROM formboxverifychar WHERE vid = 0 AND bid = '$key' AND fid = '$fid'");
      }

      if ($box['val'] > 0)
      {
  			$sql = "INSERT INTO formboxverifychar (`vid`,`bid`,`fid`,`val`) VALUES ('$vid','$key','$fid','1')";
      }
		}
		if ($box['btid'] == 3 || $box['btid'] == 4)
		{
      //delete old data
      if (DELETE_ON_VERIFICATION)
      {
        $db->Execute("DELETE FROM formboxverifychar WHERE vid = 0 AND bid = '$key' AND fid = '$fid'");
      }

			if ($box['val'] == "" || $box['val'] == " ")
			{
				//$sql = "INSERT INTO formboxverifychar (`vid`,`bid`,`fid`,`val`) VALUES ('$vid','$key','$fid',NULL)";
			}else
			{
				$bval = $db->qstr($box['val']);
				$sql = "INSERT INTO formboxverifychar (`vid`,`bid`,`fid`,`val`) VALUES ('$vid','$key','$fid',$bval)";
			}
		}
		if ($box['btid'] == 6 || $box['btid'] == 5)
		{
			if ($box['val'] == "" || $box['val'] == " ")
			{
				//$sql = "INSERT INTO formboxverifytext (`vid`,`bid`,`fid`,`val`) VALUES ('$vid','$key','$fid',NULL)";
			}else
			{
				$bval = $db->qstr($box['val']);
				$sql = "INSERT INTO formboxverifytext (`vid`,`bid`,`fid`,`val`) VALUES ('$vid','$key','$fid',$bval)";
			}

    }
    if ($sql != "")
    {
  		$db->Execute($sql);
    }

    //Delete unneeded box data
    if (DELETE_ON_VERIFICATION)
    {
      $sql = "DELETE IGNORE FROM formboxes WHERE fid = '$fid' AND bid = '$key'";
      $db->Execute($sql);
    }

		//print "$sql</br>";
	}

	//make sure worklog and update occurs at the same time
	$db->StartTrans();

  $sql = "UPDATE forms
		SET done = 1, assigned = FROM_UNIXTIME({$_SESSION['assigned']}), completed = NOW()
		WHERE assigned_vid = '$vid'
		AND fid = '$fid'
		AND done = 0";

	$db->Execute($sql);

	unset($_SESSION['boxgroups']);
	unset($_SESSION['pages']);
	unset($_SESSION['boxes']);
	session_unset();

	$sql = "UPDATE verifiers
		SET currentfid = NULL
		WHERE vid = '$vid'";

	//print "$sql</br>";
	$db->Execute($sql);

	$db->CompleteTrans();

	//if XMLRPC is set - upload this form via XMLRPC
	$sql = "SELECT rpc_server_url 
		FROM questionnaires
		WHERE qid = '$qid'";

	$rpc = $db->GetRow($sql);

	if (isset($rpc['rpc_server_url']) && !empty($rpc['rpc_server_url']))
	{
		//upload form via RPC
		include_once("functions/functions.output.php");
		uploadrpc($fid);
	}
	
	$fid = false;
}


if (isset($_GET['review']))
{
	foreach($_SESSION['boxgroups'] as $key => $val)
	{
		$_SESSION['boxgroups'][$key]['done'] = 0;
	}
}

if (isset($_GET['clear']))
{
	unset($_SESSION['boxgroups']);
	unset($_SESSION['pages']);
	unset($_SESSION['boxes']);
	session_unset();
}

if (isset($_POST['assign']))
{
	session_unset();
	$fid = assign_to($vid);
	if ($fid == false) 
	{
    xhtml_head(T_("Verify: No more work"),true,false,false,"onload='document.form1.assign.focus();'");
		print "<p>" . T_("NO MORE WORK") . "</p>";
		print "<form name=\"form1\" action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\"><input type=\"submit\" name=\"assign\" value=\"" . T_("Check for more work") . "\"/></form>";
		unset($_SESSION['boxgroups']);
		unset($_SESSION['boxes']);
		unset($_SESSION['pages']);	
		session_unset();
		xhtml_foot();
		exit();
	}
	//set assigned time session variable
	$_SESSION['assigned'] = time();
}

if ($fid == false)
{
	xhtml_head(T_("Verify: Assign form"),true,array("css/table.css"),false,"onload='document.form1.assign.focus();'");
	print "<div id=\"links\">";
	print "<p>" . T_("There is no form currently assigned to you") . "</p>";
//	print "<p><a href=\"" . $_SERVER['PHP_SELF'] . "?assign=assign\" onclick=\"document.getElementById('links').style.visibility='hidden'; document.getElementById('wait').style.visibility='visible';\">" . T_("Assign next form") . "</a></p>";
  print "<form name=\"form1\" action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\"><input type=\"submit\" name=\"assign\" onclick=\"document.getElementById('links').style.visibility='hidden'; document.getElementById('wait').style.visibility='visible';\"  value=\"" . T_("Assign next form") . "\"/></form>";
	print "</div>";
	print "<div id=\"wait\" style=\"visibility: hidden;\">
<p>" .  T_("Assigning next form: Please wait...") . "</p>
</div>";

	
	//display performance information for each assigned questionnaire
	$sql = "SELECT vq.qid, q.description 
		FROM verifierquestionnaire as vq, questionnaires as q
		WHERE vq.vid = '$vid'
		AND q.qid = vq.qid";

	$prs = $db->GetAll($sql);

	foreach($prs as $pr)
	{
		$pqid = $pr['qid'];
		$pdes = $pr['description'];

		$sql = "SELECT count(*) as rem
			FROM forms
			WHERE qid = '$pqid'
			AND done = 0";

		$remain = $db->GetOne($sql);

		$sql = "SELECT q.description as qu, v.description as ve,f.qid,f.assigned_vid as vid , count( * ) AS c, count( * ) / ( SUM( TIMESTAMPDIFF(
			SECOND , f.assigned, f.completed ) ) /3600 ) AS CPH, (
			(
			
			SELECT count( pid )
			FROM pages
			WHERE qid = f.qid
			) * count( * )
			) / ( SUM( TIMESTAMPDIFF(
			SECOND , f.assigned, f.completed ) ) /3600 ) AS PPH
			FROM forms AS f
			JOIN questionnaires as q on (f.qid = q.qid)
			JOIN verifiers as v on (v.vid = f.assigned_vid)
			WHERE f.qid = '$pqid'
			GROUP BY f.qid, f.assigned_vid
			ORDER BY CPH DESC";

		$prss = $db->GetAll($sql);

		print "<h3>$pdes</h3>";
		xhtml_table($prss,array('ve','c','CPH','PPH'),array(T_("Operator"),T_("Completed Forms"),T_("Completions Per Hour"),T_("Pages Per Hour")),"tclass",array
("vid" => $vid));
		print "<p>" . T_("Remain to verify") . ": $remain</p>";
	}
	


	xhtml_foot();
	exit();
}

$qid_desc = get_qid_description($fid);
$qid = $qid_desc['qid'];
$description = $qid_desc['description'];

if (!isset($_SESSION['boxes'])) {
	//nothing yet known about this form
	
	$sql = "SELECT b.bid as bid, b.tlx as tlx, b.tly as tly, b.brx as brx, b.bry as bry, b.pid as pid, bg.btid as btid, b.bgid as bgid, $fid as fid, bg.sortorder as sortorder, fb.filled, CASE WHEN d.fid IS NOT NULL THEN d.val ELSE c.val END as val
		FROM boxes AS b
		JOIN boxgroupstype as bg ON (bg.bgid = b.bgid AND bg.btid > 0)
    JOIN pages as p ON (p.pid = b.pid AND p.qid = '$qid')
    LEFT JOIN formboxes as fb ON (fb.bid = b.bid AND fb.fid = '$fid')
		LEFT JOIN formboxverifychar AS c ON (c.fid = '$fid' AND c.vid = 0 AND c.bid = b.bid)
		LEFT JOIN formboxverifytext AS d ON (d.fid = '$fid' AND d.vid = 0 AND d.bid = b.bid)
		ORDER BY bg.sortorder ASC";

	
	$sql2 = "SELECT b.bgid,0 as done,b.pid,bg.varname,bg.btid
		FROM boxes as b, boxgroupstype as bg, pages as p
		WHERE p.pid = b.pid
		AND bg.bgid = b.bgid
		AND p.qid = '$qid' 
		AND bg.btid > 0
		GROUP BY bg.bgid
		ORDER BY bg.sortorder ASC";

	$sql3 = "SELECT b.pid,b.bgid,0 as done, fp.width, fp.height, fp.fid
		FROM boxes as b
		JOIN pages as p ON (p.qid = '$qid' AND b.pid = p.pid)
		JOIN boxgroupstype as bg ON (bg.bgid = b.bgid)
		LEFT JOIN formpages as fp ON (fp.fid = '$fid' AND fp.pid = p.pid)
		GROUP BY b.pid
		ORDER BY bg.sortorder ASC";

	$a = $db->GetAssoc($sql);
	if (empty($a)) 
	{
    xhtml_head(T_("Verify: No more work"),true,false,false,"onload='document.form1.assign.focus();'");
		print "<p>" . T_("NO MORE WORK") . "</p>";
		print "<form name=\"form1\" action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\"><input type=\"submit\" name=\"assign\" value=\"" . T_("Check for more work") . "\"/></form>";
		//print "<p><a href=\"" . $_SERVER['PHP_SELF'] . "?assign=assign\">" . T_("Check for more work") . "</a></p>";
		unset($_SESSION['boxgroups']);
		unset($_SESSION['pages']);
		unset($_SESSION['boxes']);
		session_unset();
		xhtml_foot();
		exit();
	}

	$b = $db->GetAssoc($sql2);
	$c = $db->GetAssoc($sql3);


	$_SESSION['boxes'] = $a;
	$_SESSION['boxgroups'] = $b;
	$_SESSION['pages'] = $c;
	$_SESSION['assigned'] = time();


  if (SINGLE_CHOICE_AUTOMATIC_VERIFICATION)
  {
  
    //see if any boxes should be automatically marked as verified
  
    //search for single choice boxes (btid == 1), within box groups where > 1 box is available
    //if there is one and only one box within the filled range, and val is set as 1, then mark as done
  
    $tmpt = current($a);
    //set to first bgid
    $tmpbgid = $tmpt['bgid'];
    $tmpgroup = array();
    foreach($_SESSION['boxes'] as $key => $val)
    {
      if ($val['bgid'] != $tmpbgid)
      { 
        //check the number of boxes in this group that fall within the restrictions
        $within = 0;
        $withinkey = 0;
        $withincount = 0;
        foreach($tmpgroup as $tkey => $tval)
        {
          if ($tval['filled'] < SINGLE_CHOICE_MIN_FILLED && $tval['filled'] > SINGLE_CHOICE_MAX_FILLED)
          {
            $within++;
            $withinkey = $tkey;
          }
          $withincount++;
        }
  
        //if one box within and also this is the selected box - mark this box group as done
        if ($withincount > 1 && $within == 1 && $_SESSION['boxes'][$withinkey]['val'] == 1)
        {
          $_SESSION['boxgroups'][$_SESSION['boxes'][$withinkey]['bgid']]['done'] = 1;
        }
  
        $tmpbgid = $val['bgid'];
        $tmpgroup = array();
      }
  
      //only for single choice boxes
      if ($val['btid'] == 1)
      {
        $tmpgroup[$key] = $val;
      }
    }
  }
}


//form data already here

//if data submitted, store it to local session
if (isset($_POST['piddone']))
{
	$pid = intval($_POST['piddone']);

	foreach($_POST as $getkey => $getval)
	{
		//print "SUBMIT Key: $getkey Val: $getval<br/>";
		if (strncmp($getkey,'bgid',4) == 0)
		{
			$bgid = intval(substr($getkey,4));
			if ($getval == "on") $getval = 1;
			$_SESSION['boxgroups'][$bgid]['done'] = $getval;

			//destroy existing data in this box group...
			$sql = "SELECT bid
				FROM boxes
				WHERE bgid = '$bgid'";
		
			$b = $db->GetAll($sql);

			foreach($b as $bb)
			{
				$_SESSION['boxes'][$bb['bid']]['val'] = "";
			}



		}
	}


	//store retrieved data
	foreach($_POST as $getkey => $getval)
	{
		//print "SUBMIT Key: $getkey Val: $getval<br/>";
		if (strncmp($getkey,'bid',3) == 0)
		{
			$bid = intval(substr($getkey,3));
			$_SESSION['boxes'][$bid]['val'] = $getval;
		}
	}


}

$bgid = "";
$pid = "";
$destroypage = 0;

//move to a specific page
if (isset($_GET['pid']))
{
	$pid = intval($_GET['pid']);
	//destroy "done" for this page
	$destroypage = 1;
}
else
{
	//get next page to work on
	foreach($_SESSION['boxgroups'] as $key => $val)
	{
		if ($val['done'] == 0)
		{
			$bgid = $key;
			break;
		}
	}
}


if ($bgid != "")
{
	$sql = "SELECT pid
		FROM boxes
		WHERE bgid = '$bgid'";
	
	$bggg = $db->GetRow($sql);
	
	$pid = $bggg['pid'];
}
else if ($pid == "") 
{
	//we are done
//	xhtml_head(T_("Verify: Done"));
  xhtml_head(T_("Verify: Done"),true,false,false,"onload='document.form1.complete.focus();'");
	print "<p>" . T_("The required fields have been filled") . "</p>";
	print "<div id=\"links\">";
  print "<form name=\"form1\" action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\"><input type=\"submit\" name=\"complete\" onclick=\"document.getElementById('links').style.visibility='hidden'; document.getElementById('wait').style.visibility='visible';\"  value=\"" . T_("Submit completed form to database") . "\"/></form>";
//	print "<p><a href=\"" . $_SERVER['PHP_SELF'] . "?complete=complete\" onclick=\"document.getElementById('links').style.visibility='hidden'; document.getElementById('wait').style.visibility='visible';\">" . T_("Submit completed form to database") . "</a></p>";
	print "<p><a href=\"" . $_SERVER['PHP_SELF'] . "?review=review#boxGroup\" onclick=\"document.getElementById('links').style.visibility='hidden'; document.getElementById('wait').style.visibility='visible';\">" . T_("Review all questions again") . "</a></p>";
	print "<p><a href=\"" . $_SERVER['PHP_SELF'] . "?clear=clear#boxGroup\" onclick=\"document.getElementById('links').style.visibility='hidden'; document.getElementById('wait').style.visibility='visible';\">" . T_("Clear all entered data and review again") . "</a></p></div>";

	print "<div id=\"wait\" style=\"visibility: hidden;\"><p>" .  T_("Submitting: Please wait...") . "</p></div>";
	xhtml_foot();

	exit();
}	
	



print "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title><?php echo T_("Verifier"); ?> - <?php print "QID:$qid FID:$fid DESC:$description"; ?></title>
<script type="text/javascript">

/* <![CDATA[ */

var bgiddone = new Array();
var bgidbid = new Array();
var bgidtype = new Array();
var curbgid = 0;
var pagedone = 0;
var newwindow;

<?php

//print order variable
$sql = "SELECT boxgroupstype.bgid
	FROM boxgroupstype
	JOIN boxes ON boxes.bgid = boxgroupstype.bgid
	WHERE boxgroupstype.pid = '$pid'
	GROUP BY boxgroupstype.bgid
	ORDER BY boxgroupstype.sortorder ASC";
		
$b = $db->GetAll($sql);
			
print "bgidorder = new Array(";
		
$s = "";
		
foreach($b as $bb)
{
	$s .= "'{$bb['bgid']}',";
}
		
$s = substr($s,0,strlen($s) - 1);
		
print "$s);\n";



//print array of done/not done box groups for this page
//print all bgid box groups for this page containing a list of boxes in that box group
foreach($_SESSION['boxgroups'] as $key => $val)
{
	if ($val['pid'] == $pid)
	{
		if ($val['done'] == 0 || $destroypage == 1)
			print "bgiddone[$key] = 0;\n";
		else
			print "bgiddone[$key] = 1;\n";

		print "bgidtype[$key] = {$val['btid']};\n";

		$sql = "SELECT bid
			FROM boxes
			WHERE bgid = '$key'";

		$b = $db->GetAll($sql);
	
		print "bgidbid[$key] = new Array(";

		$s = "";

		foreach($b as $bb)
		{
			$s .= "'{$bb['bid']}',";
		}


		$s = substr($s,0,strlen($s) - 1);
	
		print "$s);\n";




	}
}
?>

function allDone()
{
        for (var i=0; i < bgidorder.length; i++)
        {
                x = bgidorder[i];
                bgiddone[x] = 1;
                document.getElementById('bgid' + x ).checked = 'checked';
                document.getElementById('bgid' + x ).val = '1';
        }
        document.forms[0].submit();
}



function nextTask()
{
	var done = 0;
	var focusdone = 0;

	for (var i=0; i < bgidorder.length; i++)
	{
		x = bgidorder[i];
		document.getElementById('boxGroupBox_' + x ).style.visibility = 'hidden';

		if (bgidtype[x] == 3 || bgidtype[x] == 4 || bgidtype[x] == 5 || bgidtype[x] == 6)
		{	
			for (y in bgidbid[x])
			{
				document.getElementById('textImage' + bgidbid[x][y]).style.visibility = 'visible';
				document.getElementById('textBox' + bgidbid[x][y]).style.visibility = 'hidden';
				document.getElementById('textImage' + bgidbid[x][y]).innerHTML = document.getElementById('textBox' + bgidbid[x][y]).value;
			}
		}

		if (bgiddone[x] == 0 && done == 0)
		{
			curbgid = x;

			if (bgidtype[x] == 3 || bgidtype[x] == 4 || bgidtype[x] == 5 || bgidtype[x] == 6)
			{	
				for (y in bgidbid[x])
				{
					document.getElementById('textImage' + bgidbid[x][y]).style.visibility = 'hidden';
					document.getElementById('textBox' + bgidbid[x][y]).style.visibility = 'visible';
					if (focusdone == 0)
					{
						focusText(bgidbid[x][y]);
						focusdone = 1;
					}

				}
			}else
			{
				if (focusdone == 0)
				{
					focusRadio();
					focusdone = 1;
				}
			}


			document.getElementById('boxGroupBox_' + x ).style.visibility = 'visible';
			document.getElementById('content').scrollTop = document.getElementById('boxGroupBox_' + x).offsetTop - <?php echo DISPLAY_GAP;?>;
		 	done = 1;
		}
	}

	if (done == 0)
	{
		//if (pagedone == 1)
			document.forms[0].submit();
		//else
		//	pagedone = 1;
	}



}

function previous() {

	if (curbgid == 0) return;

	prev = 0;

	for (var i=0; i < bgidorder.length; i++)
	{
		x = bgidorder[i];
		if (x == curbgid) break;
		prev = x;
	}

	if (prev == 0) return;

	bgiddone[prev] = 0;
}


function detectEvent(e) {
	var evt = e || event;

	if (evt.ctrlKey)
	{
		previous();
		nextTask();
		return false;
	}

	if (evt.keyCode == 91 || evt.keyCode == 92 || evt.keyCode == 113)
	{
		images = document.getElementsByTagName('img');
		poptastic(images[0].src + '&zoom');
		return false;
	}

	if(evt.keyCode != 13){ //if generated character code is equal to ascii 13 (if enter key)
		return document.defaultAction;
		
	}


	if (curbgid != 0)
	{
		bgiddone[curbgid] = 1;
		document.getElementById('bgid' + curbgid ).checked = 'checked';
		document.getElementById('bgid' + curbgid ).val = '1';
	}

	nextTask();

	return false;
}


function focusRadio()
{
	//alert('curbgid: ' + curbgid + ' bgidbid: ' + bgidbid[curbgid]);
	document.getElementById('checkBox' + bgidbid[curbgid][0]).focus();
	document.getElementById('checkBox' + bgidbid[curbgid][0]).select();

	for (y in bgidbid[curbgid])
	{
		z = bgidbid[curbgid][y];

		box = document.getElementById('checkBox' + z);
		image = document.getElementById('checkImage' + z);

		if (box.checked)
		{
			box.focus();
			box.select();
		}
	}


}



function checkFocus(bid,bgid) {

	if (curbgid != bgid)
	{
		//goto selected bgid	
		bgiddone[bgid] = 0;
		nextTask();
		return;
	}


	for (x in bgidbid[bgid])
	{
		x = bgidbid[bgid][x];

		box = document.getElementById('checkBox' + x);
		image = document.getElementById('checkImage' + x);

		if (x == bid)
		{
			box.focus();
			if (box.checked)
			{
				image.style.backgroundColor='<?php echo BOX_SELECT_COLOUR; ?>';
			} else {
				image.style.backgroundColor='<?php echo BOX_FOCUS_COLOUR; ?>';
			}
		} else {
			if (box.checked)
			{
				image.style.backgroundColor='<?php echo BOX_SELECT_COLOUR; ?>';
			} else {
				image.style.backgroundColor='<?php echo BOX_BACKGROUND_COLOUR; ?>';
			}
	
		}
	}

}


function groupChange(bgid) {

	if (curbgid != bgid)
	{
		//goto selected bgid	
		bgiddone[bgid] = 0;
		nextTask();
		return;
	}

	//else do nothing
	return;

}


function radioChange(bid,bgid) {

	if (curbgid != bgid)
	{
		//goto selected bgid	
		bgiddone[bgid] = 0;
		nextTask();
		return;
	}


	for (x in bgidbid[bgid])
	{
		x = bgidbid[bgid][x];

		box = document.getElementById('checkBox' + x);
		image = document.getElementById('checkImage' + x);

		if (x == bid)
		{
			if (box.checked)
			{
				box.checked = '';
				image.style.backgroundColor='<?php echo BOX_BACKGROUND_COLOUR; ?>';
			} else {
				box.checked = 'checked';
				image.style.backgroundColor='<?php echo BOX_SELECT_COLOUR; ?>';
				box.focus();
			}
		} else {

			box.checked = '';
			image.style.backgroundColor='<?php echo BOX_BACKGROUND_COLOUR; ?>';
		}
	}

}

function radioUpdate(bid,bgid) {

	for (x in bgidbid[bgid])
	{
		x = bgidbid[bgid][x];

		box = document.getElementById('checkBox' + x);
		image = document.getElementById('checkImage' + x);


		if (x == bid)
		{
			if (box.checked)
			{
				box.checked = 'checked';
				image.style.backgroundColor='<?php echo BOX_SELECT_COLOUR; ?>';
			} else {
				box.checked = '';
				image.style.backgroundColor='<?php echo BOX_BACKGROUND_COLOUR; ?>';
			}
		} else {
			box.checked = '';
			image.style.backgroundColor='<?php echo BOX_BACKGROUND_COLOUR; ?>';
		}
	}

}

//change the checkbox status and the replacement image
function checkChange(bid,bgid) {

	if (curbgid != bgid)
	{
		//goto selected bgid
		bgiddone[bgid] = 0;
		nextTask();		
		return;
	}


	box = document.getElementById('checkBox' + bid);
	image = document.getElementById('checkImage' + bid);

	if(box.checked) {
		box.checked = '';
		image.style.backgroundColor='<?php echo BOX_BACKGROUND_COLOUR; ?>';
	} else {
		box.checked = 'checked';
		image.style.backgroundColor='<?php echo BOX_SELECT_COLOUR; ?>';
		box.focus();
	}
}


//change the checkbox status and the replacement image
function textClick(bid,bgid) {

	if (curbgid != bgid)
	{
		//goto selected bgid	
		bgiddone[bgid] = 0;
		nextTask();
		return;
	}


}



function checkUpdate(bid,bgid) {

	box = document.getElementById('checkBox' + bid);
	image = document.getElementById('checkImage' + bid);

	if(box.checked) {
		image.style.backgroundColor='<?php echo BOX_SELECT_COLOUR; ?>';
		box.focus();
	} else {
		image.style.backgroundColor='<?php echo BOX_BACKGROUND_COLOUR; ?>';
	}


}



function checkEnter(e,bgid,bid){ //e is event object passed from function invocation
	var characterCode //literal character code will be stored in this variable
	var whi = 0;
	var current = 0;
	var next = 0;
	var prev = 0;
	var select = 0;

	if (e.keyCode == 16) return false; //ignore uppercase/shift

	characterCode = e.keyCode; //character code is contained in IE's keyCode property
	whi = e.which;
		//alert(e.which);

	if (whi >= 49 && whi <= 57) //keys 1-9 select appropriate box
	{
		cv = 0;
		for (y in bgidbid[bgid])
		{
			select = bgidbid[bgid][y];
			if (cv == (whi - 49))
			{
				break;
			}
			cv++;
		}
	
		if (bgidtype[bgid] == 1)
		{
			radioChange(select,bgid);
		}
		else if (bgidtype[bgid] == 2)
		{
			checkChange(select,bgid);
		}
	
		return true;
	}

	for (y in bgidbid[bgid])
	{
		if (current != 0)
		{
			next = bgidbid[bgid][y];
			break;
		}
				
		if (bgidbid[bgid][y] == bid)
		{
			current = bid;
		}else
		{
			prev = bgidbid[bgid][y];
		}
	}

	if (next == 0) next = current;
	if (prev == 0) prev = current;

	//alert('next: ' + next + ' current: ' + current + ' prev: ' + prev + ' bgid: ' + bgid + ' ccode: ' + characterCode);

	if(characterCode == 39 || characterCode == 40){ 
		checkFocus(next,bgid);
	}else if (characterCode == 37 || characterCode == 38){
		checkFocus(prev,bgid);	
	}


	return true;
}



function textPress(th,e,bgid,bid){ //e is event object passed from function invocation
	var characterCode //literal character code will be stored in this variable
	var current = 0;
	var next = 0;
	var prev = 0;

	if (e.keyCode == 16) return false; //ignore uppercase/shift
	if (e.keyCode == 13) return false; //ignore uppercase/shift

	characterCode = e.keyCode //character code is contained in IE's keyCode property


	for (y in bgidbid[bgid])
	{
		if (current != 0)
		{
			next = bgidbid[bgid][y];
			break;
		}
				
		if (bgidbid[bgid][y] == bid)
		{
			current = bid;
		}else
		{
			prev = bgidbid[bgid][y];
		}
	}

	if (next == 0) next = current;
	if (prev == 0) prev = current;

	if(characterCode >= 37 && characterCode <= 40){ //if generated character code is equal to ascii 13 (if enter key)
		//
	}
	else if (characterCode == 8){
		focusText(prev);
	}
	else
	{
		focusText(next);
	}

	return true;
}

function focusText(field)
{
	if (document.getElementById('textBox'+field))
	{
		document.getElementById('textBox'+field).focus();
		document.getElementById('textBox'+field).select();
	}
}

function poptastic(url)
{
	newwindow=window.open(url,'name','height=600,width=350,resizable=yes,scrollbars=yes,toolbar=no,status=no');
	if (window.focus) {newwindow.focus()}
}


function init() {
	document['onkeydown'] = detectEvent;
	nextTask();
//	focusText(0);

//	for(var i=0; i < inputs.length; i++)
//	{
//		if (inputs[i].checked)
//		{
//			inputs[i].focus();
//		}
//	}

}


window.onload = init;

/* ]]> */
</script>
<style type="text/css">
#topper {
  position : fixed;
  width : 100%;
  height : 5%;
  top : 0;
  right : 0;
  bottom : auto;
  left : 0;
  border-bottom : 2px solid #cccccc;
  overflow : auto;
	text-align:center;
}

#header {
  position : fixed;
  width : 15%;
  height : 95%;
  top : 5%;
  right : 0;
  bottom : auto;
  left : 0;
  border-bottom : 2px solid #cccccc;
  overflow : auto;
}
#content {
  position : fixed;
  top : 5%;
  left : 15%;
  bottom : auto;
  width : 85%;
  height : 100%;
  color : #000000;
  overflow : auto;
}

#note {
  width : 100%;
  height : 200px;
}
.embeddedobject {
  width:100%;
  height:100%;
}


</style>
</head>
<body>



<?php

$zoom = 1;
if (isset($_GET['zoom'])) $zoom = intval($_GET['zoom']);


print "<div id=\"content\">";

if ($pid == "")
{
	//no more to do:
	print "<p>" . T_("The required fields have been filled") . "</p>";
	print "<div id=\"links\">";
	print "<p><a href=\"" . $_SERVER['PHP_SELF'] . "?complete=complete\" onclick=\"document.getElementById('links').style.visibility='hidden'; document.getElementById('wait').style.visibility='visible';\">" . T_("Submit completed form to database") . "</a></p>";
	print "<p><a href=\"" . $_SERVER['PHP_SELF'] . "?review=review#boxGroup\" onclick=\"document.getElementById('links').style.visibility='hidden'; document.getElementById('wait').style.visibility='visible';\">" . T_("Review all questions again") . "</a></p>";
	print "<p><a href=\"" . $_SERVER['PHP_SELF'] . "?clear=clear#boxGroup\" onclick=\"document.getElementById('links').style.visibility='hidden'; document.getElementById('wait').style.visibility='visible';\">" . T_("Clear all entered data and review again") . "</a></p></div>";

	print "<div id=\"wait\" style=\"visibility: hidden;\">
<p>" . T_("Submitting: Please wait...") . "</p>
</div>";

}
else
{
	
	//show content
	if (empty($_SESSION['pages'][$pid]['fid'])) //if page missing
	{
		print "<div style=\"position:relative;\"><div style=\"width:" . PAGE_WIDTH / (PAGE_WIDTH/DISPLAY_PAGE_WIDTH) . "px; height:" . PAGE_HEIGHT / (PAGE_WIDTH/DISPLAY_PAGE_WIDTH) . "px;\">" . T_("Page is missing from scan") . "</div>";
		$pw =PAGE_WIDTH;
	}
	else
	{
		print "<div style=\"position:relative;\"><img src=\"showpage.php?pid=$pid&amp;fid=$fid\" style=\"width:" . DISPLAY_PAGE_WIDTH . "px;\" alt=\"" . T_("Image of page") . " $pid, " . T_("form") . " $fid\" />";
		$pw = $_SESSION['pages'][$pid]['width'];
		if (empty($pw)) $pw = PAGE_WIDTH;
	}
	bgidtocss(($pw/DISPLAY_PAGE_WIDTH),$fid,$pid);
	print "</div>";
	print "</div>";

	//show list of bgid for this fid
	print "<div id=\"header\">";
	
	print "<p>Q:$qid F:$fid P:$pid</p>";
	print "<p><a href=\"" . $_SERVER['PHP_SELF'] . "?pid=$pid&amp;fid=$fid&amp;centre=centre\">" . T_("Centre Page") . "</a></p>";
	print "<p><a href=\"javascript:void(0)\" onclick=\"allDone();\">" . T_("Accept page") . "</a></p>";

	print "<div id='note'><object class='embeddedobject' id='mainobj' data='pagenote.php?pid=$pid&amp;fid=$fid&amp;vid=$vid' standby='" . T_("Loading panel...") . "' type='application/xhtml+xml'><div>" . T_("Error, try with Firefox") . "</div></object></div>";
	
	foreach($_SESSION['boxgroups'] as $key => $val)
	{
		if ($val['pid'] == $pid)
		{
			//if ($bgid == $key)
				print "<strong>{$val['varname']}</strong><br/>";
			//else
			//	print "<a id=\"link$key\" href=\"" . $_SERVER['PHP_SELF'] . "?bgid=$key&amp;fid=$fid#boxGroup\">{$val['varname']}</a><br/>";
		}	
	}
	
print "</div>";

//show list of pid for this fid
	print "<div id=\"topper\">";


	//print_r($_SESSION['pages']);

	$count = 1;	
	foreach($_SESSION['pages'] as $key => $val)
	{
		if ($pid == $key)
			print "<strong>$count</strong>";
		else
			print " <a href=\"" . $_SERVER['PHP_SELF'] . "?pid=$key&amp;fid=$fid#boxGroup\">$count</a> ";
		$count++;

	}
	
print "</div>";


}


?>


</body></html>




