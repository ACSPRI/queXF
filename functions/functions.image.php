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

/**
 * Count the number of colours in the image
 * php function imagecolourstotal just counts the palette colours
 */
function imagecolorcount($image)
{
  $xdim = imagesx($image);
  $ydim = imagesy($image);
  
  $ca = array();

  // Use a 1/25th sample of the image
  for ($x = 0; $x < $xdim; $x+=5) {
    for ($y = 0; $y < $ydim; $y+=5) {
      $rgb = imagecolorat($image, $x, $y);
      if (!isset($ca[$rgb]))
        $ca[$rgb] = 1;
      else
        $ca[$rgb] += 1;
    }
  }
  return count($ca);
}


function convertmono($image)
{
  if (imagecolorcount($image) > 2)
  {
    //assume grayscale, convert to b&w no dithering
    $xdim = imagesx($image);
    $ydim = imagesy($image);
  
    $nimage = imagecreatetruecolor($xdim,$ydim);
    $white = imagecolorallocate($nimage, 255, 255, 255);
    $black = imagecolorallocate($nimage, 0, 0, 0);
    imagefill($nimage,0,0,$white);
  
    for ($x = 0; $x < $xdim; $x++) {
      for ($y = 0; $y < $ydim; $y++) {
        $rgb = imagecolorat($image, $x, $y);
        if ($rgb < IMAGE_THRESHOLD)
          imagesetpixel($nimage,$x,$y,$black);
      }
    }
    return $nimage;
  }
  return $image;
}




/**
 * Calculate the variance and mean of values in an array
 *
 * Using the incremental algorithm specified here: https://en.wikipedia.org/wiki/Algorithms_for_calculating_variance
 *
 * @param array $array An array of values
 * @return array The variance and mean of the values
 */
function variance_mean($array)
{
  $n = $mean = $M2 = 0;

  foreach($array as $x)
  {
    $n++;
    $delta = $x - $mean;
    $mean = $mean + ($delta / $n);
    $M2 = $M2 + $delta * ($x - $mean);
  }

  $variance = $M2/($n-1);

  return array($variance,$mean);
}

/**
 * Calculate the distance between two points on a plane
 * (Pythagorean theorem)
 *
 * @param float $x1
 * @param float $y1
 * @param float $x2
 * @param float $y2
 * @return float The distance
 */
function distance_plane($x1,$y1,$x2,$y2)
{
    return sqrt(pow(($x2 - $x1),2) + pow(($y2 - $y1),2));
}

/**
 * Integer division
 * 
 * @param $x 
 * @param $y
 * @return the result of x/y or FALSE if div by 0
 *
 * sourced from: http://us.php.net/manual/en/language.operators.arithmetic.php#76887
 */
function int_divide($x, $y) {
    if ($x == 0) return 0;
    if ($y == 0) return FALSE;
    return ($x - ($x % $y)) / $y;
}

function keytoindex($a)
{
	$b = array();
	$b[0] = $a['tlx'];
	$b[1] = $a['tly'];
	$b[2] = $a['trx'];
	$b[3] = $a['try'];
	$b[4] = $a['blx'];
	$b[5] = $a['bly'];
	$b[6] = $a['brx'];
	$b[7] = $a['bry'];
	return $b;
}

function indextokey($a)
{
	$b = array();
	$b['tlx'] = $a[0];
	$b['tly'] = $a[1];
	$b['trx'] = $a[2];
	$b['try'] = $a[3];
	$b['blx'] = $a[4];
	$b['bly'] = $a[5];
	$b['brx'] = $a[6];
	$b['bry'] = $a[7];
	return $b;
}

/**
* Validate a pixel location
*/
function validatepixel($a,$width,$height)
{
	if ($a[0] < 0) $a[0] = 0;
	if ($a[1] < 0) $a[1] = 0;
	if ($a[0] > $width) $a[0] = $width;
	if ($a[1] > $height) $a[1] = $height;
	return $a;
}

/* Use the presence of corner lines to see if the page is blank or not
 *
 */
