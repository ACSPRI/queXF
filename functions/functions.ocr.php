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

function ST_Guess($image,$numbersonly = false)
{
	include_once(dirname(__FILE__).'/../config.inc.php');
	include_once(dirname(__FILE__).'/../db.inc.php');

	global $db;

	$sql = "SELECT val,image
		FROM ocrtrainst";

	if ($numbersonly)
		$sql .= " WHERE (val = '0' 
				or val = '1'
				or val = '2'
				or val = '3'
				or val = '4'
				or val = '5'
				or val = '6'
				or val = '7'
				or val = '8'
				or val = '9') ";

	$images = $db->CacheGetAll($sql);

	$dmax = 1024;
	$cmin = 1024*1024;
	$val = "";
	foreach($images as $i)
	{
		$im = imagecreatefromstring($i['image']);
		$ct = ST_Cost($image,$im);
		if ($ct < $cmin)
		{
			$val = $i['val'];
			$cmin = $ct;
		}
	}

	return $val;
}


/**
 * Implmentatino of the ST_Cost algorithm from
 *  "A new shape transformation approach to handwritten character recognition", N Liolios, E. Kavallieratou, N. Fakotakis and G. Kokkinakis
 */
function ST_Cost($im1,$im2)
{
	$width = imagesx($im1);
	$height = imagesy($im1);

	//pixels in im1, not common to both
	$im1m = array();

	//pixes in im2, not common to both
	$im2m = array();


	// calculate the pixels in each image not common to both
	for ($i = 0; $i < $width; $i++) {
		for ($j = 0; $j < $height; $j++) {
			$im1rgb = (imagecolorat($im1,$i,$j) == 0);
			$im2rgb = (imagecolorat($im2,$i,$j) == 0);
			if ($im1rgb && !$im2rgb) $im1m[] = array($i,$j);
			if ($im2rgb && !$im1rgb) $im2m[] = array($i,$j);
		}
	}


	$cost = 0; //cost of transforming im1 to im2

	$dmax = 1024;

	//calcuate euclidian distance between each pixel that is not common to both images
	foreach($im1m as $i1)
	{
		$dmin = $dmax;
		foreach($im2m as $i2)
		{
			//euclidian distance between pixels
			$d = sqrt((($i1[0]-$i2[0])*($i1[0]-$i2[0]))+(($i1[1]-$i2[1])*($i1[1]-$i2[1])));
			if ($d < $dmin)
				$dmin = $d;
		}
		$cost += $dmin;
	}


	return $cost;
}



/**
 *From NIST morphchr.c
 *
 *
 */
/******************************************************************/
/* erode a one bit per byte char image, inp. Result is out which  */
/* must be disjoint with inp. The data in out before the call is  */
/* irrelevant, and is zeroed and filled by this routine. iw and   */
/* ih are the width and height of the image in pixels. Both inp   */
/* and out point to iw*ih bytes                                   */
/******************************************************************/
 
function erode_charimage($image)
{
  $xdim = imagesx($image);    
  $ydim = imagesy($image);
  $im2 = imagecreate($xdim,$ydim);
  imagecopy($im2,$image,0,0,0,0,$xdim,$ydim);
  $white = imagecolorallocate($im2, 255, 255, 255);
 /* for true pixels. kill pixel if there is at least one false neighbor */
  for ( $row = 0 ; $row < 32 ; $row++ )
  {
      for ( $col = 0 ; $col < 32 ; $col++ )
      {  
         if (imagecolorat($image,$row,$col) == 0)      /* erode only operates on true pixels */
         {
            /* more efficient with C's left to right evaluation of     */
            /* conjuctions. E N S functions not executed if W is false */
            if (!(get_west8 ($image,$row,$col) &&
                  get_east8 ($image,$row,$col,32) &&
                  get_north8($image,$row,$col) &&
		  get_south8($image,$row,$col,32)))
		    imagesetpixel($im2,$row,$col,$white); //set to background
	    }
      }
  }
  return $im2;
}

/******************************************************************/
/* dilate a one bit per byte char image, inp. Result is out which  */
/* must be disjoint with inp. The data in out before the call is  */
/* irrelevant, and is zeroed and filled by this routine. iw and   */
/* ih are the width and height of the image in pixels. Both inp   */
/* and out point to iw*ih bytes                                   */
/******************************************************************/
 
