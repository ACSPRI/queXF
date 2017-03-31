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
  $sql = "UPDATE questionnaires
          SET double_entry = 0
          WHERE 1";

  $db->Execute($sql);

	//submitted, now update database
	foreach($_POST as $key => $val)
	{
		if (substr($key,0,3) == 'qid')
		{
			$key = intval(substr($key,3));
			$val = intval($val);
			$sql = "UPDATE questionnaires
				SET double_entry = '$val'
				WHERE qid = '$key'";
			$db->Execute($sql);
		}

	}
}

xhtml_head(T_("Double entry"),true,array("../css/table.css"));

$sql = "SELECT qid,description, CONCAT('<input type=\'checkbox\' value=\'1\' name=\'qid', qid, '\' ', CASE WHEN double_entry = '1' THEN 'checked=\'checked\'' ELSE '' END, '/>' ) as doubleentry
	FROM questionnaires
	ORDER BY qid DESC";

$fs = $db->GetAll($sql);

print "<form method='post' action=''>";

print "<p><input name='submit' type='submit' value='" . T_("Save changes") . "'/></p>";

xhtml_table($fs,array('description','doubleentry'),array(T_('Questionnaire'),T_('Double entry')));

print "</form>";

xhtml_foot();

?>
