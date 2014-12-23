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


//display the performance of operators (Completions per hour, Pages per hour)

include_once("../config.inc.php");
include_once("../db.inc.php");
include("../functions/functions.database.php");
include("../functions/functions.xhtml.php");

xhtml_head(T_("Performance"),true,array("../css/table.css"));

/**
 * Display data about this questionnaire
 *
 */
if (isset($_GET['qid']))
{
	$qid = intval($_GET['qid']);

$sql = "SELECT q.description as qu, v.description as ve,f.qid,f.assigne_vid as vid , count( * ) AS c, count( * ) / ( SUM( TIMESTAMPDIFF(
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
	WHERE f.qid = '$qid'
	GROUP BY f.qid, f.assigned_vid
	ORDER BY CPH DESC";

	$rs = $db->GetAll($sql);
	
	if (!empty($rs))
	{
		print "<h1>{$rs[0]['qu']}</h1>";

		xhtml_table($rs,array('ve','c','CPH','PPH'),array(T_("Operator"),T_("Completed Forms"),T_("Completions Per Hour"),T_("Pages Per Hour")));

	
	}

}

/**
 * Display data about this operator
 */
else if (isset($_GET['vid']))
{
	$vid = intval($_GET['vid']);

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
	WHERE f.assigned_vid = '$vid'
	GROUP BY f.qid, f.assigned_vid
	ORDER BY CPH DESC";

	$rs = $db->GetAll($sql);

	if (!empty($rs))
	{

		print "<h1>{$rs[0]['ve']}</h1>";

		xhtml_table($rs,array('qu','c','CPH','PPH'),array(T_("Questionnaire"),T_("Completed Forms"),T_("Completions Per Hour"),T_("Pages Per Hour")));
	
	}


}


else 
{
	$sql = "SELECT CONCAT( f.qid, '_', f.assigned_vid ) AS qv, count( * ) AS c, count( * ) / ( SUM( TIMESTAMPDIFF(
	SECOND , f.assigned, f.completed ) ) /3600 ) AS CPH, (
	(
	
	SELECT count( pid )
	FROM pages
	WHERE qid = f.qid
	) * count( * )
	) / ( SUM( TIMESTAMPDIFF(
	SECOND , f.assigned, f.completed ) ) /3600 ) AS PPH
	FROM forms AS f
	GROUP BY f.qid, f.assigned_vid
	ORDER BY qid ASC , CPH DESC , qid ASC	";
	
	$qs = $db->GetAssoc($sql);
	
	
	$sql = "SELECT qid,description
		FROM questionnaires
		ORDER by qid ASC";
	
	$questionnaires = $db->GetAll($sql);
	
	$sql = "SELECT vid,description
		FROM verifiers
		ORDER by vid ASC";
	
	$verifiers = $db->GetAll($sql);
		
		
	print "<table class='tclass'><tr><th></th>";
	foreach($questionnaires as $q)
	{
		print "<th><a href='?qid={$q['qid']}'>{$q['description']}</a></th>";
	}
	print "</tr>";

	$odd = 1;
	foreach($verifiers as $v)
	{
		print "<tr ";
		if ($odd)
		{
			print "class='odd'";
			$odd = 0;
		}
		else
			$odd = 1;
		print "><th><a href='?vid={$v['vid']}'>{$v['description']}</a></th>";
		foreach($questionnaires as $q)
		{
			$checked = "";
			
			print "<td>";
			if (isset($qs[$q['qid'] . "_" . $v['vid']]))
			{
				print "<div>".$qs[$q['qid'] . "_" . $v['vid']]['CPH']."</div>";
			}

			print "</td>";
		}
		print "</tr>";
	}


	print "</table>";
}

xhtml_foot();

?>
