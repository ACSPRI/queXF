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
		FROM `boxgroupstype` AS b, pages AS p, boxgroups AS bg, boxes bx
		WHERE p.qid = '$qid'
		AND b.bgid = bg.bgid
		AND bg.bid = bx.bid
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


/*
 * Assign the next free form to a verifier
 */

function assign_to($vid)
{
	global $db;

	$db->StartTrans();

/*
	$sql = "SELECT f.fid as fid
		FROM forms as f
		WHERE f.done = 0 and f.assigned_vid is NULL
		ORDER BY f.fid ASC 
		LIMIT 1";
 */

	//only assign a form if none currently assigned
	//
	$sql = "SELECT f.fid as fid
		FROM forms as f
		WHERE f.done = 0
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
			print "ERROR: Multiple forms assigned, please see a technical officer";
			exit();
		}
	}


	//only get forms that are assigned to this verifier

	$sql = "SELECT f.fid AS fid
		FROM forms AS f, verifierquestionnaire AS v
		WHERE f.done =0
		AND f.assigned_vid IS NULL
		AND f.qid = v.qid
		AND v.vid = '$vid'
		ORDER BY f.fid ASC
		LIMIT 1";


	$rs = $db->GetRow($sql);

	$fid = false;

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
		AND done = 0";

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
		FROM boxesgroupstypes AS b
		LEFT JOIN formboxverifychar AS c2 ON c2.fid = '$fid'
		AND c2.vid = '$vid1'
		AND c2.bid = b.bid
		LEFT JOIN formboxverifychar AS c ON c.fid = '$fid'
		AND c.vid = '$vid2'
		AND c.bid = b.bid
		WHERE (b.btid  =1  or b.btid = 2)
		AND b.qid = '$qid'
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

	$sql = "SELECT qid,description
		FROM `forms` 
		WHERE fid = '$fid'";

	$rs = $db->GetRow($sql);

	return $rs;
}



?>
