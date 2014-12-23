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


if (isset($_GET['bgid'])){

	include_once("config.inc.php");
	include_once("db.inc.php");
	include("functions/functions.image.php");
	
	global $db;

	$bgid = intval($_GET['bgid']);

	$sql=  "SELECT *
		FROM boxes
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
		FROM boxes
		WHERE bgid = '$bgid'";

	$crop = $db->GetRow($sql);

	$image = imagecreatefromstring($row['image']);

	header("Content-type: image/png");

	if (!empty($rows))
		imagepng(crop(overlay($image,$rows),$crop));
	else
		imagepng($image);


}
else if (isset($_GET['filename']))
{
	$im = imagecreatefrompng($_GET['filename']);
	header('Content-type: image/png');
	imagepng($im);
	imagedestroy($im);
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

		$sql = "SELECT * 
			FROM formpages
			WHERE pid = $pid and fid = $fid";
	
		$row = $db->GetRow($sql);

		if (empty($row)) exit;

    if ($row['filename'] == '')
    {
      $im = imagecreatefromstring($row['image']);
    }
    else
    {
      $im = imagecreatefrompng(IMAGES_DIRECTORY . $row['filename']);
    }

		if (isset($_GET['bid']))
		{
			$bid = intval($_GET['bid']);
			$sql = "SELECT tlx,tly,brx,bry
				FROM boxes
				WHERE bid = '$bid'";
			$box = $db->GetRow($sql);

			$row['width'] = imagesx($im);
			$row['height'] = imagesy($im);
			
			//$im = st_ocr($im,calcoffset($box,$row['offx'],$row['offy']));
			//header("Content-type: image/png");
			//imagepng($im);
			//exit;
			$box['tlx']+= BOX_EDGE;
			$box['tly']+= BOX_EDGE;
			$box['brx']-= BOX_EDGE;
			$box['bry']-= BOX_EDGE;

			header("Content-type: image/png");

			$timage = crop($im,applytransforms($box,$row));

			if(!isset($_GET['a'])){ imagepng($timage); die();}
//			image_to_text($timage);
		
			include("functions/functions.ocr.php");

			$ktimage = kfill_modified($timage,5);
			//invert_image($timage);

//			if(isset($_GET['b'])){ imagepng($ktimage); die();}
//			 imagepng($ktimage); die();
//			image_to_text($ktimage);

			$ttimage = remove_boundary_noise($ktimage,2);

//			if(isset($_GET['c'])){ imagepng($ttimage); die();}
//			 imagepng($ttimage); die();
//			image_to_text($ttimage);

			$kttimage = resize_bounding($ttimage);	

//			if(isset($_GET['d'])){ imagepng($kttimage); die();}
//			 imagepng($kttimage); die();


			$i = thinzs_np($kttimage);


			imagepng($i);
			//imagepng(thin_b($timage));
			die();
		
			$box['tlx']+= BOX_EDGE;
			$box['tly']+= BOX_EDGE;
			$box['brx']-= BOX_EDGE;
			$box['bry']-= BOX_EDGE;

			header("Content-type: image/png");
			imagepng(crop($im,applytransforms($box,$row)));
		}
		else
		{
			if (isset($_GET['zoom']))
			{
				header("Content-type: image/png");
				imagepng($im);
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
