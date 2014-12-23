<?php

/*	Copyright Australian Consortium for Social and Political Research Incorporated (ACSPRI) 2009
 *	Written by Adam Zammit - adam.zammit@acspri.org.au
 *	For ACSPRI: http://www.acspri.org.au/
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

if (isset($_GET['fid']))
{
	$fid = intval($_GET['fid']);
	$vid = intval($_GET['vid']);

	$db->StartTrans();

	$sql = "DELETE FROM formboxverifychar
		WHERE fid = '$fid'
		AND vid = '$vid'";
		
	$db->Execute($sql);

	$sql = "DELETE FROM formboxverifytext
		WHERE fid = '$fid'
		AND vid = '$vid'";

	$db->Execute($sql);

	$sql = "UPDATE forms
		SET assigned_vid = NULL, done = 0, rpc_id = NULL, assigned = NULL, completed = NULL
		WHERE fid = '$fid'";

	$db->Execute($sql);

	$db->CompleteTrans();	
}

xhtml_head(T_("Listing of forms"),true,array("../css/table.css"));


if (isset($_GET['qid']))
{
  $qid = intval($_GET['qid']);

  $sql = "SELECT f.fid, v.description as name, q.description as quest, CONCAT('<a href=\"?qid=$qid&amp;fid=', f.fid ,'&amp;vid=', f.assigned_vid ,'\">" . T_("Re verify") . "</a>') as link
  	FROM forms as f
  	JOIN questionnaires AS q ON (f.qid = q.qid AND q.qid = '$qid')
  	LEFT JOIN verifiers AS v ON (v.vid = f.assigned_vid)
  	WHERE f.done = 1
  	ORDER BY f.fid ASC";
  
  $fs = $db->GetAll($sql);

  print "<div><a href=\"?\">" . T_("Go back") . "</a>";

  xhtml_table($fs,array('fid','name','quest','link'),array(T_('Form ID'),T_('Operator'),T_('Questionnaire'),T_('Re verify')));
}
else
{
  //print available questionnaires
	$sql = "SELECT qid,description
    FROM questionnaires
    ORDER BY qid DESC";
	
	$qs = $db->GetAll($sql);

	foreach($qs as $q)
	{
		print "<div><a href=\"?qid={$q['qid']}\">". T_("List") . ": {$q['description']}</a></div>";
	}
}


xhtml_foot();

?>
