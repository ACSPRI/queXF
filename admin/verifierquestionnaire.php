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


include("../config.inc.php");
include("../functions/functions.database.php");
				

function vq($vid,$qid)
{
	global $db;

	$sql = "SELECT vid,qid
		FROM verifierquestionnaire
		WHERE vid = '$vid' and qid = '$qid'";

	$vq = $db->Execute($sql);

	return $vq->RecordCount();

}

function vqi($vid,$qid)
{
	global $db;

	$sql = "INSERT INTO
		verifierquestionnaire (vid,qid)
		VALUES('$vid','$qid')";

	$db->Execute($sql);
}


function vqd($vid,$qid)
{
	global $db;

	$sql = "DELETE FROM
		verifierquestionnaire 
		WHERE vid = '$vid' and qid = '$qid'";

	$db->Execute($sql);
}




if (isset($_GET['submit']))
{
	$db->StartTrans();

	$sql = "DELETE 
		FROM verifierquestionnaire
		WHERE 1";

	$db->Execute($sql);

	foreach ($_GET as $g => $v)
	{
		$a = explode("_",$g);
		if ($a[0] == "cb")
			vqi($a[2],$a[1]);
	}

	$db->CompleteTrans();
}



$sql = "SELECT qid,description
	FROM questionnaires
	ORDER by qid ASC";

$questionnaires = $db->GetAll($sql);

$sql = "SELECT vid,description
	FROM verifiers
	ORDER by vid ASC";

$verifiers = $db->GetAll($sql);


?>

<html>
<head>
<script type="text/javascript">

<?
print "qid = new Array(";

$s = "";

foreach($questionnaires as $q)
{
	$s .= "'{$q['qid']}',";
}

$s = substr($s,0,strlen($s) - 1);
print "$s);\n";

print "vid = new Array(";

$s = "";

foreach($verifiers as $q)
{
	$s .= "'{$q['vid']}',";
}

$s = substr($s,0,strlen($s) - 1);
print "$s);\n";

?>

var QidOn = 0;
var VidOn = 0;

function checkQid(q)
{
	
	for (y in vid)
	{
		v = vid[y];

		cb = document.getElementById('cb_' + q + "_" + v);

		if (QidOn == 0)
			cb.checked = 'checked';
		else
			cb.checked = '';
			
	}

	if (QidOn == 0)
		QidOn = 1;
	else
		QidOn = 0;
}



function checkVid(v)
{
	
	for (y in qid)
	{
		q = qid[y];

		cb = document.getElementById('cb_' + q + "_" + v);

		if (VidOn == 0)
			cb.checked = 'checked';
		else
			cb.checked = '';
			
	}

	if (VidOn == 0)
		VidOn = 1;
	else
		VidOn = 0;
}



</script>
</head>
<body>


<?



print "<form action=\"\" type=\"GET\"><table>";

print "<th>";
foreach($questionnaires as $q)
{
	print "<td><a href=\"javascript:checkQid({$q['qid']})\">{$q['description']}</a></td>";
}
print "</th>";

foreach($verifiers as $v)
{
	print "<tr>";
	print "<td><a href=\"javascript:checkVid({$v['vid']})\">{$v['description']}</a></td>";
	foreach($questionnaires as $q)
	{
		$checked = "";
		if (vq($v['vid'],$q['qid'])) $checked="checked=checked";
		print "<td><input type=\"checkbox\" name=\"cb_{$q['qid']}_{$v['vid']}\" id=\"cb_{$q['qid']}_{$v['vid']}\" $checked></input></td>";
	}

	print "</tr>";
}


print "</table><input type=\"submit\" name=\"submit\"/></form>";



?>

</body>
</html>

