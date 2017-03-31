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


include_once("config.inc.php");
include_once("db.inc.php");
include_once("functions/functions.database.php");
include_once("functions/functions.image.php");
				
global $db;


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

  $sql = "SELECT CASE WHEN done = 1 THEN assigned_vid ELSE assigned_vid2 END AS assigned_vid
          FROM forms
          WHERE fid = '$fid'";

  $vid = $db->GetOne($sql);

	$sql = "(SELECT b.bid,f.val,b.bgid,g.btid,g.sortorder,b.tly,b.tlx,b.bry,b.brx
    FROM boxes as b
    JOIN boxgroupstype as g ON (b.bgid = g.bgid AND g.btid > 0 AND g.btid < 5)
    LEFT JOIN formboxverifychar as f ON (f.bid = b.bid AND f.vid = '{$vid}' and f.fid = '{$fid}')
    WHERE b.pid = '$pid'
    )
		UNION
		(SELECT b.bid,f.val,b.bgid,g.btid,g.sortorder,b.tly,b.tlx,b.bry,b.brx
		FROM boxes as b
		JOIN  boxgroupstype as g on (b.bgid = g.bgid and g.btid IN (5,6))
    LEFT JOIN formboxverifytext as f on (f.bid = b.bid and f.vid = '{$vid}' and f.fid = '{$fid}')
    WHERE b.pid = '$pid')
    ORDER BY sortorder asc,bid asc";

    $boxes = $db->GetAssoc($sql);
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

	foreach($boxes as $bid => $rest)
	{
		$box = $rest;

		$val = $rest['val'];
		$bbgid = $rest['bgid'];
		$btid = $rest['btid'];

		$box = applytransforms($box,$row);

		if ($btid == 1) //single
		{
				if ($val == 0) {$checked = ""; $colour = BOX_BACKGROUND_COLOUR; } else {$checked = "checked=\"checked\""; $colour = BOX_SELECT_COLOUR;}
				print "<div id=\"checkImage$bid\" style=\"position:absolute; top:" . $box['tly'] / $zoom . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px; background-color: $colour;opacity:" .  BOX_OPACITY . "; \"></div>";
	
		}
		else if ($btid == 2) //multiple
		{
	
				if ($val == 0) {$checked = ""; $colour = BOX_BACKGROUND_COLOUR; } else {$checked = "checked=\"checked\""; $colour = BOX_SELECT_COLOUR;}
				print "<div id=\"checkImage$bid\" style=\"position:absolute; top:" . $box['tly'] / $zoom . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px; background-color: $colour;opacity:" .  BOX_OPACITY . ";  \"></div>";

		}
		else if ($btid == 3 || $btid == 4) //text or number
		{
			$maxlength = "maxlength=\"1\"";

			if ($btid == 4)
			{
				if (!is_numeric($val)) $val = "";
			}

			$val = htmlspecialchars($val);
	
			print "<div id=\"textImage$bid\" style=\"position:absolute; top:" . $box['tly'] / $zoom . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px; background-color: " . BOX_BACKGROUND_COLOUR . "; text-align:center; font-weight:bold;\">$val</div>";
		}
		else if ($btid == 6 || $btid == 5)
		{
			$val = htmlspecialchars($val);
		
			print "<div id=\"textImage$bid\" style=\"position:absolute; top:" . $box['tly'] / $zoom . "px; left:" . $box['tlx'] / $zoom . "px; width:" . ($box['brx'] - $box['tlx'] ) / $zoom . "px; height:" . ($box['bry'] - $box['tly'] ) / $zoom . "px; background-color: " . BOX_BACKGROUND_COLOUR . "; text-align:center; font-weight:bold;\">$val</div>";


		}
	}


}


$fid = "";
$pid = "";
$var = "";

if (isset($_GET['fid']))
{
	$fid = $_GET['fid'];
}


$q = get_qid_description($fid);

if (!isset($q['qid']))
	$qid = "";
else
	$qid = $q['qid'];

if (isset($_GET['pid']))
{
	$pid = intval($_GET['pid']);
}

if (isset($_GET['var']))
{
	$var = $_GET['var'];
	$vars = $db->qstr($_GET['var']);

	$sql = "SELECT b.pid
		FROM boxgroupstype as b, pages as p
		WHERE b.varname LIKE $vars
		AND p.pid = b.pid
		AND p.qid = '$qid'";

	$v = $db->GetRow($sql);
	
	if (isset($v['pid']))
	{
		$pid = $v['pid'];
	}
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title><?php echo T_("Review Form"); ?> - <?php print "FID:$fid"; ?></title>
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

</style>
</head>
<body>



<?php



//show content
//
//
print "<div id=\"content\">";
		print "<div style=\"position:relative;\"><img src=\"showpage.php?pid=$pid&amp;fid=$fid\" style=\"width:" . DISPLAY_PAGE_WIDTH . "px;\" alt=\"" . T_("Image of page") . " $pid, " . T_("form") . " $fid\" />";
print "<div id=\"overlay\">";
bgidtocss((PAGE_WIDTH/DISPLAY_PAGE_WIDTH),$fid,$pid);
	print "</div>";
	print "</div>";
	print "</div>";


//show list of bgid for this fid
print "<div id=\"header\">";
	print "<p>F:$fid</p>";

?>

	<form action="" method="get">
	<div><?php echo T_("Form:"); ?> <input type="text" size="5" name="fid" value="<?php echo $fid ?>"/>
		<?php echo T_("Variable:"); ?> <input type="text" size="9" name="var" value="<?php echo $var ?>"/>
		<?php echo T_("Page:"); ?> <input type="text" size="4" name="pid" value="<?php echo $pid ?>"/>
		<input type="submit"/></div>
	</form>

  <p>
  <script type="text/javascript">
  function toggleoverlay() {
      var x = document.getElementById('overlay');
      if (x.style.display === 'none') {
          x.style.display = 'block';
      } else {
          x.style.display = 'none';
      }
}
  </script>
    <button onclick="toggleoverlay()"><?php echo T_("Toggle overlay");?></button>
</p>

<?php

//note here who verified and then allow for double entry if not already double 
  //entered

print "</div>";

//show list of pid for this fid
	print "<div id=\"topper\">";

	$count = 1;	

	$sql = "SELECT pid
		FROM pages
		WHERE qid = '$qid'
		ORDER BY pidentifierval ASC";

	$pages = $db->GetAll($sql);
	

	foreach($pages as $page)
	{
		$p = $page['pid'];

		if ($pid == $p)
			print "<strong>$count</strong>";
		else
			print " <a href=\"" . $_SERVER['PHP_SELF'] . "?pid=$p&amp;fid=$fid\">$count</a> ";
		$count++;

	}
	
print "</div>";




?>


</body></html>




