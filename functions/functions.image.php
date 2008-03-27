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


/* Use the presence of corner lines to see if the page is blank or not
 *
 */
function is_blank_page($image)
{
	$b = array();

	$b[] = vertlinex(140,140,267,500,$image,8);
	$b[] = horiliney(140,140,570,300,$image,8);

	$b[] = vertlinex(2195,194,2400,560,$image,8);
	$b[] = horiliney(1950,140,2400,325,$image,8);

	$b[] = vertlinex(140,2977,300,3400,$image,8);
	$b[] = horiliney(140,3150,600,3400,$image,8);

	$b[] = vertlinex(2195,2977,2400,3400,$image,8);
	$b[] = horiliney(1950,3192,2400,3400,$image,8);

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

	$b[] = vertlinex(140,140,267,500,$image,8);
	$b[] = horiliney(140,140,570,300,$image,8);

	$b[] = vertlinex(2195,194,2400,560,$image,8);
	$b[] = horiliney(1950,140,2400,325,$image,8);

	$b[] = vertlinex(140,2977,300,3400,$image,8);
	$b[] = horiliney(140,3150,600,3400,$image,8);

	$b[] = vertlinex(2195,2977,2400,3400,$image,8);
	$b[] = horiliney(1950,3192,2400,3400,$image,8);


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


function calcoffset($a,$ox=0,$oy=0)
{
	$b = array();

	$b['tlx'] = $a['tlx'] + $ox;
	$b['tly'] = $a['tly'] + $oy;
	$b['brx'] = $a['brx'] + $ox;
	$b['bry'] = $a['bry'] + $oy;

	return $b;
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
	$xadd = (($brx - $tlx) / 10);
	$s = array();
	$count = 0;
	$avg = 0;
	$tolerance = $approxw / 3;
	for ($x = $tlx; $x < $brx; $x+=$xadd) {
		$col = imagecolorat($image, $x, $y);
		$width = 1;
		$start = $y;
		for ($y = $tly; $y < $bry; $y++) {
			$rgb = imagecolorat($image, $x, $y);
			if ($rgb != $col){
				if ($width >= $approxw - $tolerance && $width <= $approxw + $tolerance && $col == 0){
					//record middle of line
					$s[$start + ($width / 2)] = $x;
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

	//run a scanline through the key val to determine the longest line
	$line = 0;
	$longest = 0;
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
	$yadd = (($bry - $tly) / 10);
	$s = array();
	$count = 0;
	$avg = 0;
	$tolerance = $approxw / 3;
	for ($y = $tly; $y < $bry; $y+=$yadd) {
		$col = imagecolorat($image, $x, $y);
		$width = 0;
		$start = $x;
		for ($x = $tlx; $x < $brx; $x++) {
			$rgb = imagecolorat($image, $x, $y);
			if ($rgb != $col){
				if ($width >= $approxw - $tolerance && $width <= $approxw + $tolerance && $col == 0){
					$s[$start + ($width / 2)] = $y;
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
	$longest = 0;
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
