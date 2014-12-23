<?php

/*	Copyright Australian Consortium for Social and Political Research Incorporated (ACSPRI) 2011
 *	Written by Adam Zammit - adam.zammit@acspri.org.au
 *	For ACSPRI
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
				

function vq($btid,$kb,$qid)
{
	global $db;

	$sql = "SELECT btid,kb
		FROM ocrkbboxgroup
		WHERE btid = '$btid' and kb = '$kb' AND qid = '$qid'";

	$vq = $db->Execute($sql);

	return $vq->RecordCount();

}

function vqi($btid,$kb,$qid)
{
	global $db;

	$sql = "INSERT INTO
		ocrkbboxgroup (btid,kb,qid)
		VALUES('$btid','$kb','$qid')";

	$db->Execute($sql);
}


if (isset($_GET['qid']))
{
	$qid = intval($_GET['qid']);
	
	if (isset($_GET['submit']))
	{
		$db->StartTrans();

		$sql = "DELETE 
			FROM ocrkbboxgroup
			WHERE qid = '$qid'";

		$db->Execute($sql);

		foreach ($_GET as $g => $v)
		{
			$a = explode("_",$g);
			if ($a[0] == "cb")
				vqi($a[2],$a[1],$qid);
		}

		$db->CompleteTrans();
	}



	$sql = "SELECT kb,description
		FROM ocrkb
		ORDER by kb ASC";

	$ocrkb = $db->GetAll($sql);

	$sql = "SELECT btid,description
		FROM boxgrouptypes
		WHERE btid = 4 or btid = 3
		ORDER by btid ASC";

	$boxgrouptypes = $db->GetAll($sql);

	xhtml_head(T_("Assign ICR KB to questionnaire"),false,array("../css/table.css"));

	$sql = "SELECT description
		FROM questionnaires
		WHERE qid = '$qid'";

	$rs = $db->GetRow($sql);

	print "<p><a href='?'>" . T_("Go back") . "</a></p>";
	print "<h1>" . $rs['description'] . "</h1>";


	?>
	<script type="text/javascript">

	<?php
	print "kb = new Array(";

	$s = "";

	foreach($ocrkb as $q)
	{
		$s .= "'{$q['kb']}',";
	}

	$s = substr($s,0,strlen($s) - 1);
	print "$s);\n";

	print "btid = new Array(";

	$s = "";

	foreach($boxgrouptypes as $q)
	{
		$s .= "'{$q['btid']}',";
	}

	$s = substr($s,0,strlen($s) - 1);
	print "$s);\n";

	?>

	var QidOn = 0;
	var VidOn = 0;

	function checkQid(q)
	{
		
		for (y in btid)
		{
			v = btid[y];

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
		
		for (y in kb)
		{
			q = kb[y];

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



	print "<form action=\"\" method=\"get\"><table class='tclass'>";

	print "<tr><th></th>";
	foreach($boxgrouptypes as $v)
	{
		print "<th><a href=\"javascript:checkVid({$v['btid']})\">{$v['description']}</a></th>";
	}
	print "</tr>";

	$odd = 1;
	foreach($ocrkb as $q)
	{
		print "<tr";
		if ($odd)
		{
			print " class='odd'";
			$odd = 0;
		}
		else
			$odd = 1;
		print "><td><a href=\"javascript:checkQid({$q['kb']})\">{$q['description']}</a></td>";
		foreach($boxgrouptypes as $v)
		{
			$checked = "";
			if (vq($v['btid'],$q['kb'],$qid)) $checked="checked='checked'";
			print "<td><input type=\"checkbox\" name=\"cb_{$q['kb']}_{$v['btid']}\" id=\"cb_{$q['kb']}_{$v['btid']}\" $checked/></td>";
		}

		print "</tr>";
	}


	print "</table><p><input type=\"hidden\" name=\"qid\" value=\"$qid\"/><input type=\"submit\" name=\"submit\" value=\"" . T_("Assign ICR KB to questionnaire") . "\"/></p></form>";

}
else
{
	//form to choose a questionnaire/form
	$sql = "SELECT qid,description
    FROM questionnaires
    ORDER BY qid DESC";
	
	$qs = $db->GetAll($sql);

	foreach($qs as $q)
	{
		print "<a href=\"?qid={$q['qid']}\">". T_("Assign ICR KB to questionnaire") . ": {$q['description']}</a>";
		print "<br/>";
	}

}





?>

</body>
</html>
