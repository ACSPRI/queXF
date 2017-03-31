<?php

/*	Copyright Deakin University 2007,2008,2009
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
include("../functions/functions.database.php");
include("../functions/functions.xhtml.php");

if (isset($_POST['submit']))
{
  $sql = "UPDATE processforms
          SET allowanother = 0
          WHERE 1";

  $db->Execute($sql);

	//submitted, now update database
	foreach($_POST as $key => $val)
	{
		if (substr($key,0,4) == 'pfid')
		{
			$key = intval(substr($key,4));
			$val = intval($val);
			$sql = "UPDATE processforms
				SET allowanother = '$val'
				WHERE pfid = '$key'";
			$db->Execute($sql);
		}

	}
}

xhtml_head(T_("Listing of imported files by status"),true,array("../css/table.css"));

$status = 1;
if (isset($_GET['status'])) $status = intval($_GET['status']);
if (isset($_POST['status'])) $status = intval($_POST['status']);

if ($status == 1)
	print "<h1>" . T_("Forms successfully imported") . "</h1>";
if ($status == 2)
	print "<h1>" . T_("Forms not imported") . "</h1>";

$sql = "SELECT pfid,filepath,filehash,date,status, CONCAT('<input type=\'checkbox\' value=\'1\' name=\'pfid', pfid, '\' ', CASE WHEN allowanother = '1' THEN 'checked=\'checked\'' ELSE '' END, '/>' ) as allowanother
	FROM processforms
	WHERE status = $status
	ORDER BY date DESC";

$fs = $db->GetAll($sql);

print "<form method='post' action=''>";

print "<p><input name='status' type='hidden' id='status' value='$status'/><input name='submit' type='submit' value='" . T_("Save changes") . "'/></p>";

xhtml_table($fs,array('filepath','filehash','date','allowanother'),array(T_('File'),T_('SHA1'),T_('Date'),T_('Allow import again?')));

print "</form>";

xhtml_foot();

?>
