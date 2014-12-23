<?php

/*	Copyright Australian Consortium for Social and Political Research Incorporated (ACSPRI) 2009
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

if (isset($_GET['pid'])){

	include_once("config.inc.php");
	include_once("db.inc.php");
	
	global $db;

	$pid = intval($_GET['pid']);
	$vid = intval($_GET['vid']);
	$fid = intval($_GET['fid']);

	if (isset($_GET['submit']))
	{
		$note = $db->qstr($_GET['pagenote']);
		
		$sql = "INSERT INTO formpagenote (fpnid,fid,pid,vid,note)
			VALUES (NULL,'$fid','$pid','$vid',$note)";
		
		$db->Execute($sql);
	}

	
	$sql = "SELECT note
		FROM formpagenote
		WHERE pid = '$pid'
		AND fid = '$fid'";

	$rs = $db->GetAll($sql);

	foreach($rs as $r)
	{
		print "<div>" . $r['note'] . "</div>";
	}

	print "<form action='?' method='get'>";
	print "<p><label for='pagenote'>" . T_("Page note:") . "</label>";
	print "<input type='text' name='pagenote' id='pagenote'>";
	print "<input type='hidden' name='vid' value='$vid'/>";
	print "<input type='hidden' name='pid' value='$pid'/>";
	print "<input type='hidden' name='fid' value='$fid'/>";
	print "<input type='submit' value='" . T_("Add note") . "' name='submit' id='submit'/></p>";
	print "</form>";

}

?>