function is_blank_page($image,$page)
{
	$b = array();

	$b[] = vertlinex($page['TL_VERT_TLX'],$page['TL_VERT_TLY'],$page['TL_VERT_BRX'],$page['TL_VERT_BRY'],$image,$page['VERT_WIDTH']);
	$b[] = horiliney($page['TL_HORI_TLX'],$page['TL_HORI_TLY'],$page['TL_HORI_BRX'],$page['TL_HORI_BRY'],$image,$page['HORI_WIDTH']);

	$b[] = vertlinex($page['TR_VERT_TLX'],$page['TR_VERT_TLY'],$page['TR_VERT_BRX'],$page['TR_VERT_BRY'],$image,$page['VERT_WIDTH']);
	$b[] = horiliney($page['TR_HORI_TLX'],$page['TR_HORI_TLY'],$page['TR_HORI_BRX'],$page['TR_HORI_BRY'],$image,$page['HORI_WIDTH']);

	$b[] = vertlinex($page['BL_VERT_TLX'],$page['BL_VERT_TLY'],$page['BL_VERT_BRX'],$page['BL_VERT_BRY'],$image,$page['VERT_WIDTH']);
	$b[] = horiliney($page['BL_HORI_TLX'],$page['BL_HORI_TLY'],$page['BL_HORI_BRX'],$page['BL_HORI_BRY'],$image,$page['HORI_WIDTH']);

	$b[] = vertlinex($page['BR_VERT_TLX'],$page['BR_VERT_TLY'],$page['BR_VERT_BRX'],$page['BR_VERT_BRY'],$image,$page['VERT_WIDTH']);
	$b[] = horiliney($page['BR_HORI_TLX'],$page['BR_HORI_TLY'],$page['BR_HORI_BRX'],$page['BR_HORI_BRY'],$image,$page['HORI_WIDTH']);

	$total = 0;
	foreach ($b as $key => $value)
		$total += $value;

	if ($total == 0) return true;
	return false;
}


/**
 * Determine if any of the page edges appear incorrect
 * by checking each corners rotation against the average
 *
 * If one looks abnormal, correct it by comparing it with the
 * expected edge length from the original page
 *
 * Will need to be run more than once if multiple edges are incorrect
 *
 * @param array $offset The edges as detected
 * @param array $page The expected page dimensions
 * @return array The corrected offset
 */

function validate_offset($offset,$page)
{
  //return all rotation values
  $rotate = calcrotate($offset,true);

  //calculate the variance and mean of the rotation
  $vm = variance_mean($rotate);

  if (DEBUG) print "validate_offset - pid:{$page['pid']} variance:{$vm[0]}";

  if ($vm[0] > 0.00003)
  {
    //if the variance in the rotation is greater than 0.00003
    //it is likely one of the corners was misdetected

    //find the rotated edge with the greatest difference from the mean
    $mean = $vm[1];

    $diff = 0;
    $rdiff = 0;
    $index = NULL;
    foreach($rotate as $key => $val)
    {
      if (abs($val - $mean) > $diff)
      {
        $diff = abs($val - $mean);
        $rdiff = $val - $mean;
        $index = $key;
      }
    }

    //index 0=top, 1=bottom, 2=left, 3=right

    //Now find the edge with the biggest length difference from the original
    //
    //start with finding the original edge lengths
    $edgesoriginal = array();
    $edgesoriginal[0] = distance_plane($page['tlx'],$page['tly'],$page['trx'],$page['try']);
    $edgesoriginal[1] = distance_plane($page['blx'],$page['bly'],$page['brx'],$page['bry']);
    $edgesoriginal[2] = distance_plane($page['tlx'],$page['tly'],$page['blx'],$page['bly']);
    $edgesoriginal[3] = distance_plane($page['trx'],$page['try'],$page['brx'],$page['bry']);

    //Edge lengths based on current offset
    $edgesnow = array();
    $edgesnow[0] = distance_plane($offset[0],$offset[1],$offset[2],$offset[3]);
    $edgesnow[1] = distance_plane($offset[4],$offset[5],$offset[6],$offset[7]);
    $edgesnow[2] = distance_plane($offset[0],$offset[1],$offset[4],$offset[5]);
    $edgesnow[3] = distance_plane($offset[2],$offset[3],$offset[6],$offset[7]);

    //calculate differences in edge lengths from original
    $diff = 0;
    $ddiff = 0;
    $dindex= NULL;
    foreach($edgesnow as $key => $val)
    {
      if (abs(($val - $edgesoriginal[$key])) > $diff)
      {
        $diff = abs(($val - $edgesoriginal[$key]));
        $dindex = $key;
        $ddiff = $val - $edgesoriginal[$key];
      }
    }

    //Choose the edge to correct based on the index (the corner with the most abnormal rotation)
    //and the dindex (the edge with the most abnormal length)
    //Then correct it by adjusting the length to be the expected length
    //NOTE: TODO: This assumes the image scale is static (1) - this should be adjusted to take account of image scale

    if (DEBUG) print " index:$index dindex:$dindex ddiff:$ddiff";

    if ($index == 2 && $dindex == 0) //tlx, offset 0
    {
      $offset[0] += $ddiff;
    }
    else if ($index == 0 && $dindex == 2) //tly, offset 1
    {
      $offset[1] += $ddiff;
    }
    else if ($index == 3 && $dindex == 0) //trx, offset 2
    {
      $offset[2] -= $ddiff;
    }
    else if ($index == 0 && $dindex == 3) //try, offset 3
    {
      $offset[3] += $ddiff;
    }
    else if ($index == 2 && $dindex == 1) //blx, offset 4
    {
      $offset[4] += $ddiff;
    }
    else if ($index == 1 && $dindex == 2) //bly, offset 5
    {
      $offset[5] -= $ddiff;
    }
    else if ($index == 3 && $dindex == 1) //brx, offset 6
    {
      $offset[6] -= $ddiff;
    }
    else if ($index == 1 && $dindex == 3) //bry, offset 7
    {
      $offset[7] -= $ddiff;
    }

  } 
  return $offset; 
}

