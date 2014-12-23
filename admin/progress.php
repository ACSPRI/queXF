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


//display the progress of questionnaires

include_once("../config.inc.php");
include_once("../db.inc.php");
include("../functions/functions.database.php");
include("../functions/functions.xhtml.php");

xhtml_head(T_("Progress"),true,array("../css/table.css"));

$sql = "SELECT q.description, f1.c AS done, f2.c AS remain
	FROM questionnaires AS q
	LEFT JOIN (
	
	SELECT count( fid ) AS c, qid
	FROM forms
	WHERE done =1
	GROUP BY qid
	) AS f1 ON ( f1.qid = q.qid )
	LEFT JOIN (
	
	SELECT count( fid ) AS c, qid
	FROM forms
	WHERE done =0
	GROUP BY qid
	) AS f2 ON ( f2.qid = q.qid )
	ORDER BY q.qid DESC";
	
$qs = $db->GetAll($sql);

print "<table class='tclass'><tr><th>" . T_("Questionnaire") . "</th><th>" . T_("Done") . "</th><th>" . T_("Remain") . "</th><th>" . T_("Total forms imported") . "</th></tr>";
$done = 0;
$remain = 0;
$rtotal = 0;
$odd = 1;
$class = "class='odd'";
foreach($qs as $q)
{
	$rtotal = $q['done'] + $q['remain'];
	$remain += $q['remain'];
	$done += $q['done'];
	print "<tr ";
	if ($odd)
	{
		$odd = 0;
		print $class;
	}
	else
		$odd = 1;
	print "><td>{$q['description']}</td><td>{$q['done']}</td><td>{$q['remain']}</td><td>$rtotal</td></tr>";
}
$rtotal = $done + $remain;
print "<tr><td>" . T_("Total") . ":</td><td>$done</td><td>$remain</td><td>$rtotal</td></tr>";
print "</table>";

xhtml_foot();

?>
