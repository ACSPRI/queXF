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
				
global $db;


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
		FROM boxes as b, pages as p
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
print "<div id=\"content\">";
	print "<div style=\"position:relative;\"><img src=\"showpage.php?pid=$pid&amp;fid=$fid\" style=\"width:800px;\" alt=\"Image of page $pid, form $fid\" />";
print "</div></div>";

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

<?php

	/*
	foreach($_SESSION['boxgroups'] as $key => $val)
	{
		if ($val['pid'] == $pid)
		{
			//if ($bgid == $key)
				print "<strong>{$val['varname']}</strong><br/>";
			//else
			//	print "<a id=\"link$key\" href=\"" . $_SERVER['PHP_SELF'] . "?bgid=$key&amp;fid=$fid#boxGroup\">{$val['varname']}</a><br/>";
		}	
	}*/
	
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




