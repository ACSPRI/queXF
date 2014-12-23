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
include ("../functions/functions.xhtml.php");

if (isset($_GET['qid']))
{
	$qid = intval($_GET['qid']);

	$db->StartTrans();

	$sql = "DELETE 
		FROM questionnaires
		WHERE qid = '$qid'";

	$db->Execute($sql);

	$sql = "SELECT pid 
		FROM pages
		WHERE qid = '$qid'";

	$rs = $db->GetAll($sql);

	foreach($rs as $r)
	{
		$sql = "DELETE FROM boxes
			WHERE pid = '{$r['pid']}'";

		$db->Execute($sql);

		$sql = "DELETE FROM boxgroupstype
			WHERE pid = '{$r['pid']}'";

		$db->Execute($sql);
	}

	$sql = "DELETE 
		FROM pages
		WHERE qid = '$qid'";

	$db->Execute($sql);

	$db->CompleteTrans();
}

xhtml_head(T_("Delete a questionnaire"));

//select only questionnaires where there are no forms attached
$sql = "SELECT q.qid, q.description
	FROM questionnaires AS q
	LEFT JOIN (
		SELECT count( fid ) AS c, qid
		FROM forms
		GROUP BY qid
		) AS f ON ( f.qid = q.qid )
	WHERE f.c IS NULL OR f.c =0";


$qs = $db->GetAll($sql);

print "<h3>" . T_("Click to delete questionnaire") . "</h3>";
print "<p>" . T_("The following questionnaires have no forms associated with them so are safe to delete") . "</p>";

foreach ($qs as $q)
{
	print "<p>" . T_("Delete") . ": <a href=\"?qid={$q['qid']}\">{$q['description']}</a></p>";
}

xhtml_foot();

?>
