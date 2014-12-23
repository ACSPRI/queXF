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


include_once("config.inc.php");
include_once("db.inc.php");
include_once("functions/functions.xhtml.php");


/* Create a box group in the DB
 */
function updateboxgroup($bgid,$width,$varname,$btid)
{
	global $db;
	$db->StartTrans();

	$sql = "UPDATE boxgroupstype
		SET btid = '$btid', width = '$width', varname = '$varname'
		WHERE bgid = '$bgid'";

	$db->Execute($sql);

	$db->CompleteTrans();
}

/**
 * When boxes are evenly spaced, boxes are created inbetween
 * Delete the inbetween boxes
 */
function deleteinbetween($bgid)
{
	global $db;
	$db->StartTrans();

	$sql = "SELECT bid 
		FROM boxes 
		WHERE bgid = '$bgid'";

	$rows = $db->GetAll($sql);

	$rc = 1;
	foreach($rows as $row)
	{
		if (($rc % 2) == 0 && next($rows)) // if even and there is at least one more box
		{
			$sql = "DELETE
				FROM boxes
				WHERE bid = '{$row['bid']}'";
	
			$db->Execute($sql);

		}
		$rc++;
	}

	$db->CompleteTrans();

	return $bgid;

}


/**
 * Delete a box from a boxgroup
 */
function deletebox($bid)
{
	global $db;

	$sql = "DELETE
		FROM boxes
		WHERE bid = '$bid'";
	
	$db->Execute($sql);


	return $bid;

}



/* Delete a box group in the DB
 */
function deleteboxgroup($bgid)
{

	global $db;
	$db->StartTrans();

	$sql = "SELECT bid 
		FROM boxes 
		WHERE bgid = '$bgid'";

	$rows = $db->GetAll($sql);

	foreach($rows as $row)
	{
		$sql = "DELETE
			FROM boxes
			WHERE bid = '{$row['bid']}'";

		$db->Execute($sql);
	}

	$sql = "DELETE
		FROM boxgroupstype
		WHERE bgid = '$bgid'";

	$db->Execute($sql);

	$db->CompleteTrans();

	return $bgid;
}

if (isset($_GET['deletebgid']))
{
	deleteboxgroup(intval($_GET['deletebgid']));
	exit();
}

if (isset($_GET['deletebid']))
{
	deletebox(intval($_GET['deletebid']));
	exit();
}


if (isset($_GET['deleteinbetween']))
{
	deleteinbetween(intval($_GET['deleteinbetween']));
}


if (isset($_GET['bgid']) && isset($_GET['btid']))
{
	updateboxgroup(intval($_GET['bgid']),1,'',$intval($_GET['btid']));
}


if (isset($_POST['submit']))
{
	$bgid = $_POST['bgid'];
	$width = $_POST['width'];
	$varname = $_POST['varname'];
	$btid = $_POST['btid'];
	updateboxgroup($bgid,$width,$varname,$btid);
}



if (isset($_GET['bgid']) || isset($_GET['bid']))
{
	xhtml_head(T_("Modify box"));

	global $db;

	if (isset($_GET['bid'])){
		$bid = intval($_GET['bid']);
		$sql = "SELECT bgid 
			FROM boxes
			WHERE bid = '$bid'";
		$row = $db->GetRow($sql);
		$bgid = $row['bgid'];
	}else
		$bgid = intval($_GET['bgid']);

	
	$sql = "SELECT btid,varname,width
		FROM boxgroupstype
		WHERE bgid = '$bgid'";

	$row = $db->GetRow($sql);
	$btid = $row['btid'];
	$varname = $row['varname'];
	$width = $row['width'];

	//display the cropped boxes
	print "<img src=\"showpage.php?bgid=$bgid\"/>";

	?><form method="post" action="<?php echo $_SERVER['PHP_SELF'] . "?bgid=$bgid";?>"><?php

	//display group selection
	$sql = "SELECT description,btid as value, CASE WHEN btid = '$btid' THEN 'selected=\'selected\'' ELSE '' END AS selected
		FROM boxgrouptypes";

	$rs = $db->GetAll($sql);

	print T_("Group type:");
	translate_array($rs,array("description"));
	display_chooser($rs,"btid","btid",false,false,false,false,false);

	//display variable name
	?><br/><?php echo T_("Variable name:"); ?> <input type="text" size="12" value="<?php echo $varname; ?>" name="varname"><br/><?php

	//display width
	?><?php echo T_("Width:"); ?> <input type="text" size="12" value="<?php echo $width; ?>" name="width"><br/><?php

	?><input  TYPE="hidden" VALUE="<?php echo $bgid; ?>" NAME="bgid"><br/><input type="submit" value="<?php echo T_("Submit"); ?>" name="submit"/></form><?php

	?><p><a href="<?php echo $_SERVER['PHP_SELF'] . "?deletebgid=$bgid";?>"><?php echo T_("Delete this group"); ?></a></p>
		<p><a href="<?php echo $_SERVER['PHP_SELF'] . "?deleteinbetween=$bgid&amp;bgid=$bgid";?>"><?php echo T_("Delete in between boxes"); ?></a></p>
	<?php

	xhtml_foot();
}




?>