//calculate the offset of an image given DCARF standard corner lines
//and original page id
//given an image and the tlx,tly,trx,try,blx,bly,brx,bry as an array
//
function offset($image,$a,$compare = 1,$page)
{
	$b = array();
	$c = array();
  $d = array();

	//temp only ?
	if (!isset($a['tlx']) && $compare == 1)
	{
		$c[0] = 0;
		$c[1] = 0;
		return $c;
	}

  //line edge detection
  $d[] = vertlinex($page['TL_VERT_TLX'],$page['TL_VERT_TLY'],$page['TL_VERT_BRX'],$page['TL_VERT_BRY'],$image,$page['VERT_WIDTH_BOX'],int_divide($page['VERT_WIDTH_BOX'],10),50,false);
  $d[] = horiliney($page['TL_HORI_TLX'],$page['TL_HORI_TLY'],$page['TL_HORI_BRX'],$page['TL_HORI_BRY'],$image,$page['HORI_WIDTH_BOX'],int_divide($page['HORI_WIDTH_BOX'],10),50,false);

  $d[] = vertlinex($page['TR_VERT_TLX'],$page['TR_VERT_TLY'],$page['TR_VERT_BRX'],$page['TR_VERT_BRY'],$image,$page['VERT_WIDTH_BOX'],int_divide($page['VERT_WIDTH_BOX'],10),50,false);
  $d[] = horiliney($page['TR_HORI_TLX'],$page['TR_HORI_TLY'],$page['TR_HORI_BRX'],$page['TR_HORI_BRY'],$image,$page['HORI_WIDTH_BOX'],int_divide($page['HORI_WIDTH_BOX'],10),50,false);

  $d[] = vertlinex($page['BL_VERT_TLX'],$page['BL_VERT_TLY'],$page['BL_VERT_BRX'],$page['BL_VERT_BRY'],$image,$page['VERT_WIDTH_BOX'],int_divide($page['VERT_WIDTH_BOX'],10),50,false);
  $d[] = horiliney($page['BL_HORI_TLX'],$page['BL_HORI_TLY'],$page['BL_HORI_BRX'],$page['BL_HORI_BRY'],$image,$page['HORI_WIDTH_BOX'],int_divide($page['HORI_WIDTH_BOX'],10),50,false);

  $d[] = vertlinex($page['BR_VERT_TLX'],$page['BR_VERT_TLY'],$page['BR_VERT_BRX'],$page['BR_VERT_BRY'],$image,$page['VERT_WIDTH_BOX'],int_divide($page['VERT_WIDTH_BOX'],10),50,false);
  $d[] = horiliney($page['BR_HORI_TLX'],$page['BR_HORI_TLY'],$page['BR_HORI_BRX'],$page['BR_HORI_BRY'],$image,$page['HORI_WIDTH_BOX'],int_divide($page['HORI_WIDTH_BOX'],10),50,false);


  //check to see how many are 0 - if none are - proceed, otherwise try for box 
  //edge detection
  //
  $boxerrors = 0;
  foreach($d as $bb)
    if ($bb == 0) $boxerrors++;

  if ($boxerrors > 0)
  {
    //try box edge detection
    $b[] = vertlinex($page['TL_VERT_TLX'],$page['TL_VERT_TLY'],$page['TL_VERT_BRX'],$page['TL_VERT_BRY'],$image,$page['VERT_WIDTH'],int_divide($page['VERT_WIDTH'],3));
    $b[] = horiliney($page['TL_HORI_TLX'],$page['TL_HORI_TLY'],$page['TL_HORI_BRX'],$page['TL_HORI_BRY'],$image,$page['HORI_WIDTH'],int_divide($page['HORI_WIDTH'],3));

    $b[] = vertlinex($page['TR_VERT_TLX'],$page['TR_VERT_TLY'],$page['TR_VERT_BRX'],$page['TR_VERT_BRY'],$image,$page['VERT_WIDTH'],int_divide($page['VERT_WIDTH'],3));
    $b[] = horiliney($page['TR_HORI_TLX'],$page['TR_HORI_TLY'],$page['TR_HORI_BRX'],$page['TR_HORI_BRY'],$image,$page['HORI_WIDTH'],int_divide($page['HORI_WIDTH'],3));

    $b[] = vertlinex($page['BL_VERT_TLX'],$page['BL_VERT_TLY'],$page['BL_VERT_BRX'],$page['BL_VERT_BRY'],$image,$page['VERT_WIDTH'],int_divide($page['VERT_WIDTH'],3));
    $b[] = horiliney($page['BL_HORI_TLX'],$page['BL_HORI_TLY'],$page['BL_HORI_BRX'],$page['BL_HORI_BRY'],$image,$page['HORI_WIDTH'],int_divide($page['HORI_WIDTH'],3));

    $b[] = vertlinex($page['BR_VERT_TLX'],$page['BR_VERT_TLY'],$page['BR_VERT_BRX'],$page['BR_VERT_BRY'],$image,$page['VERT_WIDTH'],int_divide($page['VERT_WIDTH'],3));
    $b[] = horiliney($page['BR_HORI_TLX'],$page['BR_HORI_TLY'],$page['BR_HORI_BRX'],$page['BR_HORI_BRY'],$image,$page['HORI_WIDTH'],int_divide($page['HORI_WIDTH'],3));

    $lineerrors = 0;
    foreach($b as $bb)
      if ($bb == 0) $lineerrors++;

    //check which one has the least number of errors

    if ($lineerrors < $boxerrors)
      $d = $b;
  }


	if ($compare == 0) return $d;

	$xa =0;
	$xb = 0;
	$xc = 0;
	$ya =0;
	$yb = 0;
	$yc = 0;

	if ($b[0] != 0){ $xa += $a['tlx']; $xb += $b[0]; $xc++; } else return false;
	if ($b[2] != 0){ $xa += $a['trx']; $xb += $b[2]; $xc++; } else return false;
	if ($b[4] != 0){ $xa += $a['blx']; $xb += $b[4]; $xc++; } else return false;
	if ($b[6] != 0){ $xa += $a['brx']; $xb += $b[6]; $xc++; } else return false;

	if ($b[1] != 0){ $ya += $a['tly']; $yb += $b[1]; $yc++; } else return false;
	if ($b[3] != 0){ $ya += $a['try']; $yb += $b[3]; $yc++; } else return false;
	if ($b[5] != 0){ $ya += $a['bly']; $yb += $b[5]; $yc++; } else return false;
	if ($b[7] != 0){ $ya += $a['bry']; $yb += $b[7]; $yc++; } else return false;

	$c[0] = round($xb / $xc) - round($xa / $xc);
	$c[1] = round($yb / $yc) - round($ya / $yc);

	return $c;
}

