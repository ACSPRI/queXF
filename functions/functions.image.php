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
function validatepixel($a)
{
	if ($a[0] < 0) $a[0] = 0;
	if ($a[1] < 0) $a[1] = 0;
	if ($a[0] > PAGE_WIDTH) $a[0] = PAGE_WIDTH;
	if ($a[1] > PAGE_HEIGHT) $a[1] = PAGE_HEIGHT;
	return $a;
}

/* Use the presence of corner lines to see if the page is blank or not
 *
 */
function is_blank_page($image)
{
	$b = array();

	$b[] = vertlinex(TL_VERT_TLX,TL_VERT_TLY,TL_VERT_BRX,TL_VERT_BRY,$image,VERT_WIDTH);
	$b[] = horiliney(TL_HORI_TLX,TL_HORI_TLY,TL_HORI_BRX,TL_HORI_BRY,$image,HORI_WIDTH);

	$b[] = vertlinex(TR_VERT_TLX,TR_VERT_TLY,TR_VERT_BRX,TR_VERT_BRY,$image,VERT_WIDTH);
	$b[] = horiliney(TR_HORI_TLX,TR_HORI_TLY,TR_HORI_BRX,TR_HORI_BRY,$image,HORI_WIDTH);

	$b[] = vertlinex(BL_VERT_TLX,BL_VERT_TLY,BL_VERT_BRX,BL_VERT_BRY,$image,VERT_WIDTH);
	$b[] = horiliney(BL_HORI_TLX,BL_HORI_TLY,BL_HORI_BRX,BL_HORI_BRY,$image,HORI_WIDTH);

	$b[] = vertlinex(BR_VERT_TLX,BR_VERT_TLY,BR_VERT_BRX,BR_VERT_BRY,$image,VERT_WIDTH);
	$b[] = horiliney(BR_HORI_TLX,BR_HORI_TLY,BR_HORI_BRX,BR_HORI_BRY,$image,HORI_WIDTH);

	$total = 0;
	foreach ($b as $key => $value)
		$total += $value;

	if ($total == 0) return true;
	return false;
}



