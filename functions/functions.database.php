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
include_once(dirname(__FILE__).'/../config.inc.php');
include_once(dirname(__FILE__).'/../db.inc.php');

/* Sort box groups by pageid then box tly tlx
 *
 */
function sort_order_pageid_box($qid)
{
	global $db;


	$db->StartTrans();

	$sql = "SELECT b.bgid as bgid , p.pidentifierval, bx.tlx, bx.tly
		FROM `boxgroupstype` AS b, pages AS p, boxes as bx
		WHERE p.qid = '$qid'
		AND b.bgid = bx.bgid
		AND bx.pid = p.pid
		GROUP BY b.bgid
		ORDER BY p.pidentifierval ASC , bx.tly ASC , bx.tlx ASC";

	$all = $db->GetAll($sql);

	$i = 0;
	foreach ($all as $row)
	{
		$sql = "UPDATE boxgroupstype
			SET sortorder = '$i'
			WHERE bgid = '{$row['bgid']}'";
		$db->Execute($sql);
		$i++;
	}
	$db->CompleteTrans();
}

/* Sort box groups by their variable name
 *
 */
function sort_order_varname($qid)
{
	global $db;

	$db->StartTrans();
	$sql = "SELECT b.bgid as bgid
		FROM `boxgroupstype` AS b, pages AS p
		WHERE p.qid = '$qid'
		AND b.pid = p.pid
		ORDER BY b.varname ASC";
	$all = $db->GetAll($sql);

	$i = 0;
	foreach ($all as $row)
	{
		$sql = "UPDATE boxgroupstype
			SET sortorder = '$i'
			WHERE bgid = '{$row['bgid']}'";
		$db->Execute($sql);
		$i++;
	}

	$db->CompleteTrans();
}

/*
 * Assign the next free form to a verifier
 */
function assign_to($vid)
{
	global $db;
	$db->StartTrans();

	//only assign a form if none currently assigned
	$sql = "SELECT f.fid as fid
		FROM forms as f
		WHERE f.done IN (0,2,3)
		AND f.assigned_vid = '$vid'";

	$rs = $db->GetAll($sql);

	if (!empty($rs))
	{
		if (count($rs) == 1)
		{
			return $rs[0]['fid'];
		}
		else if (count($rs) > 1)
		{
			print T_("ERROR: Multiple forms assigned, please see a technical officer");
			exit();
		}
	}

	$fid = false;

	//check for supervisor forms first
	$sql = "SELECT f.fid AS fid
		FROM forms AS f
		JOIN supervisorquestionnaire AS v ON (v.vid = '$vid' AND f.qid = v.qid) ";
	$sql .= " WHERE f.done =2
		AND f.assigned_vid IS NULL ";
	$sql .= " ORDER BY f.fid ASC LIMIT 1";
	$rs = $db->GetRow($sql);
	
	//check for revise forms second
	$sql = "SELECT f.fid AS fid
		FROM forms AS f
		WHERE f.done = 4 AND f.assigned_vid = '$vid'
		ORDER BY f.fid ASC LIMIT 1";
	$rs = $db->GetRow($sql);
	if (!empty($rs))
	{
		if(!reload_session_from_database($rs['fid'], $vid)) {
			print T_("ERROR: reload_session_from_database fid=".$rs['fid']." vid=".$vid.", please see a technical officer");
			exit();
		} else {
			print T_("SUCCESS: reload forms fid=".$rs['fid']." from database for revise.");
		}
	}
	else
	{
		//only get forms that are assigned to this verifier
		$sql = "SELECT f.fid AS fid
			FROM forms AS f
			JOIN verifierquestionnaire AS v ON (v.vid = '$vid' AND f.qid = v.qid) ";

		if (!MISSING_PAGE_ASSIGN){
			$sql .= " LEFT JOIN missingpages AS m ON (f.fid = m.fid) ";
		}

		$sql .= " WHERE ((f.done = 0 AND f.assigned_vid IS NULL) OR
			(f.done = 3 AND f.assigned_vid IS NULL AND f.assigned_vid2 != '$vid')) ";

		if (!MISSING_PAGE_ASSIGN) {
			$sql .= " AND m.fid IS NULL ";
		}

		if (!VERIFY_WITH_MISSING_PAGES)
		{
			$sql .= "AND NOT EXISTS(
				SELECT p.pid
				FROM pages AS p
				WHERE  p.qid = f.qid
				AND NOT EXISTS 
				(SELECT fp.fid 
					FROM formpages AS fp 
					WHERE fp.fid = f.fid 
					AND fp.pid = p.pid))";
		}

		$sql .= " ORDER BY f.done,f.fid ASC LIMIT 1";
		//var_dump($sql);exit();

		$rs = $db->GetRow($sql);
	}

	if (!empty($rs))
	{
		$fid = $rs['fid'];
		
		$sql = "UPDATE verifiers
			SET currentfid = '$fid'
			WHERE vid = '$vid'";

		$sql = "UPDATE forms
			SET assigned_vid = '$vid'
			WHERE fid = '$fid'";

		$db->Execute($sql);
	}

	$db->CompleteTrans();

	return $fid;

}

