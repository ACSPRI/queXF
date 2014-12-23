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


include(dirname(__FILE__) . "/../functions/functions.process.php");
include_once(dirname(__FILE__) . "/../lang.inc.php");
include_once(dirname(__FILE__) . "/../db.inc.php");
include_once(dirname(__FILE__) . "/../functions/functions.ocr.php");
include_once(dirname(__FILE__) . "/../functions/functions.image.php");

function update_callback($buffer)
{
	global $process_id;

	process_append_data($process_id,$buffer);

	return ""; //empty buffer
}


//get the arguments from the command line (directory to process, and this process_id)
if ($argc != 3) exit("No the right parameters");

$kb = $argv[1];
$process_id = $argv[2];

//register an exit function which will tell the database we have ended
register_shutdown_function('end_process',$process_id);

//start a loop importing the directory, sleeping for a while, then checking if the process
//needs to be killed and trying again

ob_start('update_callback',2);

print T_("Processing KB") . ": $kb";


$sql = "SELECT ocrprocessid,fid,vid,bid,vid,val,kb
        FROM ocrprocess
        WHERE kb = '$kb'";

$rs = $db->GetAll($sql);

$completed = 1;

foreach($rs as $o)
{
	if (is_process_killed($process_id))
	{
		$completed = 0;
		break;
	}

	$db->StartTrans();

        $fid = $o['fid'];
        $bid = $o['bid'];
        $val = $o['val'];
        $vid = $o['vid'];

        $sql = "SELECT pid,tlx,tly,brx,bry
                FROM boxes
                WHERE bid = '$bid'";

        $box = $db->GetRow($sql);

        $sql = "SELECT * 
                FROM formpages
                WHERE pid = '{$box['pid']}' and fid = '$fid'";

        $row = $db->GetRow($sql);

        if ($row['filename'] == '')
        {
          $im = imagecreatefromstring($row['image']);
        }
        else
        {
          $im = imagecreatefrompng(IMAGES_DIRECTORY . $row['filename']);
        }

        $row['width'] = imagesx($im);
        $row['height'] = imagesy($im);

        $box['tlx']+= BOX_EDGE;
        $box['tly']+= BOX_EDGE;
        $box['brx']-= BOX_EDGE;
        $box['bry']-= BOX_EDGE;

        $timage = crop($im,applytransforms($box,$row));

        $ktimage = kfill_modified($timage,5);

        $ttimage = remove_boundary_noise($ktimage,2);

        $kttimage = resize_bounding($ttimage);

        $i = thinzs_np($kttimage);

        $t = sector_distance($i);

        $sql = "INSERT INTO ocrtrain (ocrtid,val,f1,f2,f3,f4,f5,f6,f7,f8,f9,f10,f11,f12,f13,f14,f15,f16,fid,vid,bid,kb)
                VALUES (NULL,'$val','{$t[0][1]}','{$t[0][2]}','{$t[0][3]}','{$t[0][4]}','{$t[0][5]}','{$t[0][6]}','{$t[0][7]}','{$t[0][8]}','{$t[0][9]}','{$t[0][10]}','{$t[0][11]}','{$t[0][12]}','{$t[1][1]}','{$t[1][2]}','{$t[1][3]}','{$t[1][4]}','$fid','$vid','$bid','$kb')";

        $db->Execute($sql);

	$sql = "DELETE from ocrprocess
		WHERE ocrprocessid = '{$o['ocrprocessid']}'";
	
	$db->Execute($sql);
	

	print T_("Trained") . ": $val "	 . T_("to knowledge base") . ": $kb";


	$db->CompleteTrans();
}

if ($completed == 1)
{
	$sql = "SELECT count(*) as c
		FROM ocrprocess
		WHERE kb = '$kb'";

	$rs = $db->GetRow($sql);

	if ($rs['c'] == 0)
	{
		generate_kb($kb);
		print T_("Generated KB");
	}
	else
		print T_("Did not generate KB as not all records trained");
}
else
	print T_("Did not generate KB as not all records trained");

ob_get_contents();
ob_end_clean();

?>