function offsetxy($a,$offset)
{
	$b = array();
	$b[0] = $a[0] + $offset[0];
	$b[1] = $a[1] + $offset[1];
	return $b;
}


function calcoffset($a,$ox=0,$oy=0)
{
	$b = array();

	$b['tlx'] = $a['tlx'] + $ox;
	$b['tly'] = $a['tly'] + $oy;
	$b['brx'] = $a['brx'] + $ox;
	$b['bry'] = $a['bry'] + $oy;

	return $b;
}


/**
 * Sanitize the page border variables based on width and height of image
 * 
 * @param mixed $page 
 * @param int $width The width of the current page
 * @param int $height The height of the current page
 * 
 * @return $page sanitized
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2011-08-26
 */
function sanitizepage($page,$width,$height)
{
	$tb = array('t','b');
	$lr = array('l','r');
	$vh = array('vert','hori');
	$ex = array('tlx','brx');
	$ey = array('tly','bry');
	foreach($tb as $a)
		foreach($lr as $b)
			foreach($vh as $c)					
			{
				$vname = "$a$b" . "_" . $c ."_";
			
				foreach($ex as $d)
				{
					$vn = strtoupper($vname . $d);
					if ($page[$vn] <= 0) $val = 1;
					if ($page[$vn] >= $width) $page[$vn] = $width - 1;
				}

				foreach($ey as $d)
				{
					$vn = strtoupper($vname . $d);
					if ($page[$vn] <= 0) $page[$vn] = 1;
					if ($page[$vn] >= $height) $page[$vn] = $height - 1;
				}

			}
	return $page;
}


