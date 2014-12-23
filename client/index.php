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


include_once("../config.inc.php");
include_once("../db.inc.php");
include_once("../functions/functions.database.php");
include_once("../functions/functions.client.php");
include_once("../functions/functions.xhtml.php");

xhtml_head(T_("Review forms"),true,array("css/review.css"));

$cid = get_client_id();

if ($cid)
{
	global $db;

	$fid = "";
	$pid = "";
	$var = "";
	
	if (isset($_GET['fid']))
		$fid = intval($_GET['fid']);
	
	if (isset($_GET['pid']))
		$pid = intval($_GET['pid']);


		print "<div id=\"header\">";
		print "<p>F:$fid</p>";
	
	?>
	<form action="" method="get">
	<div><?php echo T_("Form:"); ?> <input type="text" size="5" name="fid" value="<?php echo $fid ?>"/>
		<?php echo T_("Variable:"); ?> <input type="text" size="9" name="var" value="<?php echo $var ?>"/>
		<?php echo T_("Page:"); ?> <input type="text" size="4" name="pid" value="<?php echo $pid ?>"/>
		<input type="submit" value="<?php echo T_("Review"); ?>"/></div>
	</form>
	
	<?php
	print "</div>";


	$sql = "SELECT f.fid,f.qid
		FROM clientquestionnaire as cq, forms as f
		WHERE cq.cid = '$cid'
		AND f.qid = cq.qid
		AND f.fid = '$fid'";

	$rs = $db->GetRow($sql);

	if (empty($rs))
		print "<div id='content'><p>" . T_("This form not available for you to review. Please enter a form id in the box on the left") . "</p></div>";
	else
	{
		$qid = $rs['qid'];
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

	
		//show content
		print "<div id=\"content\">";
		print "<div style=\"position:relative;\"><img src=\"showpage.php?pid=$pid&amp;fid=$fid\" style=\"width:" . DISPLAY_PAGE_WIDTH ."px;\" alt=\"Image of page $pid, form $fid\" />";
		print "</div></div>";
	
	
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
	}
}	
else
{
	print "<p>" . T_("You are not authorised to review any forms") . "</p>";
}

xhtml_foot();	

?>