function dilate_charimage($image)
{
   $black = imagecolorallocate($image, 0, 0, 0);
   /* for all pixels. set pixel if there is at least one true neighbor */
   for ( $row = 0 ; $row < 32 ; $row++ )
     {
      for ( $col = 0 ; $col < 32 ; $col++ )
      {  
         if (imagecolorat($image,$row,$col))     /* pixel is already true, neighbors irrelevant */
         {
            /* more efficient with C's left to right evaluation of     */
            /* conjuctions. E N S functions not executed if W is false */
            if (get_west8 ($image,$row,$col) ||
                  get_east8 ($image,$row,$col,32) ||
                  get_north8($image,$row,$col) ||
                  get_south8($image,$row,$col,32))
               imagesetpixel($image,$row,$col,$black); //set to black
         }
      }  
     }
   return $image;
}


/**
 *From NIST morphchr.c
 */
function get_south8($im, $x, $y, $height)
{
   if ($y >= $height-1) /* catch case where image is undefined southwards   */
      return 0;     /* use plane geometry and return false.             */

   return (imagecolorat($im,$x,($y +1)) == 0);
}

/**
 *From NIST morphchr.c
 */
function get_north8($im, $x, $y)
{
   if ($y < 1)     /* catch case where image is undefined northwards     */
      return 0;     /* use plane geometry and return false.              */

   return (imagecolorat($im,$x,($y - 1)) == 0);
}

/**
 *From NIST morphchr.c
 */
function get_east8($im, $x, $y, $width)
{
   if ($x >= $width-1) /* catch case where image is undefined eastwards    */
      return 0;     /* use plane geometry and return false.             */

   return (imagecolorat($im,($x+1),$y) == 0);
}


/**
 *From NIST morphchr.c
 */
function get_west8($im, $x, $y)
{
   if ($x < 1)     /* catch case where image is undefined westwards     */
      return 0;     /* use plane geometry and return false.              */

   return (imagecolorat($im,($x-1),$y) == 0);
}


/**
 * Shear an image by finding the left most pixel in the top row
 * then the leftmost in the bottom row, calculating the angle
 * and straightening all rows by shifting pixels across
 *
 *
 */
function image_shear($image)
{
	//find top left most black pixel location, 
	//and bottom left most black pixel location
	$xdim = imagesx($image);
	$ydim = imagesy($image);
	$tly = "";
	$tlx = "";
	$bly = "";
	$blx = "";

	for ($y = 0; $y < $ydim; $y++) {
		for ($x = 0; $x < $xdim; $x++) {
			if(!imagecolorat($image, $x, $y))
			{
				$tly = $y;
				$tlx = $x;
				break 2;
			}
		}
	}
	

	for ($y = ($ydim - 1); $y >= 0; $y--) {
		for ($x = 0; $x < $xdim; $x++) {
			if(!imagecolorat($image, $x, $y))
			{
				$bly = $y;
				$blx = $x;
				break 2;
			}
		}
	}


	$slope = (($tlx-$blx)/($bly-$tly));

	$im2 = imagecreate($xdim,$ydim);
	imagepalettecopy($im2,$image);
	$white = imagecolorallocate($im2, 255, 255, 255);


	$m = floor($ydim / 2);


	for ($y = 0; $y < $ydim; $y++) {
		$shift = ($y - $m) * $slope;
		for ($x = 0; $x < $xdim; $x++) {

			$ox = $x - $shift;
			if ($ox >= $xdim || $ox < 0) //if off the charts
				imagesetpixel($im2,$x,$y,$white); //set blank
			else
				imagesetpixel($im2,$x,$y,imagecolorat($image,$ox,$y));
		}
	}

	return $im2;
}




/**
 * stocr
 *
 */
function st_ocr($image,$a)
{
	//calc bounding box
	$bound = get_bounding_box($image,$a);

	//normalise image to a 20x32 image on a 32x32 box
	$image = normalise_image($image,$bound);


	//erode or dilate based on number of black pixels in image
	$npix = fillcount($image);    
	if($npix > 412){
		if($npix > 560) {
			$image = erode_charimage(erode_charimage($image));
         	}
		else {
			$image = erode_charimage($image);
         	}
      	}
      	else if ($npix < 256) {
		if($npix < 108){
			$image = dilate_charimage(dilate_charimage($image));
		}
		else {
			$image = dilate_charimage($image);
		}
	}

	//shear image
	$image = image_shear($image);	

	return $image;
}





/**
 * Normalise image to a size of 20x32 given the bounding box
 * to an image of size 32x32
 *
 */