/**
* Detect the rotation, scale and offset of the given image 
* Use the template page offsets for calculations of scale and offset
*
*/
function detecttransforms($image,$page)
{
	$width = imagesx($image);
	$height = imagesy($image);

	$page = sanitizepage($page,$width,$height);

  $offset = offset($image,false,0,$page);

  //correct offset if possible (multiple runs to correct for possible multiple errors)
  $offset = validate_offset($offset,$page);
  $offset = validate_offset($offset,$page);
  $offset = validate_offset($offset,$page);

	if (!in_array("",$offset)) //all edges detected
	{
		$centroid = calccentroid($offset);
		$rotate = calcrotate($offset);
		$rotate = $rotate - $page['rotation'];

		//rotate offset
		for ($i = 0; $i <= 6; $i += 2)	
			list($offset[$i],$offset[$i+1]) = rotate($rotate,array($offset[$i],$offset[$i+1]),$centroid);
	
		$scale = calcscale($page,$offset);
	
		//scale offset
		for ($i = 0; $i <= 6; $i += 2)	
			list($offset[$i],$offset[$i+1]) = scale($scale,array($offset[$i],$offset[$i+1]),$centroid);
		
		//calc offset
		$offsetxy = array();
		$offsetxy[0] = $page['tlx'] - $offset[0];
		$offsetxy[1] = $page['tly'] - $offset[1];
		
		//reverse all values
		$offsetxy[0] *= -1.0;
		$offsetxy[1] *= -1.0;
		$scale[0] = 1.0 / $scale[0];
		$scale[1] = 1.0 / $scale[1];
		$rotate *= -1.0;
		
		$transforms = array('offx' => $offsetxy[0], 'offy' => $offsetxy[1], 'scalex' => $scale[0], 'scaley' => $scale[1], 'centroidx' => $centroid[0], 'centroidy' => $centroid[1], 'costheta' => cos($rotate), 'sintheta' => sin($rotate), 'width' => $width, 'height' => $height);
	
		return $transforms;
	} 

	return array('offx' => 0, 'offy' => 0, 'scalex' => 1, 'scaley' =>1, 'centroidx' => 0, 'centroidy' => 0, 'costheta' => 1, 'sintheta' => 0, 'width' => $width, 'height' => $height); //return no transformation if all edges not detected
}


