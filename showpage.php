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

if (isset($_GET['bgid'])){

	include_once("config.inc.php");
	include_once("db.inc.php");
	include("functions/functions.image.php");
	
	global $db;

	$bgid = intval($_GET['bgid']);

	$sql=  "SELECT *
		FROM boxesgroupstypes
		WHERE bgid = '$bgid'";

	$rows = $db->GetAll($sql);

	if (empty($rows)) exit;

	$pid = $rows[0]['pid'];

	$sql = "SELECT image 
		FROM pages
		WHERE pid = $pid";

	$row = $db->GetRow($sql);

	if (empty($row)) exit;

	$sql = "SELECT MIN(`tlx`) as tlx,MIN(`tly`) as tly,MAX(`brx`) as brx,MAX(`bry`) as bry
		FROM `boxesgroupstypes`
		WHERE bgid = '$bgid'";

	$crop = $db->GetRow($sql);

	$image = imagecreatefromstring($row['image']);

	header("Content-type: image/png");

	if (!empty($rows))
		imagepng(crop(overlay($image,$rows),$crop));
	else
		imagepng($image);


}
else if (isset($_GET['pid']))
{
	include("config.inc.php");
	include("db.inc.php");
	include("functions/functions.image.php");
	
	global $db;
	
	$pid = intval($_GET['pid']);


	if (isset($_GET['fid']))
	{
		$fid = intval($_GET['fid']);

		$sql = "SELECT image,offx,offy 
			FROM formpages
			WHERE pid = $pid and fid = $fid";
	
		$row = $db->GetRow($sql);

		if (empty($row)) exit;

		$im = imagecreatefromstring($row['image']);

		if (isset($_GET['bid']))
		{
			$bid = intval($_GET['bid']);
			$sql = "SELECT tlx,tly,brx,bry
				FROM boxes
				WHERE bid = '$bid'";
			$box = $db->GetRow($sql);

			/*
			include("functions/functions.ocr.php");
			$im = st_ocr($im,calcoffset($box,$row['offx'],$row['offy']));
			header("Content-type: image/png");
			imagepng($im);
			exit;
			 */
			
			$box['tlx']+= BOX_EDGE;
			$box['tly']+= BOX_EDGE;
			$box['brx']-= BOX_EDGE;
			$box['bry']-= BOX_EDGE;

			header("Content-type: image/png");
			imagepng(crop($im,calcoffset($box,$row['offx'],$row['offy'])));
		}
		else
		{
			if (isset($_GET['zoom']))
			{
				header("Content-type: image/png");
				echo ($row['image']);
			}
			else
			{
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
	}
	else
	{
		$sql = "SELECT image 
			FROM pages
			WHERE pid = $pid";
	
		$row = $db->GetRow($sql);

		if (empty($row)) exit;

		header("Content-Type: image/png");
		echo ($row['image']);

	}

}


?>
