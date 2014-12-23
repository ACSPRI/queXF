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

if (isset($_GET['fpnid']))
{
	$fpnid = intval($_GET['fpnid']);

	$db->StartTrans();

	$sql = "DELETE FROM formpagenote
		WHERE fpnid = '$fpnid'";

	$db->Execute($sql);

	$db->CompleteTrans();	
}

xhtml_head(T_("Listing of forms"),true,array("../css/table.css"));

$sql = "SELECT f.fid, f.pid, v.description as name, q.description as quest, CONCAT('<a href=\"?fpnid=', f.fpnid ,'\">" . T_("Delete note") . "</a>') as link, f.note
	FROM formpagenote as f
	JOIN forms ON (forms.fid = f.fid)
	JOIN questionnaires AS q ON (forms.qid = q.qid)
	LEFT JOIN verifiers AS v ON (v.vid = f.vid)
	ORDER BY f.fid,f.pid ASC";

$fs = $db->GetAll($sql);

xhtml_table($fs,array('fid','pid','name','quest','note','link'),array(T_('Form ID'),T_('Page ID'),T_('Operator'),T_('Questionnaire'),T_('Note'),T_('Delete note')));

xhtml_foot();

?>