/**
* 
* @param array $a A box group to transform
* @param array $transforms the transforms from the database offx,offy,centroidx,centroidy,scalex,scaley,costheta,sintheta
*/
function applytransforms($a,$transforms)
{
	$b = array();
	$scale = array($transforms['scalex'],$transforms['scaley']);
	$offsetxy = array($transforms['offx'],$transforms['offy']);
	$centroid = array($transforms['centroidx'],$transforms['centroidy']);

	list($b['tlx'],$b['tly']) = validatepixel(rotate(false,scale($scale,offsetxy(array($a['tlx'],$a['tly']),$offsetxy),$centroid),$centroid,$transforms['costheta'],$transforms['sintheta']),$transforms['width'],$transforms['height']);
	list($b['brx'],$b['bry']) = validatepixel(rotate(false,scale($scale,offsetxy(array($a['brx'],$a['bry']),$offsetxy),$centroid),$centroid,$transforms['costheta'],$transforms['sintheta']),$transforms['width'],$transforms['height']);

	return $b;
}


/**
* Calculate the centroid of an image based on the corner lines
*
* @return array The x and y of the centroid
*/
function calccentroid($a)
{
	$b = array();

	$xb = 0;
	$yb = 0;
	$xc = 0;
	$yc = 0;

	if ($a[0] != 0){ $xb += $a[0]; $xc++; }
	if ($a[2] != 0){ $xb += $a[2]; $xc++; }
	if ($a[4] != 0){ $xb += $a[4]; $xc++; }
	if ($a[6] != 0){ $xb += $a[6]; $xc++; }

	if ($a[1] != 0){ $yb += $a[1]; $yc++; }
	if ($a[3] != 0){ $yb += $a[3]; $yc++; }
	if ($a[5] != 0){ $yb += $a[5]; $yc++; }
	if ($a[7] != 0){ $yb += $a[7]; $yc++; }

	$b[0] = round($xb / $xc);
	$b[1] = round($yb / $yc);

	return $b;
}

/**
 * Calculate the amount of rotation of an image based on the corner lines
 *
 * @param array $a The array of detected corner lines
 * @param bool $ret Whether to return the angles as detected (true) or the average (false)
 * @return array|float The array of angles or the average
 */
function calcrotate($a,$ret = false)
{
	//the angle at the top
	// remember: sohcahtoa
	$topangle = 0;
	$bottomangle = 0;
	$leftangle = 0;
	$rightangle = 0;
	$count = 0;

	if (($a[2] - $a[0]) != 0)
	{
		$topangle = atan(($a[1] - $a[3]) / ($a[2] - $a[0]));
		$count++;
	}

	if (($a[6] - $a[4]) != 0)
	{
		$bottomangle = atan(($a[5] - $a[7]) / ($a[6] - $a[4]));
		$count++;
	}

	if (($a[1] - $a[5]) != 0)
	{
		$leftangle = atan(($a[0] - $a[4]) / ($a[1] - $a[5]));
		$count++;
	}

	if (($a[3] - $a[7]) != 0)
	{
		$rightangle = atan(($a[2] - $a[6]) / ($a[3] - $a[7]));
		$count++;
	}

  if ($ret)
    return array($topangle,$bottomangle,$leftangle,$rightangle);

	if ($count == 0) 
		return 0;

	$count = (float)$count;
	//print "<p>ANGLES: $topangle $bottomangle $leftangle $rightangle</p>";
	//take the average
	return (($topangle + $bottomangle + $leftangle + $rightangle) / $count);
}

/**
* Calculate the new pixel location based on the rotation and centroid
*
*
*/
function rotate($angle=false,$point,$centroid,$costheta=false,$sintheta=false)
{
	if ($angle !== false)
	{
		$sintheta = sin($angle);
		$costheta = cos($angle);
	}
	
	$a = array();
	$a[0] = round((($costheta*($point[0]-$centroid[0])) - ($sintheta*($point[1]-$centroid[1]))) + $centroid[0]);
	$a[1] = round((($sintheta*($point[0]-$centroid[0])) + ($costheta*($point[1]-$centroid[1]))) + $centroid[1]);

	return $a;
}

/**
* Calculate the x and y scaling of the image based on the corner lines
*
* @param array $a An array containing the 4 corner coordinates of the existing image
* @param array $b An array containing the 4 corner coordinates of the new image
* @return array The scale factor on the x and y axis
*/
function calcscale($a,$b)
{
	//default scale is 1
	$c = array(1,1);
	
	//Top and bottom horizontal - x - average
	$xa = (($b[2] - $b[0]) + ($b[6] - $b[4]));
	if ($xa != 0) $c[0] = (((($a['trx'] - $a['tlx']) + ($a['brx'] - $a['blx'])) / 2.0) / ($xa / 2.0));
	//Left vertical and Right vertical - y - average
	$ya = (($b[5] - $b[1]) + ($b[7] - $b[3]));
	if ($ya != 0) $c[1] = (((($a['bly'] - $a['tly']) + ($a['bry'] - $a['try'])) / 2.0) / ($ya / 2.0));

	return $c;
}


