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

include("../config.inc.php");
include("../db.inc.php");
include("../functions/functions.image.php");
include("../functions/functions.client.php");

$cid = get_client_id();

if (isset($_GET['pid']) && $cid != false)
{
	global $db;
	
	$pid = intval($_GET['pid']);

	if (isset($_GET['fid']))
	{
		$fid = intval($_GET['fid']);

		$sql = "SELECT fp.image
			FROM formpages as fp, clientquestionnaire as cq, forms as f
			WHERE fp.pid = $pid AND fp.fid = $fid
			AND cq.cid = '$cid' AND cq.qid = f.qid AND f.fid = fp.fid";
	
		$row = $db->GetRow($sql);

		if (empty($row)) exit;

		$im = imagecreatefromstring($row['image']);

		$width = imagesx($im);
		$height = imagesy($im);
	
		$newwidth = DISPLAY_PAGE_WIDTH;
		$newheight = round($height * (DISPLAY_PAGE_WIDTH/$width));
		
		$thumb = imagecreatetruecolor($newwidth, $newheight);
		imagepalettecopy($thumb,$im);
		
		imagecopyresized($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		
		header("Content-type: image/png");
		imagepng($thumb);
	}
}
?>
