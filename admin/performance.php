<?

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

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>Performance</title>
<style type="text/css">
.tclass th {
	text-align:left;
	border: 1px solid #aaa;
}
.tclass td {
	border: 1px solid #aaa;
}
.bold {
	font-weight:bold;
}
</style>
</head>
<body>

<?

/**
 * Display data about this questionnaire
 *
 */
if (isset($_GET['qid']))
{
	$qid = intval($_GET['qid']);

$sql = "SELECT q.description as qu, v.description as ve,f.qid,w.vid , count( * ) AS c, count( * ) / ( SUM( TIMESTAMPDIFF(
	SECOND , w.assigned, w.completed ) ) /3600 ) AS CPH, (
	(
	
	SELECT count( pid )
	FROM pages
	WHERE qid = f.qid
	) * count( * )
	) / ( SUM( TIMESTAMPDIFF(
	SECOND , w.assigned, w.completed ) ) /3600 ) AS PPH
	FROM worklog AS w
	JOIN forms AS f ON ( f.fid = w.fid )
	JOIN questionnaires as q on (f.qid = q.qid)
	JOIN verifiers as v on (v.vid = w.vid)
	WHERE f.qid = '$qid'
	GROUP BY f.qid, w.vid
	ORDER BY CPH DESC";

	$rs = $db->GetAll($sql);
	
	if (!empty($rs))
	{
		print "<h1>{$rs[0]['qu']}</h1><table><tr><th>Operator</th><th>Completed Forms</th><th>Completions Per Hour</th><th>Pages Per Hour</th></tr>";
		foreach ($rs as $r)
		{
			print "<tr><td>{$r['ve']}</td><td>{$r['c']}</td><td>{$r['CPH']}</td><td>{$r['PPH']}</td></tr>";
		}
		print "</table>";
	}

}

/**
 * Display data about this operator
 */
else if (isset($_GET['vid']))
{
	$vid = intval($_GET['vid']);

$sql = "SELECT q.description as qu, v.description as ve,f.qid,w.vid , count( * ) AS c, count( * ) / ( SUM( TIMESTAMPDIFF(
	SECOND , w.assigned, w.completed ) ) /3600 ) AS CPH, (
	(
	
	SELECT count( pid )
	FROM pages
	WHERE qid = f.qid
	) * count( * )
	) / ( SUM( TIMESTAMPDIFF(
	SECOND , w.assigned, w.completed ) ) /3600 ) AS PPH
	FROM worklog AS w
	JOIN forms AS f ON ( f.fid = w.fid )
	JOIN questionnaires as q on (f.qid = q.qid)
	JOIN verifiers as v on (v.vid = w.vid)
	WHERE w.vid = '$vid'
	GROUP BY f.qid, w.vid
	ORDER BY CPH DESC";

	$rs = $db->GetAll($sql);

	if (!empty($rs))
	{
		print "<h1>{$rs[0]['ve']}</h1><table><tr><th>Questionnaire</th><th>Completed Forms</th><th>Completions Per Hour</th><th>Pages Per Hour</th></tr>";
		foreach ($rs as $r)
		{
			print "<tr><td>{$r['qu']}</td><td>{$r['c']}</td><td>{$r['CPH']}</td><td>{$r['PPH']}</td></tr>";
		}
		print "</table>";
	}


}


else 
{
	$sql = "SELECT CONCAT( f.qid, '_', w.vid ) AS qv, count( * ) AS c, count( * ) / ( SUM( TIMESTAMPDIFF(
	SECOND , w.assigned, w.completed ) ) /3600 ) AS CPH, (
	(
	
	SELECT count( pid )
	FROM pages
	WHERE qid = f.qid
	) * count( * )
	) / ( SUM( TIMESTAMPDIFF(
	SECOND , w.assigned, w.completed ) ) /3600 ) AS PPH
	FROM worklog AS w
	JOIN forms AS f ON ( f.fid = w.fid )
	GROUP BY f.qid, w.vid
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
		
		
	print "<table><tr><th></th>";
	foreach($questionnaires as $q)
	{
		print "<td><a href='?qid={$q['qid']}'>{$q['description']}</a></td>";
	}
	print "</tr>";

	foreach($verifiers as $v)
	{
		print "<tr><td><a href='?vid={$v['vid']}'>{$v['description']}</a></td>";
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

?>
</body></html>