/**
* Return a new pixel location based on the scale and centroid
*
*/
function scale($scale,$point,$centroid)
{
	//calculate distance from centroid, multiply by scale and add to centroid
//	$dx = ($point[0] - $centroid[0]);
//	$dy = ($point[1] - $centroid[1]);

	$c = array();

//	$c[0] = round(($dx*$scale[0]) + $centroid[0]);
//	$c[1] = round(($dy*$scale[1]) + $centroid[1]);

	$c[0] = round($point[0]*$scale[0]);
	$c[1] = round($point[1]*$scale[1]);

	return $c;
}


function crop($image,$a)
{
	$newwidth = $a['brx']-$a['tlx'];
	$newheight = $a['bry']-$a['tly'];
	$new = imagecreatetruecolor($newwidth, $newheight);
	imagepalettecopy($new,$image);
	imagecopyresized($new, $image, 0, 0, $a['tlx'], $a['tly'], $newwidth, $newheight, $newwidth, $newheight);
	//print "$tlx $tly $newwidth $newheight<br/>";
	return $new;
}

/*return the fill ratio of an area of an image
 * 1 indicates empty, 0 indicates black
 */
function fillratio($image,$a)
{
	$xdim = imagesx($image);
	$ydim = imagesy($image);
	$total = 0;
	$count = 0;
	for ($x = $a['tlx']; $x < $a['brx']; $x++) {
		for ($y = $a['tly']; $y < $a['bry']; $y++) {
      $rgb = imagecolorat($image, $x, $y);
      if ($rgb > 0) {
        $rgb = 1;
      }
			//$r = ($rgb >> 16) & 0xFF;
			//$g = ($rgb >> 8) & 0xFF;
			//$b = $rgb & 0xFF;
			$count++;	
			$total += $rgb;
			//print $rgb . "<br/>\n";
		}
	}
	if ($count == 0) return 0;
	return $total/$count;
}


/* Find a horizontal line and return it's position
 *
 */
function horiliney($tlx,$tly,$brx,$bry,$image,$approxw,$tolerance = 2,$attempts = 10,$searchlongest = true,$dgaps = 3)
{
	//0 is black, 1 is white
	$y = 0;
	//try $attempts times to find start of line
	$xadd = int_divide(($brx - $tlx), $attempts);
	$s = array();
	$count = 0;
	$avg = 0;
	for ($x = $tlx; $x < $brx; $x+=$xadd) {
		$col = imagecolorat($image, $x, $y);
		$width = 1;
    $start = $y;
    $dgapst = $dgaps;
		for ($y = $tly; $y < $bry; $y++) {
			$rgb = imagecolorat($image, $x, $y);
			if ($rgb != $col){
				if ($width >= $approxw - $tolerance && $width <= $approxw + $tolerance && $col == 0){
					//record middle of line
					$s[$start + int_divide($width, 2)] = $x;
					//$count++;
				  $width = 0;
          $start = $y;
 				//$avg += $start;
        }
        if ($dgapst <= 0)
        {
				  $width = 0;
          $start = $y;
          $dgapst = $dgaps + 1;
        }
				$col = $rgb;
			}
			$width++;
      //print $rgb;
      $dgapst--;
		}
		//print "<br/>\n";
	}
	//s is an array of with key being y val of middle of line, value being x val

  if (empty($s)) return 0;

	//print_r($s);

	//run a scanline through the key val to determine the longest/shortest line
  $line = 0;
  
  if ($searchlongest == false) //if searching for shortest
    $line = 100000;
;
	$longest = key($s);
	foreach($s as $y => $xval)
	{
		$col = imagecolorat($image, $tlx, $y);
		$width = 1;
		for($x = $tlx; $x < $brx; $x += 1)
		{
			$rgb = imagecolorat($image, $x, $y);
      if ($rgb != $col){
        if ($searchlongest)
        {
  				if ($width > $line && $col == 0)
  				{
  					$longest= $y;
  					$line = $width;
  				}
        }
        else
        {
      		if (abs($width - $approxw) < $line && $col == 0)
  				{
  					$longest= $y;
  					$line = abs($width - $approxw);
  				}
        }
				$width = 0;
				$col = $rgb;
			}
			$width++;
		}

	}

	return $longest;
}