function normalise_image($image,$bound)
{
	$nim = imagecreate(32,32);
	$white = imagecolorallocate($nim, 255, 255, 255);	
	imagepalettecopy($nim,$image);
	imagecopyresized($nim,$image,6,0,$bound['tlx'],$bound['tly'],20,32,($bound['brx']-$bound['tlx']),($bound['bry']-$bound['tly']));
	return $nim;
}


/**
 * Return the number of filled pixels in an image
 *
 */
function fillcount($image)
{
	$xdim = imagesx($image);
	$ydim = imagesy($image);
	$total = 0;
	for ($x = 0; $x < $xdim; $x++) {
		for ($y = 0; $y < $ydim; $y++) {
			if (!imagecolorat($image, $x, $y))
				$total++;
		}
	}
	return $total;
}



/**
 *Character recognition algorithm from here:
 *  http://www.cs.berkeley.edu/~fateman/kathey/char_recognition.html
 */

/**
 * Return the most likely character given the box data
 */
function quexf_ocr($boxes,$justnumbers = false)
{
	include_once(dirname(__FILE__).'/../config.inc.php');
	include_once(dirname(__FILE__).'/../db.inc.php');

	global $db;
	/*
	$sql = "SELECT * FROM octrain";
	$rs = $db->GetAll($sql);

	foreach($rs as $r)
	{
		$confidence = 0;
		foreach ($r as $rnam => $val)
		{
			if ($rnam != 'val')
			{
				$c = ($boxes[$rnam] - $val);
				$c = $c * $c;
										
			}	
		}
	}

	 */
	$sql = 	"SELECT val,
		(POW((r1 - {$boxes['r1']}),2) +
		POW((r2 - {$boxes['r2']}),2) +
		POW((r3 - {$boxes['r3']}),2) +
		POW((r4 - {$boxes['r4']}),2) +
		POW((r5 - {$boxes['r5']}),2) +
		POW((r6 - {$boxes['r6']}),2) +
		POW((r7 - {$boxes['r7']}),2) +
		POW((r8 - {$boxes['r8']}),2) +
		POW((r9 - {$boxes['r9']}),2) +
		POW((r10 - {$boxes['r10']}),2) +
		POW((r11 - {$boxes['r11']}),2) +
		POW((r12 - {$boxes['r12']}),2) +
		POW((r13 - {$boxes['r13']}),2) +
		POW((r14 - {$boxes['r14']}),2) +
		POW((r15 - {$boxes['r15']}),2) +
		POW((r16 - {$boxes['r16']}),2) +
		POW((r17 - {$boxes['r17']}),2) +
		POW((r18 - {$boxes['r18']}),2) +
		POW((r19 - {$boxes['r19']}),2) +
		POW((r20 - {$boxes['r20']}),2) +
		POW((r21 - {$boxes['r21']}),2) +
		POW((r22 - {$boxes['r22']}),2) +
		POW((r23 - {$boxes['r23']}),2) +
		POW((r24 - {$boxes['r24']}),2) +
		POW((r25 - {$boxes['r25']}),2) +
		POW((ratio - {$boxes['ratio']}),2)) as confidence
		FROM ocrtrain ";

	if ($justnumbers)
	{
		$sql .= " WHERE (val = '0' 
				or val = '1'
				or val = '2'
				or val = '3'
				or val = '4'
				or val = '5'
				or val = '6'
				or val = '7'
				or val = '8'
				or val = '9') ";
	}

	$sql .= " ORDER BY confidence ASC
		LIMIT 1";

	$rs = $db->GetRow($sql);

	print $rs['val'] . ": " . $rs['confidence'] . "<br/>";

	if ($rs['confidence'] < 20000)
		return $rs['val'];
	else 
		return " ";
}


/**
 * Return the bounding box of an image
 *
 *
 */
function get_bounding_box($image,$a)
{
	/**
	 * start with centre coordinates
	 */
	$box = array('tlx' => floor(($a['brx'] + $a['tlx']) / 2),
			'tly' => floor(($a['bry'] + $a['tly']) / 2),
			'bry' => floor(($a['bry'] + $a['tly']) / 2),
			'brx' => floor(($a['brx'] + $a['tlx']) / 2));


	for ($x = ($a['tlx'] + BOX_EDGE); $x < ($a['brx'] - BOX_EDGE); $x++) {
		for ($y = ($a['tly'] + BOX_EDGE); $y < ($a['bry'] - BOX_EDGE); $y++) {
			$rgb = imagecolorat($image, $x, $y);
			if (!$rgb) //0 is black
			{
				if ($x < $box['tlx']) $box['tlx'] = $x;
				if ($x > $box['brx']) $box['brx'] = $x;
				if ($y < $box['tly']) $box['tly'] = $y;
				if ($y > $box['bry']) $box['bry'] = $y;
			}
		}
	}
	return $box;	
}