function assign_to_merge($vid)
{
	global $db;

	$db->StartTrans();

	//only get the next form where exactly 2 people have verified it

	$sql = "SELECT fid
		FROM worklog
		GROUP BY fid
		HAVING COUNT(*) = 2
		LIMIT 1";

	$rs = $db->GetRow($sql);

	$fid = false;

	if (!empty($rs))
	{
		$fid = $rs['fid'];
	}

	$db->CompleteTrans();

	return $fid;
}

function get_vid()
{
	global $db;

	$sql = "SELECT vid
		FROM verifiers
		WHERE http_username = '{$_SERVER['PHP_AUTH_USER']}'";

	$rs = $db->GetRow($sql);

	if (empty($rs))
		return false;//invalid user
	else
	{
		return $rs['vid'];
	}
}

function get_fid($vid = "")
{
	global $db;

	$sql ="";

	$sql = "SELECT fid
		FROM forms
		WHERE assigned_vid = '$vid'
		AND done IN (0,2,3,4)";

	$rs = $db->GetRow($sql);

	if (empty($rs))
		return false;//invalid user
	else
	{
		if (empty($rs['fid']))
		{
			//assign a form
		}else
		{
			return $rs['fid'];
		}
	}
	return false;
}

function detect_differences()
{
	global $db;

	$sql = "SELECT fid
		FROM worklog
		GROUP BY fid
		HAVING COUNT(*) = 2";

	$r = $db->GetAll($sql);

	foreach ($r as $f)
	{
		$fid = $f['fid'];
		print "$fid: <br/>";
		
		$sql = "SELECT vid 
			FROM worklog
			WHERE fid = $fid";

		$vids = $db->GetAll($sql);

		$vid1 = $vids[0]['vid'];
		$vid2 = $vids[1]['vid'];

		$q = get_qid_description($fid);
		$qid = $q['qid'];

		$sql = "SELECT b.bid AS bid
			FROM boxes AS b
			JOIN boxgroupstype as bg ON (bg.bgid = b.bgid)
			JOIN pages as p ON (p.pid = b.pid)
			LEFT JOIN formboxverifychar AS c2 ON c2.fid = '$fid'
			AND c2.vid = '$vid1'
			AND c2.bid = b.bid
			LEFT JOIN formboxverifychar AS c ON c.fid = '$fid'
			AND c.vid = '$vid2'
			AND c.bid = b.bid
			WHERE (bg.btid  =1  or bg.btid = 2)
			AND p.qid = '$qid'
			AND c.val != c2.val";

		$diffs = $db->GetAll($sql);

		foreach($diffs as $diff)
		{
			print_r($diff);
			print "<br/>";
		}
	}
}

