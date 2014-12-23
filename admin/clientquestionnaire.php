<?php
/**
 * Assign clients to questionnaires in a checkbox matrix
 *
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
 *
 * @author Adam Zammit <adam.zammit@deakin.edu.au>
 * @copyright Deakin University 2007,2008
 * @package queXF
 * @subpackage admin
 * @link http://www.deakin.edu.au/dcarf/ queXF was writen for DCARF - Deakin Computer Assisted Research Facility
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL) Version 2
 * 
 */

/**
 * Configuration file
 */
include ("../config.inc.php");

/**
 * Database file
 */
include ("../db.inc.php");

/**
 * XHTML functions
 */
include ("../functions/functions.xhtml.php");

/**
 * Return if an client has already been assigned to this questionnaire
 *
 * @param int $client Client id
 * @param int $qid Questionnaire id
 * @return int 1 if assigned otherwise 0
 *
 */
function vq($cid,$qid)
{
	global $db;

	$sql = "SELECT cid,qid
		FROM clientquestionnaire
		WHERE cid = '$cid' and qid = '$qid'";

	$vq = $db->Execute($sql);

	return $vq->RecordCount();

}

/**
 * Assign an client to a questionnaire
 *
 * @param int $cid Client id
 * @param int $qid Questionnaire id
 *
 */
function vqi($cid,$qid)
{
	global $db;

	$sql = "INSERT INTO
		clientquestionnaire (cid,qid)
		VALUES('$cid','$qid')";

	$db->Execute($sql);
}


/**
 * Unassign an client from a questionnaire
 *
 * @param int $cid Client id
 * @param int $qid Questionnaire id
 *
 */
function vqd($cid,$qid)
{
	global $db;

	$sql = "DELETE FROM
		clientquestionnaire	
		WHERE cid = '$cid' and qid = '$qid'";

	$db->Execute($sql);
}




if (isset($_POST['submit']))
{
	$db->StartTrans();

	$sql = "DELETE 
		FROM clientquestionnaire
		WHERE 1";

	$db->Execute($sql);

	foreach ($_POST as $g => $v)
	{
		$a = explode("_",$g);
		if ($a[0] == "cb")
			vqi($a[2],$a[1]);
	}

	$db->CompleteTrans();
}



$sql = "SELECT qid,description
	FROM questionnaires
	ORDER by qid DESC";

$questionnaires = $db->GetAll($sql);

$sql = "SELECT cid,description
	FROM clients
	ORDER by cid ASC";

$clients = $db->GetAll($sql);


xhtml_head(T_("Assign clients to questionnaires"),false,array("../css/table.css"));

?>

<script type="text/javascript">

<?php
print "qid = new Array(";

$s = "";

foreach($questionnaires as $q)
{
	$s .= "'{$q['qid']}',";
}

$s = substr($s,0,strlen($s) - 1);
print "$s);\n";

print "cid = new Array(";

$s = "";

foreach($clients as $q)
{
	$s .= "'{$q['cid']}',";
}

$s = substr($s,0,strlen($s) - 1);
print "$s);\n";

?>

var QidOn = 0;
var VidOn = 0;

function checkQid(q)
{
	
	for (y in cid)
	{
		v = cid[y];

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


<?php



print "<form action=\"\" method=\"post\"><table class='tclass'>";

print "<tr><th></th>";
foreach($questionnaires as $q)
{
	print "<th><a href=\"javascript:checkQid({$q['qid']})\">{$q['description']}</a></th>";
}
print "</tr>";

$class = 0;

foreach($clients as $v)
{
	print "<tr class='";
	if ($class == 0) {$class = 1; print "even";} else {$class = 0; print "odd";}
	print "'>";
	print "<th><a href=\"javascript:checkVid({$v['cid']})\">{$v['description']}</a></th>";
	foreach($questionnaires as $q)
	{
		$checked = "";
		if (vq($v['cid'],$q['qid'])) $checked="checked=\"checked\"";
		print "<td><input type=\"checkbox\" name=\"cb_{$q['qid']}_{$v['cid']}\" id=\"cb_{$q['qid']}_{$v['cid']}\" $checked></input></td>";
	}

	print "</tr>";
}


print "</table><p><input type=\"submit\" name=\"submit\" value=\"" . T_("Assign clients to questionnaires") . "\"/></p></form>";


xhtml_foot();

?>
