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

xhtml_head(T_("queXS and Limesurvey integration"),true,array("../css/table.css"));

if (isset($_GET['fid']))
{
	include_once("../functions/functions.output.php");
	uploadrpc(intval($_GET['fid']));
}

			
if (isset($_GET['qid']))
{
	print "<p><a href='?'>" . T_("Go back") . "</a></p>";
	$qid = intval($_GET['qid']);
	
	if (isset($_POST['submit']))
	{
		$sql = "UPDATE questionnaires
			SET 
			rpc_server_url = " . $db->quote($_POST['rpc_server_url']) . ",
			rpc_username = " . $db->quote($_POST['rpc_username']) . ",
			rpc_password = " . $db->quote($_POST['rpc_password']) . ",
			limesurvey_sid = " . intval($_POST['limesurvey_sid']) . "
			WHERE qid = '$qid'";

		$db->Execute($sql);
	}


	$sql = "SELECT description,rpc_server_url,rpc_username,rpc_password,limesurvey_sid
		FROM questionnaires
		WHERE qid = '$qid'";

	$q = $db->GetRow($sql);

	print "<h2>" . $q['description'] . "</h2>";
	print "<form action='?qid=$qid' method='post'>";
	print "<p><label for='rpc_server_url'>" . T_("RPC Server URL (example: http://user:password@localhost/quexs/include/limesurvey/admin/remotecontrol.php)") . "</label> <input id='rpc_server_url' name='rpc_server_url' type='text' value='{$q['rpc_server_url']}' size='100'/></p>";
	print "<p><label for='rpc_username'>" . T_("RPC Username") . "</label> <input id='rpc_username' name='rpc_username' type='text' value='{$q['rpc_username']}'/></p>";
	print "<p><label for='rpc_password'>" . T_("RPC Password") . "</label> <input id='rpc_password' name='rpc_password' type='text' value='{$q['rpc_password']}'/></p>";
	print "<p><label for='limesurvey_sid'>" . T_("Limesurvey Survey ID") . "</label> <input id='limesurvey_sid' name='limesurvey_sid' type='text' value='{$q['limesurvey_sid']}'/></p>";
	print "<p><input type='submit' name='submit' value='".  T_("Update settings") . "'/></p>";
	print "</form>";

	if (!empty($q['rpc_server_url']))
	{
		$sql = "SELECT f.fid, v.description, CONCAT('<a href=\'?qid=$qid&amp;fid=',f.fid,'\'>" . T_("Upload") . "</a>') as link
			FROM forms as f, verifiers as v
			WHERE f.qid = '$qid'
			AND f.assigned_vid = v.vid
			AND f.done = 1
			AND f.rpc_id IS NULL";
	
		$rs = $db->GetAll($sql);

		if (empty($rs))
			print "<p>" . T_("No forms to upload") . "</p>";
		else
			xhtml_table($rs,array("fid","description","link"),array(T_("Form ID"),T_("Verifier"),T_("Upload")));
	}
}
else
{
	//form to choose a questionnaire/form
	$sql = "SELECT qid,description
		FROM questionnaires";
	
	$qs = $db->GetAll($sql);

	foreach($qs as $q)
	{
		print "<a href=\"?qid={$q['qid']}\">". T_("queXS and Limesurvey integration") . ": {$q['description']}</a>";
		print "<br/>";
	}

}

xhtml_foot();