function get_qid_description($fid)
{
	global $db;

	$sql = "SELECT f.qid,f.description,q.double_entry
		FROM `forms` as f, questionnaires as q 
		WHERE f.fid = '$fid'
		AND q.qid = f.qid";

	$rs = $db->GetRow($sql);

	return $rs;
}

function reload_session_from_database($fid, $vid)
{
	global $db;

	$fid = intval($fid);
	$vid = intval($vid);

	$qid_desc = get_qid_description($fid);
	if (empty($qid_desc)) {
		return false;
	}

	$qid = intval($qid_desc['qid']);

	$sql = "SELECT b.bid as bid,
			b.tlx as tlx,
			b.tly as tly,
			b.brx as brx,
			b.bry as bry,
			b.pid as pid,
			bg.btid as btid,
			b.bgid as bgid,
			$fid as fid,
			bg.sortorder as sortorder,
			fb.filled,
			CASE
				WHEN d.fid IS NOT NULL THEN d.val
				WHEN c.fid IS NOT NULL THEN c.val
				ELSE NULL
			END as val
			FROM boxes AS b
			JOIN boxgroupstype as bg ON (bg.bgid = b.bgid AND bg.btid > 0)
			JOIN pages as p ON (p.pid = b.pid AND p.qid = '$qid')
			LEFT JOIN formboxes as fb ON (fb.bid = b.bid AND fb.fid = '$fid')
			LEFT JOIN formboxverifychar AS c ON (
				c.fid = '$fid'
				AND c.bid = b.bid
				AND c.vid = (
					SELECT c2.vid
					FROM formboxverifychar c2
					WHERE c2.fid = '$fid'
					AND c2.bid = b.bid
					ORDER BY c2.fbvcid DESC
					LIMIT 1
				)
			)
			LEFT JOIN formboxverifytext AS d ON (
				d.fid = '$fid'
				AND d.bid = b.bid
				AND d.vid = (
					SELECT d2.vid
					FROM formboxverifytext d2
					WHERE d2.fid = '$fid'
					AND d2.bid = b.bid
					ORDER BY d2.fbvtid DESC
					LIMIT 1
				)
			)
			ORDER BY bg.sortorder ASC";

	$sql2 = "SELECT b.bgid,
			0 as done,
			MIN(b.pid) as pid,
			bg.varname,
			bg.btid
			FROM boxes as b, boxgroupstype as bg, pages as p
			WHERE p.pid = b.pid
			AND bg.bgid = b.bgid
			AND p.qid = '$qid'
			AND bg.btid > 0
			GROUP BY bg.bgid
			ORDER BY bg.sortorder ASC";

	$sql3 = "SELECT b.pid,
			MIN(b.bgid) as bgid,
			0 as done,
			fp.width,
			fp.height,
			fp.fid
			FROM boxes as b
			JOIN pages as p ON (p.qid = '$qid' AND b.pid = p.pid)
			JOIN boxgroupstype as bg ON (bg.bgid = b.bgid)
			LEFT JOIN formpages as fp ON (fp.fid = '$fid' AND fp.pid = p.pid)
			GROUP BY b.pid
			ORDER BY MIN(bg.sortorder) ASC";

	$boxes = $db->GetAssoc($sql);
	$boxgroups = $db->GetAssoc($sql2);
	$pages = $db->GetAssoc($sql3);

	if (empty($boxes)) {
		return false;
	}

	unset($_SESSION['boxgroups']);
	unset($_SESSION['pages']);
	unset($_SESSION['boxes']);
	session_unset();
	$_SESSION['boxes'] = $boxes;
	$_SESSION['boxgroups'] = $boxgroups;
	$_SESSION['pages'] = $pages;
	$_SESSION['assigned'] = time();
	$_SESSION['review_mode'] = 0;

	return true;
}
?>