/* Find a vertical line and return it's position
 *
 *
 *
 */
function vertlinex($tlx,$tly,$brx,$bry,$image,$approxw,$tolerance = 2,$attempts = 10,$searchlongest = true,$dgaps = 3)
{
	//0 is black, 1 is white
	$x = 0;
	//try $attempts times to find start of line
	$yadd = int_divide(($bry - $tly) ,$attempts);
	$s = array();
	$count = 0;
	$avg = 0;
	for ($y = $tly; $y < $bry; $y+=$yadd) {
		$col = imagecolorat($image, $x, $y);
		$width = 0;
    $start = $x;
    $dgapst = $dgaps; //allow for gaps due to dithering
		for ($x = $tlx; $x < $brx; $x++) {
			$rgb = imagecolorat($image, $x, $y);
			if ($rgb != $col){
				if ($width >= $approxw - $tolerance && $width <= $approxw + $tolerance && $col == 0){
					$s[$start + int_divide($width, 2)] = $y;
					$count++;
				  $width = 0;
          $start = $x;
      			$avg += $start;
        }
       
        if ($dgapst <= 0)
        {
				  $width = 0;
          $start = $x;
          $dgapst = $dgaps + 1;
        }
				$col = $rgb;
      }
      $dgapst--;
			$width++;
			//print $rgb;
		}
		//print "<br/>\n";
	}

  if (empty($s)) return 0;

	//add ability to search for the line closest to a certain length - not just the longest which
	//may be a page artifact. need to define CORNER_LINE_LENGTH in pixels and enablels


  $line = 0;
  if ($searchlongest == false)
    $line = 100000;

	$longest = key($s);
	foreach($s as $x => $yval)
	{
		$col = imagecolorat($image, $x, $tly);
		$width = 0;
		for($y = $tly; $y < $bry; $y += 1)
		{
			$rgb = imagecolorat($image, $x, $y);
			if ($rgb != $col){
        //print "X LINE: $x width: $width COL: $col<br/>";
        if ($searchlongest)
        {
   				if ($width > $line && $col == 0)
  				{
  					$longest= $x;
	  				$line = $width;
          }
        }
        else
        {
  				if (abs($width - $approxw) < $line && $col == 0)
  				{
  					$longest= $x;
	  				$line = abs($width - $approxw);
          }
        }
				$width = 0;
				$col = $rgb;
			}
			$width++;
		}

	}

	return $longest;

}


function overlay($image, $boxes)
{
	$sizex = imagesx($image);
	$sizey = imagesy($image);

	// Convert the Image to PNG-24 (for alpha blending)
	$im_tc = imagecreatetruecolor($sizex,$sizey);
	imagecopy($im_tc,$image,0,0,0,0,$sizex,$sizey);

	//orange overlay colour
	$bgc = imagecolorallocatealpha($im_tc, 255, 0, 0, 75);

	foreach($boxes as $box)
	{
		imagefilledrectangle($im_tc, $box['tlx'], $box['tly'], $box['brx'], $box['bry'], $bgc);
	}

	//imagepng($im_tc);
	return $im_tc;
}


function split_scanning($image)
{
	//check if we need to split
	if (SPLIT_SCANNING)
	{
		$width = imagesx($image);
		$height = imagesy($image);
		$swidth = $width / 2.0;
	
		if ((PAGE_WIDTH - SPLIT_SCANNING_THRESHOLD) < $swidth && $swidth < (PAGE_WIDTH + SPLIT_SCANNING_THRESHOLD))
		{
			//if image is side by side double the page size, it needs to be split
			
			$image1 = crop($image, array("tlx" => 0, "tly" => 0, "brx" => ($swidth), "bry" => $height));
			$image2 = crop($image, array("tlx" => ($swidth ), "tly" => 0, "brx" => $width, "bry" => $height));
			
			return array($image1,$image2);
		}	
	}
	
	return array($image); //just return the image if not splitting
}

?>
