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

include_once('../config.inc.php');
include("../functions/functions.database.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>Progress</title>
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
	ORDER BY qid ASC , CPH DESC , vid ASC	";
	
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
	print "<td>{$q['description']}</td>";
}
print "</tr>";

foreach($verifiers as $v)
{
	print "<tr><td>{$v['description']}</td>";
	$tq = -1;
	foreach($questionnaires as $q)
	{
		$checked = "";
		
		print "<td>";
		if (isset($qs[$q['qid'] . "_" . $v['vid']]))
		{
			if ($tq != $q['qid'])
			{
				print "<div class='bold'>";
				$tq = $q['qid'];
			}
			else
				print "<div>";
			
			print $qs[$q['qid'] . "_" . $v['vid']]['CPH']."</div>";
		}

		print "</td>";
	}
	print "</tr>";
}


print "</table>";


?>

</body></html>

