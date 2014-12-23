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


//Reorder the variables on a form manually
//
//Sorting list thanks to: http://tool-man.org/examples/sorting.html

include_once("../config.inc.php");
include_once("../db.inc.php");
include("../functions/functions.database.php");
include("../functions/functions.xhtml.php");


function printquestionnaires()
{
	global $db;

	//print available questionnaires
	$sql = "SELECT qid,description
    FROM questionnaires
    ORDER BY qid DESC";
	
	$qs = $db->GetAll($sql);

	xhtml_head(T_("Reorder variables"),true,array("../css/table.css"));

	print "<table class='tclass'><tr><th>" . T_("Questionnaire") . "</th><th></th><th></th><th></th></tr>";
	$c = 1;
	foreach($qs as $q)
	{
		print "<tr ";
		if ($c == 1)
			$c = 0;
		else
		{
			$c = 1;
			print "class='odd'";
		}
		print "><td>{$q['description']}</td><td><a href=\"?qid={$q['qid']}\">" . T_("Manual reorder") . "</a></td><td><a href=\"?qid={$q['qid']}&amp;position=position\">" . T_("Reorder by position") . "</a></td><td><a href=\"?qid={$q['qid']}&amp;varname=varname\">" . T_("Reorder by variable name") . "</a></td></tr>";
	}
	print "</table>";
}



if (isset($_GET['qid']))
{
	$qid = intval($_GET['qid']);

	if (isset($_GET['position']))
	{
		sort_order_pageid_box($qid);
		printquestionnaires();
	}
	else if (isset($_GET['varname']))
	{
		sort_order_varname($qid);
		printquestionnaires();
	}
	else
	{
		xhtml_head(T_("Reorder variables"),true,array("../css/toolman/lists.css","../css/table.css"),array("../js/toolman/core.js","../js/toolman/events.js","../js/toolman/css.js","../js/toolman/coordinates.js","../js/toolman/drag.js","../js/toolman/dragsort.js","../js/toolman/cookies.js","../js/reorder.js"));

		if (isset($_POST['list']))
		{
			$list = $_POST['list'];
			$elements = explode("|",$_POST['list']);
		
			$i = 0;
	
			$db->StartTrans();
			foreach($elements as $e)
			{
				$e = intval($e);
		
				$sql = "UPDATE boxgroupstype
					SET sortorder = '$i'
					WHERE bgid = '$e'";
		
				$db->Execute($sql);
				$i++;
			}
			$db->CompleteTrans();
		}
	
	
		//Create a list of all (non temporary) variables for this questionnaire
	
		$sql = "SELECT b.bgid as bgid, b.varname as varname
			FROM `boxgroupstype` AS b, pages AS p
			WHERE p.qid = '$qid'
			AND b.pid = p.pid
			AND b.btid != 0
			ORDER BY b.sortorder ASC";
	
		$vars = $db->GetAll($sql);

		print "<p>" . T_("Reorder variables by dragging and dropping, then clicking on 'Save Changes' below") . "</p>";

		print "<p><a href='reorder.php'>" . T_("Click here") . "</a> " . T_("to return without saving changes") . "</p>";

		print "<ul id='phoneticlong' class='boxy'>";
		foreach($vars as $var)
		{
			print "<li title='{$var['bgid']}'>{$var['varname']}</li>";
		}
		print "</ul>";
	
		print "<form method='post' action='' onsubmit=\"return saveOrderList('phoneticlong');\"><p><input type='hidden' name='list' id='list'/></p><p><input type='submit' name='submit' id='submit' value='" .  T_("Save changes") . "'/></p></form>";
	}
}
else
	printquestionnaires();


xhtml_foot();
?>
