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

	$db->StartTrans();

	$sql = "SELECT pfid 
		FROM forms
		WHERE fid = '$fid'";

	$rs = $db->GetRow($sql);

	$pfid = $rs['pfid'];

	$sql = "DELETE FROM formpages
		WHERE fid = '$fid'";

	$db->Execute($sql);

	$sql = "DELETE FROM forms
		WHERE fid = '$fid'";

	$db->Execute($sql);

	$sql = "UPDATE processforms
		SET allowanother = 1
		WHERE pfid = '$pfid'";

	$db->Execute($sql);

	$db->CompleteTrans();	
}

xhtml_head(T_("Pages missing from scan"),true,array("../css/table.css"));

$sql = "SELECT f.fid AS fid, f.description, q.description as qdesc, 
	CASE WHEN EXISTS (SELECT mpid FROM missingpages WHERE fid = f.fid) THEN CONCAT('<a href=\'missingpages.php?missingfid=',f.fid,'\'>" . T_("Handle undetected pages") . "</a>')  ELSE CONCAT('<a href=\'?fid=' , f.fid , '\'>" . T_("Delete form and allow importing again") . "</a>') END as link
	FROM forms AS f
	LEFT JOIN questionnaires as q on (f.qid = q.qid)
	WHERE f.done =0
	AND f.assigned_vid IS NULL
	AND EXISTS(
                                SELECT p.pid
                                FROM pages AS p
                                WHERE  p.qid = f.qid
                                AND NOT EXISTS 
                                (SELECT fp.fid 
                                        FROM formpages AS fp 
                                        WHERE fp.fid = f.fid 
                                        AND fp.pid = p.pid))
	ORDER BY f.fid ASC";

$fs = $db->GetAll($sql);

xhtml_table($fs,array('fid','description','qdesc','link'),array(T_('Form ID'),T_('Form'),T_('Questionnaire'),T_('Delete form and allow importing again')));

xhtml_foot();

?>
