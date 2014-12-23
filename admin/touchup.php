<?php

/*	Copyright Australian Consortium for Social and Political Research (ACSPRI), 2010
 *	Written by Adam Zammit - adam.zammit@acspri.org.au
 *	For the ACSPRI: http://www.acspri.org.au/
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


//Touch up pages on a form template (By downloading and being able to re-upload them for banding)

include_once("../config.inc.php");
include_once("../db.inc.php");
include("../functions/functions.database.php");
include("../functions/functions.xhtml.php");

if (isset($_GET['pid']))
{
	$pid = intval($_GET['pid']);

	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Content-Type: image/png");
	header ("Content-Disposition: attachment; filename=pid$pid.png");

	$sql = "SELECT image 
		FROM pages
		WHERE pid = '$pid'";

	$i = $db->GetRow($sql);

	echo ($i['image']);

	//Download file
	exit();
}

xhtml_head();

if (isset($_POST['submit']))
{
	foreach($_FILES as $key => $val)
	{
		if (substr($key,0,3) == "pid")
		{
			$filename = $val['tmp_name'];
			if (!empty($filename))
			{
				$pid = intval(substr($key,3));
				//store $filename back in the database
				$data = file_get_contents($filename);
				
				$sql = "UPDATE pages
					SET image = '" . addslashes($data) . "'
					WHERE pid = '$pid'";

				$db->Execute($sql);
			}
		}
	}
	$_GET['qid'] = $_POST['qid'];
}

if (isset($_GET['qid']))
{
	$qid = intval($_GET['qid']);

	print "<p><a href='?'>" . T_("Go back") . "</a></p>";

	$sql = "SELECT pid,pidentifierval
		FROM pages
		WHERE qid = '$qid'";

	$rs = $db->GetAll($sql);

	//print both a download link and an upload form
	print "<form enctype='multipart/form-data' action='?' method='post'><p><input type='hidden' name='MAX_FILE_SIZE' value='10000000000'/><input type='hidden' name='qid' value='$qid'/></p>";

	foreach($rs as $r)
	{
		print "<p><a href='?pid={$r['pid']}'>".T_("Download")." {$r['pid']} ".T_("to edit")."</a> - ".T_("Upload here").": <input name='pid{$r['pid']}' type='file'/></p>";
	}

	print "<p><input type='submit' value='" . T_("Upload pages") . "' name='submit'/></p></form>";
}
else
{
	//print available questionnaires
	$sql = "SELECT qid,description
		FROM questionnaires";
	
	$qs = $db->GetAll($sql);

	foreach($qs as $q)
	{
		print "<a href=\"?qid={$q['qid']}\">" . T_("Touch up") . ": {$q['description']}</a>";
		print "<br/>";
	}


}

xhtml_foot();

?>