//calculate the offset of an image given DCARF standard corner lines
//and original page id
//given an image and the tlx,tly,trx,try,blx,bly,brx,bry as an array
//
function offset($image,$a,$compare = 1)
{
	$b = array();
	$c = array();

	//temp only ?
	if (!isset($a['tlx']) && $compare == 1)
	{
		$c[0] = 0;
		$c[1] = 0;
		return $c;
	}

	$b[] = vertlinex(TL_VERT_TLX,TL_VERT_TLY,TL_VERT_BRX,TL_VERT_BRY,$image,VERT_WIDTH);
	$b[] = horiliney(TL_HORI_TLX,TL_HORI_TLY,TL_HORI_BRX,TL_HORI_BRY,$image,HORI_WIDTH);

	$b[] = vertlinex(TR_VERT_TLX,TR_VERT_TLY,TR_VERT_BRX,TR_VERT_BRY,$image,VERT_WIDTH);
	$b[] = horiliney(TR_HORI_TLX,TR_HORI_TLY,TR_HORI_BRX,TR_HORI_BRY,$image,HORI_WIDTH);

	$b[] = vertlinex(BL_VERT_TLX,BL_VERT_TLY,BL_VERT_BRX,BL_VERT_BRY,$image,VERT_WIDTH);
	$b[] = horiliney(BL_HORI_TLX,BL_HORI_TLY,BL_HORI_BRX,BL_HORI_BRY,$image,HORI_WIDTH);

	$b[] = vertlinex(BR_VERT_TLX,BR_VERT_TLY,BR_VERT_BRX,BR_VERT_BRY,$image,VERT_WIDTH);
	$b[] = horiliney(BR_HORI_TLX,BR_HORI_TLY,BR_HORI_BRX,BR_HORI_BRY,$image,HORI_WIDTH);


	if ($compare == 0) return $b;

	$xa =0;
	$xb = 0;
	$xc = 0;
	$ya =0;
	$yb = 0;
	$yc = 0;

	if ($b[0] != 0){ $xa += $a['tlx']; $xb += $b[0]; $xc++; }
	if ($b[2] != 0){ $xa += $a['trx']; $xb += $b[2]; $xc++; }
	if ($b[4] != 0){ $xa += $a['blx']; $xb += $b[4]; $xc++; }
	if ($b[6] != 0){ $xa += $a['brx']; $xb += $b[6]; $xc++; }

	if ($b[1] != 0){ $ya += $a['tly']; $yb += $b[1]; $yc++; }
	if ($b[3] != 0){ $ya += $a['try']; $yb += $b[3]; $yc++; }
	if ($b[5] != 0){ $ya += $a['bly']; $yb += $b[5]; $yc++; }
	if ($b[7] != 0){ $ya += $a['bry']; $yb += $b[7]; $yc++; }

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
* Detect the rotation, scale and offset of the given image 
* Use the template page offsets for calculations of scale and offset
*
*/
function detecttransforms($image,$page)
{
	$offset = offset($image,false,0);
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
	
	$transforms = array('offx' => $offsetxy[0], 'offy' => $offsetxy[1], 'scalex' => $scale[0], 'scaley' => $scale[1], 'centroidx' => $centroid[0], 'centroidy' => $centroid[1], 'costheta' => cos($rotate), 'sintheta' => sin($rotate));

	return $transforms;
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

	list($b['tlx'],$b['tly']) = validatepixel(rotate(false,scale($scale,offsetxy(array($a['tlx'],$a['tly']),$offsetxy),$centroid),$centroid,$transforms['costheta'],$transforms['sintheta']));
	list($b['brx'],$b['bry']) = validatepixel(rotate(false,scale($scale,offsetxy(array($a['brx'],$a['bry']),$offsetxy),$centroid),$centroid,$transforms['costheta'],$transforms['sintheta']));

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
*/
function calcrotate($a)
{
	//the angle at the top
	// remember: sohcahtoa

	$topangle = atan(($a[1] - $a[3]) / ($a[2] - $a[0]));
	$bottomangle = atan(($a[5] - $a[7]) / ($a[6] - $a[4]));

	$leftangle = atan(($a[0] - $a[4]) / ($a[1] - $a[5]));
	$rightangle = atan(($a[2] - $a[6]) / ($a[3] - $a[7]));

	//print "<p>ANGLES: $topangle $bottomangle $leftangle $rightangle</p>";
	//take the average
	return (($topangle + $bottomangle + $leftangle + $rightangle) / 4.0);
}

/**
* Calculate the new pixel location based on the rotation and centroid
*
*
*/
function rotate($angle=false,$point,$centroid,$costheta=false,$sintheta=false)
{
	if ($angle != false)
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
	$c = array();
	
	//Top and bottom horizontal - x - average
	$c[0] = (((($a['trx'] - $a['tlx']) + ($a['brx'] - $a['blx'])) / 2.0) / ((($b[2] - $b[0]) + ($b[6] - $b[4])) / 2.0));
	//Left vertical and Right vertical - y - average
	$c[1] = (((($a['bly'] - $a['tly']) + ($a['bry'] - $a['try'])) / 2.0) / ((($b[5] - $b[1]) + ($b[7] - $b[3])) / 2.0));

	return $c;
}


/**
* Return a new pixel location based on the scale and centroid
*
*/
function scale($scale,$point,$centroid)
{
	//calculate distance from centroid, multiply by scale and add to centroid
	$dx = ($point[0] - $centroid[0]);
	$dy = ($point[1] - $centroid[1]);

	$c = array();

	$c[0] = round(($dx*$scale[0]) + $centroid[0]);
	$c[1] = round(($dy*$scale[1]) + $centroid[1]);

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
function horiliney($tlx,$tly,$brx,$bry,$image,$approxw)
{
	//0 is black, 1 is white
	$y = 0;
	//try 10 times to find start of line
	$xadd = int_divide(($brx - $tlx), 10);
	$s = array();
	$count = 0;
	$avg = 0;
	$tolerance = int_divide($approxw, 3);
	for ($x = $tlx; $x < $brx; $x+=$xadd) {
		$col = imagecolorat($image, $x, $y);
		$width = 1;
		$start = $y;
		for ($y = $tly; $y < $bry; $y++) {
			$rgb = imagecolorat($image, $x, $y);
			if ($rgb != $col){
				if ($width >= $approxw - $tolerance && $width <= $approxw + $tolerance && $col == 0){
					//record middle of line
					$s[$start + int_divide($width, 2)] = $x;
					//$count++;
					//$avg += $start;
				}
				$width = 0;
				$col = $rgb;
				$start = $y;
			}
			$width++;
			//print $rgb;
		}
		//print "<br/>\n";
	}
	//s is an array of with key being y val of middle of line, value being x val

	//print_r($s);

	//run a scanline through the key val to determine the longest line
	$line = 0;
	$longest = key($s);
	foreach($s as $y => $xval)
	{
		$col = imagecolorat($image, $tlx, $y);
		$width = 1;
		for($x = $tlx; $x < $brx; $x += 1)
		{
			$rgb = imagecolorat($image, $x, $y);
			if ($rgb != $col){
				if ($width > $line && $col == 0)
				{
					$longest= $y;
					$line = $width;
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
function vertlinex($tlx,$tly,$brx,$bry,$image,$approxw)
{
	//0 is black, 1 is white
	$x = 0;
	//try 10 times to find start of line
	$yadd = int_divide(($bry - $tly) ,10);
	$s = array();
	$count = 0;
	$avg = 0;
	$tolerance = int_divide($approxw, 3);
	for ($y = $tly; $y < $bry; $y+=$yadd) {
		$col = imagecolorat($image, $x, $y);
		$width = 0;
		$start = $x;
		for ($x = $tlx; $x < $brx; $x++) {
			$rgb = imagecolorat($image, $x, $y);
			if ($rgb != $col){
				if ($width >= $approxw - $tolerance && $width <= $approxw + $tolerance && $col == 0){
					$s[$start + int_divide($width, 2)] = $y;
					$count++;
					$avg += $start;
				}
				$width = 0;
				$col = $rgb;
				$start = $x;
			}
			$width++;
			//print $rgb;
		}
		//print "<br/>\n";
	}

	$line = 0;
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
				if ($width > $line && $col == 0)
				{
					$longest= $x;
					$line = $width;
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


?>