/**
 * Return the ratio of black to white on a scale of 0 - 255
 * in each 25 portions of the image given the bounding box
 *
 */
function get_25_boxes($image,$a)
{

	$rows = ((int) (($a['bry'] - $a['tly']) / 5));
	$rowsm = ($a['bry'] - $a['tly']) % 5;
	$cols = ((int) (($a['brx'] - $a['tlx']) / 5));
	$colsm = ($a['brx'] - $a['tlx']) % 5;

	$row = array($rows + ($rowsm == 3 || $rowsm == 4),
		$rows + ($rowsm == 1 || $rowsm == 2),
		$rows + ($rowsm == 3 || $rowsm == 4),
		$rows + ($rowsm == 2 || $rowsm == 4),
		$rows + ($rowsm == 3 || $rowsm == 4));

	$col = array($cols + ($colsm == 3 || $colsm == 4),
		$cols + ($colsm == 1 || $colsm == 2),
		$cols + ($colsm == 3 || $colsm == 4),
		$cols + ($colsm == 2 || $colsm == 4),
		$cols + ($colsm == 3 || $colsm == 4));

	$box = array();

	$width = $a['brx'] - $a['tlx'];
	$height = $a['bry'] - $a['tly'];

	if ($width == 0 || $height == 0)
		$ratio = 0;
	else
		$ratio = $width / $height;
	
	if ($ratio <= 1) $box['ratio'] = floor($ratio * 128);
	else $box['ratio'] = floor(255 - ((1/$ratio) * 128));


	//print "Row: "; print_r($row);
	//print " Col: "; print_r($col);

	$rs = $a['tly'];
	foreach($row as $rn => $r)
	{
		$cs = $a['tlx'];
		foreach ($col as $cn => $c)
		{
			$total = 0;
			$count = 0;
			for ($x = $cs; $x < ($cs + $c); $x++) {
				for ($y = $rs; $y < ($rs + $r); $y++) {
					$rgb = imagecolorat($image, $x, $y);
					if (!$rgb) //0 is black
						$count++;
					$total++;
				}
			}
			$boxnum = "r" . (($cn + 1) + ($rn * 5));
			if ($total == 0)
				$box[$boxnum] = 0;
			else	
				$box[$boxnum] = floor(($count / $total) * 255);
			$cs += $c;
		}
		$rs += $r;		
	}


	return $box;	
}


/* Recognise ONE character from the given image
 *
 */
function ocr($image)
{
	//remove the border from the image
/*
	$w = imagesx($image);	
	$h = imagesy($image);
	$ni = imagecreate($w,$h);
	$bgc = imagecolorallocate($ni,255,255,255);
	imagecopy ($ni,$image,0,0,5,5,($w - 10),($h - 10));
	return tesseractocr($ni);
 */
	return tesseractocr($image);
}

/* Use tesseract for OCR engine
 *
 */
function tesseractocr($image)
{
	//output image to temp file
	$tmpfname = tempnam("/tmp","tesseract");
	imagewbmp($image,"$tmpfname.wbmp");

	//convert to tiff
	//exec("/usr/bin/convert $tmpfname.wbmp $tmpfname.tif");

	//convert with options
	//exec("/usr/bin/convert $tmpfname.wbmp -density 150x150 -resize 200% -fill white -tint 50 -level 20%,80%,1.0 -sharpen 0x2 -compress none -monochrome $tmpfname.tif");

	//convert with some options
	//exec("/usr/bin/convert $tmpfname.wbmp -density 150x150 -compress none -monochrome $tmpfname.tif");
	exec(CONVERT_BIN . " $tmpfname.wbmp -compress none -monochrome $tmpfname.tif");

	//call tesseract 
	exec(TESSERACT_BIN . " $tmpfname.tif $tmpfname");

	//read temp file created by tessearact (auto appends  .txt)
	$ocr = file_get_contents("$tmpfname.txt");

	//delete temp files
	unlink("$tmpfname.wbmp");
	unlink("$tmpfname.txt");
	unlink("$tmpfname.tif");
	unlink("$tmpfname");

	//return first character in file
	//print "OCR: $ocr<br/>";
	return substr(trim($ocr),0,1);

}








?>
