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

/**
 * Set an image pixel color specifically to either black or white
 * 
 * @param image $image A reference to the image
 * @param int $x X pixel location
 * @param int $y Y pixel location
 * @param int $col The colour value. Anything that is >=1 is set to black
 * @param reference $white The white colour
 * @param reference $black The black colour
 * 
 * @return Image passed by reference is altered
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-13
 */
function imagecolorsettrue(&$image,$x,$y,$col,&$white,&$black)
{
	if ($col >= 1)
	{
		imagesetpixel($image,$x,$y,$black);
	}
	else
	{
		imagesetpixel($image,$x,$y,$white);
	}
}	

/**
 * Part of the Zhang-Suen thinning algorithm
 *
 *  This function tells the number N of regions that would exist if the
 *  pixel P(r,c) where changed from FG to BG. It returns 2*N, that is,
 *  the number of pixel transitions in the neighbourhood.
 *
 * @param image $im Reference to the image
 * @param int $r X value
 * @param int $c Y value
 *
 * @return int Number of N regions
 */
function crossing_index_np(&$im, $r, $c)
{
   $n=0;
   $next8 = array(1,2,3,4,5,6,7,0);
   $n8 = array(array(0,1),
	 array(-1,1),
	 array(-1,0),
	 array(-1,-1),
	 array(0,-1),
	 array(1,-1),
	 array(1,0),
	 array(1,1));



   $curr=imagecolorat($im, $r+$n8[0][1], $c+$n8[0][0]);
   for ($i=0,$idx=0;  $i<=8;  $i++,$idx=$next8[$idx]) {
      if (imagecolorat($im, $r + $n8[$idx][1], $c + $n8[$idx][0]) != $curr ) {
         $n++;
         $curr = imagecolorat($im, $r + $n8[$idx][1],$c + $n8[$idx][0]);
      }
   }

   return $n;
}


/**
 * Part of the Zhang-Suen thinning algorithm
 * 
 * Counts the number of neighbors of a pixel (r,c) in the image with value val.
 *
 * @param image $im Reference to the image
 * @param int $r X pixel location
 * @param int $c Y pixel location
 * @param int $val the colour value to look for
 *
 * @return int The number of neighbours in the image with value val
 */
function nh8count_np(&$im, $r, $c, $val) 
{
   $n=0;

   $n8 = array(array(0,1),
	 array(-1,1),
	 array(-1,0),
	 array(-1,-1),
	 array(0,-1),
	 array(1,-1),
	 array(1,0),
	 array(1,1));

   for ($i=0; $i < 8; $i++){

      if (imagecolorat($im, $r + $n8[$i][1], $c + $n8[$i][0]) == $val)
         $n++;
	}

   return $n;
}


/**
 * Zhang-Suen thinning
 * ported to PHP by Adam Zammit from analysis.c in http://sourceforge.net/projects/animal/
 *
 * Produce a "skeleton" of the 1 bit image using the Zhang-Suen method.
 *
 * @param image $image The image of the character
 * 
 * @return image The image thinned using Zhang-Suen thinning
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-18
 */
function thinzs_np($image)
{
	$xdim = imagesx($image);
	$ydim = imagesy($image);
	
	$im2 = imagecreate($xdim,$ydim);
	$border_color = imagecolorallocate($im2, 255, 255, 255);
	imagefilledrectangle($im2,0,0,$xdim, $ydim, $border_color);
	imagecopy($im2,$image,1,1,0,0,$xdim - 2,$ydim - 2);
	$image = $im2;

  	$B = imagecreate($xdim,$ydim);

	$white = imagecolorallocate($B, 255, 255, 255);
	$black = imagecolorallocate($B, 0, 0, 0);



	do {
		$repeat=false;
		for ($i=1; $i < $xdim-1; $i++)  for ($j=1; $j < $ydim-1; $j++) {
			$col = imagecolorat($image,$i,$j);

			imagecolorsettrue($B,$i,$j,$col,$white,$black);
			if ($col != 0) {
				$n = nh8count_np($image,$i,$j,1);
				if ( $n>=2 && $n<=6 && crossing_index_np($image,$i,$j) == 2 )
					if((imagecolorat($image,$i-1,$j)==0 || imagecolorat($image,$i,$j+1)==0 || imagecolorat($image,$i+1,$j)==0)
							&& (imagecolorat($image,$i-1,$j)==0 || imagecolorat($image,$i+1,$j)==0 || imagecolorat($image,$i,$j-1)==0)){
						imagecolorsettrue($B,$i,$j,0,$white,$black);
						$repeat=true;
					}
			}
		}


		for ($i=1; $i < $xdim-1; $i++)  for ($j=1; $j < $ydim-1; $j++) {
			$col = imagecolorat($B,$i,$j);
			imagecolorsettrue($image,$i,$j,$col,$white,$black);
			if ($col != 0) {
				$n = nh8count_np($B,$i,$j,1);
				if ( $n>=2 && $n<=6 && crossing_index_np($B,$i,$j) == 2 )
					if((imagecolorat($B,$i,$j+1)==0 || imagecolorat($B,$i,$j-1)==0 || imagecolorat($B,$i-1,$j)==0)
							&& (imagecolorat($B,$i-1,$j)==0 || imagecolorat($B,$i,$j-1)==0 || imagecolorat($B,$i+1,$j)==0)){
						imagecolorsettrue($image,$i,$j,0,$white,$black);
						$repeat=true;
					}
			}
		}

	} while ($repeat);

	return $image;
}




/**
 * Return what character based on the guess and the given kb
 * 
 * @param image $image The image of the character
 * @param int $btid The box type this came from
 * @param int $qid The questionniare this box came from
 * 
 * @return char The character detected
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-18
 */
function ocr_guess($image,$btid,$qid)
{
 	include_once(dirname(__FILE__).'/../config.inc.php');
	include_once(dirname(__FILE__).'/../db.inc.php');
	global $db;

	//remove speckles
	$a1 = kfill_modified($image,5);
	
	//remove boundary lines
	$a2 = remove_boundary_noise($a1,2);

	//resize the image to suit the OCR functions
	$timage = resize_bounding($a2);

	//thin the image to a skeleton
	$bimage = thinzs_np($timage);

	//extract the 16 features from the image
	$f = sector_distance($bimage);

	$sql = "SELECT val,
			(
			(pow(exp( - IFNULL((pow((m1-'{$f[0][1]}'),2)/(2*v1)),0)),2)) +
			(pow(exp( - IFNULL((pow((m2-'{$f[0][2]}'),2)/(2*v2)),0)),2)) +
			(pow(exp( - IFNULL((pow((m3-'{$f[0][3]}'),2)/(2*v3)),0)),2)) +
			(pow(exp( - IFNULL((pow((m4-'{$f[0][4]}'),2)/(2*v4)),0)),2)) +
			(pow(exp( - IFNULL((pow((m5-'{$f[0][5]}'),2)/(2*v5)),0)),2)) +
			(pow(exp( - IFNULL((pow((m6-'{$f[0][6]}'),2)/(2*v6)),0)),2)) +
			(pow(exp( - IFNULL((pow((m7-'{$f[0][7]}'),2)/(2*v7)),0)),2)) +
			(pow(exp( - IFNULL((pow((m8-'{$f[0][8]}'),2)/(2*v8)),0)),2)) +
			(pow(exp( - IFNULL((pow((m9-'{$f[0][9]}'),2)/(2*v9)),0)),2)) +
			(pow(exp( - IFNULL((pow((m10-'{$f[0][10]}'),2)/(2*v10)),0)),2)) +
			(pow(exp( - IFNULL((pow((m11-'{$f[0][11]}'),2)/(2*v11)),0)),2)) +
			(pow(exp( - IFNULL((pow((m12-'{$f[0][12]}'),2)/(2*v12)),0)),2)) +
			(pow(exp( - IFNULL((pow((m13-'{$f[1][1]}'),2)/(2*v13)),0)),2)) +
			(pow(exp( - IFNULL((pow((m14-'{$f[1][2]}'),2)/(2*v14)),0)),2)) +
			(pow(exp( - IFNULL((pow((m15-'{$f[1][3]}'),2)/(2*v15)),0)),2)) +
			(pow(exp( - IFNULL((pow((m16-'{$f[1][4]}'),2)/(2*v16)),0)),2))
			) as calc
		FROM ocrkbdata
		JOIN ocrkbboxgroup ON (ocrkbdata.kb = ocrkbboxgroup.kb AND ocrkbboxgroup.btid = '$btid' AND ocrkbboxgroup.qid = '$qid')
		ORDER BY calc DESC";

	$guess = $db->GetRow($sql);

	//DEBUG
	//print $sql . "<br/>";

	return $guess['val'];
}


/**
 * Generate a knowledge base based on the training data
 * Use the "Fuzzy logic" method outlined in "Hand printed Character Recognition using Neural Networks" Vamsi K. Madasu, Brian c. Lovell, M. Hanmandlu
 * 
 * @param mixed $kb 
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-18
 */
function generate_kb($kb)
{
	include_once(dirname(__FILE__).'/../config.inc.php');
	include_once(dirname(__FILE__).'/../db.inc.php');
	global $db;

	$db->StartTrans();

	$sql = "SELECT val
		FROM ocrtrain
		WHERE kb = '$kb'
		GROUP BY val";	

	$chars = $db->GetAll($sql);

	foreach($chars as $c)
	{
		$ch = $c['val'];
		$sql = "INSERT INTO ocrkbdata(kb,`val`,m1,v1,m2,v2,m3,v3,m4,v4,m5,v5,m6,v6,m7,v7,m8,v8,m9,v9,m10,v10,m11,v11,m12,v12,m13,v13,m14,v14,m15,v15,m16,v16)
			SELECT '$kb','$ch',avg(f1),var_pop(f1),avg(f2),var_pop(f2),avg(f3),var_pop(f3),avg(f4),var_pop(f4),avg(f5),var_pop(f5),avg(f6),var_pop(f6),avg(f7),var_pop(f7),avg(f8),var_pop(f8),avg(f9),var_pop(f9),avg(f10),var_pop(f10),avg(f11),var_pop(f11),avg(f12),var_pop(f12),avg(f13),var_pop(f13),avg(f14),var_pop(f14),avg(f15),var_pop(f15),avg(f16),var_pop(f16)
			FROM ocrtrain
			WHERE val = '$ch' AND kb = '$kb'";

		$db->Execute($sql);
	}

	$db->CompleteTrans();
}


/**
 * Given an image that has been thinned to a skeleton
 * Return the 12 vector distances and 4 occupancies
 * Based on section 2 of the article: "Hand printed Character Recognition using Neural Networks" by
 * Vamsi K. Madasu, Brian C. Lovell and M. Hanmandlu
 *
 * @link http://www.nicta.com.au/__data/assets/pdf_file/0006/14928/Hand_Printed_Character_Recognition_Using_Neural_Networks.pdf
 * @link http://espace.library.uq.edu.au/view/UQ:8573
 * 
 * @param image $image A reference to the thinned image
 * 
 * @return array An array with the 12 vector distances and 4 occupancies
 *
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-14
 */
function sector_distance(&$image)
{
	$s = array();
	$d = array();

	$xdim = imagesx($image);
	$ydim = imagesy($image);
	
	$xm = $xdim / 2;
	$yn = $ydim / 2;

	$t1pixels = 0;

	for ($y =0; $y < $ydim; $y++)
	{
		for ($x = 0; $x < $xdim; $x++)
		{
			if (imagecolorattrue($image,$x,$y))
			{
				//Assumes origin is at 0,0 - so translate
				$deg = rad2deg(atan2(($x - $xm),($y - $yn)));

//				print str_pad(round($deg,0),4," ") . " ";

				//Assign point to correct sector
				if ($deg > 0 && $deg <= 30) $s[1][] = array($x,$y);
				else if ($deg > 30 && $deg <= 60) $s[2][] = array($x,$y);
				else if ($deg > 60 && $deg <= 90) $s[3][] = array($x,$y);
				else if ($deg > 90 && $deg <= 120) $s[4][] = array($x,$y);
				else if ($deg > 120 && $deg <= 150) $s[5][] = array($x,$y);
				else if ($deg > 150 && $deg <= 180) $s[6][] = array($x,$y);
				else if ($deg > -30 && $deg <= 0) $s[12][] = array($x,$y);
				else if ($deg > -60 && $deg <= -30) $s[11][] = array($x,$y);
				else if ($deg > -90 && $deg <= -60) $s[10][] = array($x,$y);
				else if ($deg > -120 && $deg <= -90) $s[9][] = array($x,$y);
				else if ($deg > -150 && $deg <= -120) $s[8][] = array($x,$y);
				else if ($deg > -180 && $deg <= -150) $s[7][] = array($x,$y);

				$t1pixels++;
			}
//			else print "0000 ";
		}
//		print "<br/>";
	}



	$o = array(); //occupancy
	$o[1] = 0;
	$o[2] = 0;
	$o[3] = 0;
	$o[4] = 0;

	for($i = 1; $i <= 12; $i++)
	{
		$scount = 0;
		if(isset($s[$i])) {
			$scount = count($s[$i]);
		}

		//calc occupancy
		if ($i <= 3) $o[1] += $scount;
		else if ($i > 3 && $i <= 6) $o[2] += $scount;
		else if ($i > 6 && $i <= 9) $o[3] += $scount;
		else if ($i > 9) $o[4] += $scount;

		if ($scount == 0)
		{
			$d[$i] = 0;
			continue;
		}
		$sdistance = 0;
		foreach($s[$i] as $point)
		{
			$sdistance += sqrt(pow(($xm - $point[0]),2) + pow(($yn - $point[1]),2));
		//	print "sector: $i x: $xm {$point[0]} - y: $yn {$point[1]} distance: " . sqrt(pow(($xm - $point[0]),2) + pow(($yn - $point[1]),2)) . "<br/>";
		}
		$d[$i] = ($sdistance / $scount);
	}

//	print_r($o);

	if ($t1pixels == 0 )
	{
		//should we return something else here as the image is blank?
		 $o[1] = 0;
		 $o[2] = 0;
		 $o[3] = 0;
		 $o[4] = 0;
	}
	else
	{ 
		$o[1] /= $t1pixels;
		$o[2] /= $t1pixels;
		$o[3] /= $t1pixels;
		$o[4] /= $t1pixels;
	}

//	print_r($d);
//	print_r($o);
//	die();
	return array($d,$o);
}



/**
 * Remove blank columns and blank rows from the image edge
 * Then resize the image based on this area to the given size
 * 
 * @param image $image 
 * @param int $x The width
 * @param int $y The height
 * 
 * @return image a copy of the image of size x,y only containing the bounding box
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-14
 */
function resize_bounding(&$image, $x = 44, $y = 34)
{
	$new = imagecreate($x,$y);

	list($tlx,$tly,$brx,$bry) = bounding_box($image);

	$width = $brx - $tlx;
	$height = $bry - $tly;

	if ($width < 1) $width = 1;
	if ($height < 1) $height = 1;

	// Find the centre
	$xc = $width/2 + $tlx;
	$yc = $height/2 + $tly;

	// Preserve aspect ratio to an extent
	if ($width < $height) {
		$aspect = $width / $height;
		$aspect2 = pow($aspect, 1/3);
		$height2 = $height;
		$width2 = $height * $aspect2;
		$dx = ($width2 - $width) / 2;

		// Resize
		$blank = imagecreate($width2, $height2);
		imagecopy($blank, $image, $dx, 0, $tlx, $tly, $width, $height);
		imagecopyresized($new, $blank, 0, 0, 0, 0, $x, $y, $width2, $height2);
	} else {
		$aspect = $height / $width;
		$aspect2 = pow($aspect, 1/3);
		$width2 = $width;
		$height2 = $width * $aspect2;
		$dy = ($height2 - $height) / 2;

		// Resize
		$blank = imagecreate($width2, $height2);
		imagecopy($blank, $image, 0, $dy, $tlx, $tly, $width, $height);
		imagecopyresized($new, $blank, 0, 0, 0, 0, $x, $y, $width2, $height2);
	}
	return $new;
}


/**
 * Given an image of a character, remove boundary noise based on the algorithm given in:
 *
 * Preprocessing and Image Enhancement Algorithms for a Form-based Intelligent Character Recognition System, Dipti Deodhare, NNR Ranga Suri and R Amit. International Journal of Computer Science & Appliacations Vol. II, No. II pp. 131-144
 * 
 * Algorithm from page 138.
 *
 * Basically looks at each boundary and works out what is noise by looking at 1/8th of the image width
 * and finding filled columns and empty columns. If there is a filled column followed by an "empty" columns (defined as one
 * with 3 or less pixels in it) then destroy it, otherwise leave it. 
 *
 *
 * @param image $image The image to have the boundary noise removed
 * @param int $threshold The number of pixels in a column or row to define as blank
 * @return image The image with the boundary noise removed
 *
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2011-01-14
 */
function remove_boundary_noise($image, $threshold = 3)
{
	$w = imagesx($image);
	$h = imagesy($image);

	$black = imagecolorallocate($image, 0, 0, 0);
	$white = imagecolorallocate($image, 255, 255, 255);

	$w8 = $w / 6;
	$h8 = $h / 6;

	//left vertical
	$firstcol = -1;
	$pixels = array();
	for ($x = 0; $x <= $w8; $x++)
	{
		$pval = 0;
		for ($y = 0; $y < $h; $y++)
		{
			if (!imagecolorattrue($image,$x,$y))
			{
				$pval++;			
				$pixels[] = array($x,$y);
			}
		}

		if ($firstcol != -1)
		{
			//we have a first column, look for a second column with a pval <= threshold
			if ($pval <= $threshold)
			{
				//found secondcol
				//blank everything from firstcol to secondcol and break loop
				foreach($pixels as $p)
					imagecolorsettrue($image,$p[0],$p[1],0,$white,$black);
				break;
			}
		}
		else
		{
			if ($pval > 0)
				$firstcol = $x;
		}
	}

	//right vertical
	$firstcol = -1;
	$pixels = array();
	for ($x = ($w -1); $x >= ($w - 1 - $w8); $x--)
	{
		$pval = 0;
		for ($y = 0; $y < $h; $y++)
		{
			if (!imagecolorattrue($image,$x,$y))
			{
				$pval++;			
				$pixels[] = array($x,$y);
			}
		}

		if ($firstcol != -1)
		{
			//we have a first column, look for a second column with a pval <= threshold
			if ($pval <= $threshold)
			{
				//found secondcol
				//blank everything from firstcol to secondcol and break loop
				foreach($pixels as $p)
					imagecolorsettrue($image,$p[0],$p[1],0,$white,$black);
				break;
			}
		}
		else
		{
			if ($pval > 0)
				$firstcol = $x;
		}
	}

	//top horizontal
	$firstrow = -1;
	$pixels = array();
	for ($y = 0; $y <= $h8; $y++)
	{
		$pval = 0;
		for ($x = 0; $x < $w; $x++)
		{
			if (!imagecolorattrue($image,$x,$y))
			{
				$pval++;			
				$pixels[] = array($x,$y);
			}
		}

		if ($firstrow != -1)
		{
			//we have a first column, look for a second column with a pval <= threshold
			if ($pval <= $threshold)
			{
				//found secondcol
				//blank everything from firstcol to secondcol and break loop
				foreach($pixels as $p)
					imagecolorsettrue($image,$p[0],$p[1],0,$white,$black);
				break;
			}
		}
		else
		{
			if ($pval > 0)
				$firstrow = $y;
		}
	}


	//bottom horizontal
	$firstrow = -1;
	$pixels = array();
	for ($y = ($h -1); $y >= ($h -1 - $h8); $y--)
	{
		$pval = 0;
		for ($x = 0; $x < $w; $x++)
		{
			if (!imagecolorattrue($image,$x,$y))
			{
				$pval++;			
				$pixels[] = array($x,$y);
			}
		}

		if ($firstrow != -1)
		{
			//we have a first column, look for a second column with a pval <= threshold
			if ($pval <= $threshold)
			{
				//found secondcol
				//blank everything from firstcol to secondcol and break loop
				foreach($pixels as $p)
					imagecolorsettrue($image,$p[0],$p[1],0,$white,$black);
				break;
			}
		}
		else
		{
			if ($pval > 0)
				$firstrow = $y;
		}
	}

	return $image;
}



/**
 * Count the number of core ON pixels
 * 
 * @param int   $img  The image
 * @param mixed $x    
 * @param mixed $y    
 * @param mixed $c_lr 
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2011-01-05
 */
function kfill_count_core_pixel(&$img,$x,$y,$c_lr)
{
    $core_pixel = 0;
    for( $cy = $y ; $cy <= $c_lr['y'] ; $cy++ ) {
      for( $cx = $x ; $cx <= $c_lr['x'] ; $cx++ ) {
        if( imagecolorattrue($img,$cx,$cy)) {
          $core_pixel++;
        }
      }
    }
    return $core_pixel;
}

/**
 * TODO: short description.
 * 
 * @param int   $img  
 * @param mixed $x    
 * @param mixed $y    
 * @param mixed $c_lr 
 * @param mixed $v    
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2011-01-05
 */
function kfill_set_core_pixel(&$img,$x,$y,$c_lr,$v,&$white,&$black)
{
  // set all core pixel to given value
    for( $cy = $y ; $cy <= $c_lr['y'] ; $cy++ ) {
      for( $cx = $x ; $cx <= $c_lr['x']; $cx++ ) {
	imagecolorsettrue($img,$cx,$cy,$v,$white,$black);
      }
    }
}

/**
 * TODO: short description.
 * 
 * @param int      $img    
 * @param mixed    $k      
 * @param mixed    $x      
 * @param mixed    $y      
 * @param string   $size_x 
 * @param string   $size_y 
 * @param mixed    $n      
 * @param resource $r      
 * @param mixed    $c      
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2011-01-05
 */
function kfill_get_condition_variables(&$tmp,$k,$x,$y,$size_x,$size_y,&$n,&$r,&$c,&$white,&$black)
{
    $nnp = 4*($k-1); // total number of neighborhood pixels
    $nh_pixel = array(); // array for neighborhood pixel
    $nh_pixel_count = 0;
		
    // calculate window borders
    $ul_x = ( $x - 1 );
    $ul_y = ( $y - 1 );
    $ur_x = ( $x + $k - 2 );
    $ur_y = ( $y - 1 );
    $ll_x = ( $x - 1 );
    $ll_y = ( $y + $k - 2 );
    $lr_x = ( $x + $k - 2 );
    $lr_y = ( $y + $k - 2 );
    
					
    // fill array with neighborhood and count neighborhood ON pixel
    $i = 0;
    for( $ul_to_ur_np = $ul_x ; $ul_to_ur_np < $ur_x ; $ul_to_ur_np++ ) {
		
      if($ul_to_ur_np < 0 || ($y-1) < 0 ) {
        $pixelvalue = 0;
      } else {
        //pixelvalue = (*tmp).get( Point(ul_to_ur_np, y - 1) );
	$pixelvalue = imagecolorattrue($tmp,$ul_to_ur_np,($y-1));
      }
			
      $nh_pixel[$i++] = $pixelvalue;
      if ($pixelvalue) { $nh_pixel_count++; }
    }

    for( $ur_to_lr_np = $ur_y ; $ur_to_lr_np < $lr_y ; $ur_to_lr_np++ ) {
		
      if($ur_to_lr_np < 0 || ($x + $k - 2) > ($size_x - 1) ) {
        $pixelvalue = 0;
      } else {
        //pixelvalue = (*tmp).get( Point(x + k - 2, ur_to_lr_np) );
	$pixelvalue = imagecolorattrue($tmp,($x +$k - 2), $ur_to_lr_np);
      }
			
      $nh_pixel[$i++] = $pixelvalue; //0 should be white, else should be black
      if ($pixelvalue) { $nh_pixel_count++; }
    }
		
    for( $lr_to_ll_np = $lr_x ; $lr_to_ll_np > $ll_x ; $lr_to_ll_np-- ) {
		
      if( $lr_to_ll_np > ($size_x - 1) || ($y + $k - 2) > ($size_y - 1) ) {
        $pixelvalue = 0;
      } else {
        //pixelvalue = (*tmp).get( Point(lr_to_ll_np, y + k - 2) );
	$pixelvalue = imagecolorattrue($tmp,$lr_to_ll_np, ($y +$k - 2));
      }
			
      $nh_pixel[$i++] = $pixelvalue;
      if ($pixelvalue) { $nh_pixel_count++; }
    }
			
    for( $ll_to_ul_np = $ll_y ; $ll_to_ul_np > $ul_y ; $ll_to_ul_np-- ) {
		
      if((($x - 1) < 0) || ($ll_to_ul_np > ($size_y - 1)) ) {
        $pixelvalue = 0;
      } else {
        //pixelvalue = (*tmp).get( Point(x - 1, ll_to_ul_np) );
	$pixelvalue = imagecolorattrue($tmp,($x - 1), $ll_to_ul_np);
      }
			
      $nh_pixel[$i++] = $pixelvalue;
      if ($pixelvalue) { $nh_pixel_count++; }
    }
						
    // count corner ON pixel
    $corner_pixel_count = $nh_pixel[($k-1)*0] + $nh_pixel[($k-1)*1] + $nh_pixel[($k-1)*2] + $nh_pixel[($k-1)*3];


    // get ccs in neighborhood
    $nh_ccs = 0;
    for($nhpixel = 0 ; $nhpixel < $i ; $nhpixel++) {
      $nh_ccs += abs( $nh_pixel[($nhpixel+1)%$nnp] - $nh_pixel[$nhpixel] );
    }
    $nh_ccs /= 2;

    $n = $nh_pixel_count;
    $r = $corner_pixel_count;
    $c = $nh_ccs;

    //delete[] nh_pixel;
}

/**
 * Remove "salt and pepper" noise from an image
 * kfill_modified ported from Gamera
 * @link http://gamera.svn.sourceforge.net/viewvc/gamera/trunk/gamera/include/plugins/misc_filters.hpp
 * In turn algorithm from:
 * K.Chinnasarn, Y.Rangsanseri, P.Thitimajshima: Removing Salt-and-Pepper Noise in Text/Graphics Images. Proceedings of The Asia-Pacific Conference on Circuits and Systems (APCCAS'98), pp. 459-462, 1998
 * 
 * @param image $img The image to clean
 * @param int $k The size of the window
 * 
 * @return image The image that has been cleaned for salt and pepper noise
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2011-01-05
 */
function kfill_modified($img,$k)
{
	$iwhite = imagecolorallocate($img, 255, 255, 255);
	$iblack = imagecolorallocate($img, 0, 0, 0);

$src_size_x = imagesx($img);    
    $src_size_y = imagesy($img);
 
    //copy of original	
    $tmp = imagecreate($src_size_x,$src_size_y);
    imagecopy($tmp,$img,0,0,0,0,$src_size_x,$src_size_y);
    $twhite = imagecolorallocate($tmp, 255, 255, 255);
    $tblack = imagecolorallocate($tmp, 0, 0, 0);
		
    //blank
    $res = imagecreate($src_size_x,$src_size_y);
    $rblack = imagecolorallocate($res, 0, 0, 0);
    $rwhite = imagecolorallocate($res, 255, 255, 255);

    /*
      create a copy of the original image
      kfill algorithm sets pixel ON/OFF information in this image
    */
//    OneBitImageData *res_data = new OneBitImageData( src.size(), src.origin() );
//    OneBitImageView *res = new OneBitImageView(*res_data);
		
 //   OneBitImageData *tmp_data = new OneBitImageData( src.size(), src.origin() );
//    OneBitImageView *tmp = new OneBitImageView(*tmp_data);
//    image_copy_fill(src, *tmp);
		
//    int src_size_x = src.ncols(); // source image size x
  //  int src_size_y = src.nrows(); // source image size y
		
//    int x, y; // windows position (upper left core coordinate)
  //  Point c_lr; // windows position (lower right core coordinate)
		
	$c_lr = array();

    $ncp = ($k-2) * ($k-2);
    $ncp_required = ($ncp / 2.0); // number of core pixel required -- modified version
		
$n = "";
$r = "";
$c = "";

//    int core_pixel; // number of ON core pixel
		
  //  int r; // number of pixel in the neighborhood corners
  //  int n; // number of neighborhood pixel
  //  int c; // number of ccs in neighborhood
		
    // move window over the image
    for($y = 0 ; $y < ($src_size_y - ($k-3)) ; $y++) {
      for($x = 0 ; $x < ($src_size_x - ($k-3)) ; $x++) {
        // calculate lower right core coordinate
        $c_lr['x'] = ( $x + ($k - 3) );
	$c_lr['y'] = ( $y + ($k - 3) );
	

        // count core ON pixel
        $core_pixel = kfill_count_core_pixel($tmp, $x, $y, $c_lr);
		

//	print "Core pixel: $core_pixel NCP: $ncp_required<br/>";	
	
        // ON >= (k-2)^2/2 ?
        if($core_pixel >= $ncp_required) {
					
          // Examine in the Neighborhood
          kfill_get_condition_variables($tmp, $k, $x, $y, $src_size_x, $src_size_y, $n, $r, $c, $twhite, $tblack);
          $n = ( 4*($k-1) ) -$n;
          $r = 4 - $r;

//	print "$c $n $k $r<br/>";
					
          // eq. satisfied?
          if( ($c <= 1) && ( ($n > ((3*$k) - 4)) || ($n == (3*($k - 4))) && ($r == 2) ) ) {
            kfill_set_core_pixel($res, $x, $y, $c_lr, 0, $rwhite, $rblack);
	//	print "1 $x $y {$c_lr['x']} {$c_lr['y']} 0<br/>";
          } else {
            kfill_set_core_pixel($res, $x, $y, $c_lr, 1, $rwhite, $rblack);
	//	print "1 $x $y {$c_lr['x']} {$c_lr['y']} 1<br/>";
          }			
					
        } else {
					
          // Examine in the Neighborhood
          kfill_get_condition_variables($tmp, $k, $x, $y, $src_size_x, $src_size_y, $n, $r, $c, $twhite, $tblack);

          // eq. satisfied?					
          if( ($c <= 1) && ( ($n > ((3*$k) - 4)) || ($n == (3*($k - 4))) && ($r == 2) ) ) {
            kfill_set_core_pixel($res, $x, $y, $c_lr, 1, $rwhite, $rblack);
	//	print "2 $x $y {$c_lr['x']} {$c_lr['y']} 1<br/>";
          } else {
            kfill_set_core_pixel($res, $x, $y, $c_lr, 0, $rwhite, $rblack);
	//	print "2 $x $y {$c_lr['x']} {$c_lr['y']} 0<br/>";
          }

        }
								
      } // end for x
    } // end for y
		
    return $res;
}

/**
 * Find the bounding box of an image so as to remove blank
 * rows and blank columns
 * 
 * @param image $image 
 * 
 * @return array tlx,tly,brx,bry of bounding box
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-14
 */
function bounding_box(&$image)
{
	$tlx = 0;
	$tly = 0;
	$brx = imagesx($image) - 1;
	$bry = imagesy($image) - 1;

	//find first non empty left column (tlx)
	for ($tlx=0; $tlx < $brx; $tlx++)
		for ($j = 0; $j < $bry; $j++)
			if (imagecolorat($image, $tlx, $j) == 0) break 2;

	//find first non empty top row (tly)
	for ($tly=0; $tly < $bry; $tly++)
		for ($i = 0; $i < $brx; $i++)
			if (imagecolorat($image, $i, $tly) == 0) break 2;
 
	//find first non empty right column (brx)
	for (; $brx > 0; $brx--)
		for ($j = 0; $j < $bry; $j++)
			if (imagecolorat($image, $brx, $j) == 0) break 2;

	//find first non empty top row (tly)
	for (; $bry > 0; $bry--)
		for ($i = 0; $i < $brx; $i++)
			if (imagecolorat($image, $i, $bry) == 0) break 2;

	return array($tlx,$tly,$brx+1,$bry+1);
	//print_r( array($tlx,$tly,$brx+1,$bry+1));
	//die();
}


/* Recognise ONE character from the given image
 *
 */
function ocr($image,$btid,$qid)
{
	set_time_limit(60);
	return ocr_guess($image,$btid,$qid);
}






/*




The functions below here are not used but left for posterity






*/


















/**
 * TODO: short description.
 * 
 * @param int $image 
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2011-01-14
 */
function invert_image(&$image)
{
	$sx = imagesx($image);
	$sy = imagesy($image);

	$white = imagecolorallocate($image,255,255,255);
	$black = imagecolorallocate($image,0,0,0);

	for ($x = 0; $x < $sx; $x++)
	{
		for ($y = 0; $y < $sy; $y++)
		{
			imagecolorsettrue($image,$x,$y,imagecolorattrue($image,$x,$y),$white,$black);
		}
	}
}


/**
 * TODO: short description.
 * 
 * @param int $image 
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-13
 */
function ip(&$image,$x,$y)
{
	for ($i=0;$i<$x;$i++)
	{
		for($j=0;$j<$y;$j++)
		{
			print imagecolorattrue($image,$j,$i) . " ";
		}
		print "<br/>";
	}
			
}


/* Zhang-Suen thinning */

/**
 * Zhang-Suen thinning adapted from:
 * 
 * @link http://pages.cpsc.ucalgary.ca/~parker/thin.c
 *
 * @param int          
 * @param mixed        
 * @param mixed        
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-13
 */
function thin_b ($image)
{
/*		Thinning algorithm: CACM 1984 march (Zhang and Suen)	*/

	$a = array();
	$xdim = imagesx($image);
	$ydim = imagesy($image);
  	$y = imagecreate($xdim,$ydim);

	$white = imagecolorallocate($y, 255, 255, 255);
	$black = imagecolorallocate($y, 0, 0, 0);

	$br = 0;
	$ccc = 0;

	$cont = 1;
	while ($cont) {
		$cont = 0;

/*	Sub-iteration 1: */
		for ($i=0; $i<$xdim; $i++)
		  for ($j=0; $j<$ydim; $j++) {		/* Scan the entire image */
			if ((imagecolorat($image,$i,$j)) == 0) {
				imagecolorsettrue($y,$i,$j,0,$white,$black);
				//$y[$i][$j] = 0;
				continue;
			}
			$ar = t1a($image, $i, $j, $a, $br,$xdim,$ydim);	/* Function A */
			$p1 = $a[0]*$a[2]*$a[4];
			$p2 = $a[2]*$a[4]*$a[6];
			if ( ($ar == 1) && (($br>=2) && ($br<=6)) &&
				($p1 == 0) && ($p2 == 0) )  {
					//$y[$i][$j] = 1;
					imagecolorsettrue($y,$i,$j,1,$white,$black);
					$cont = 1;
			}
			else imagecolorsettrue($y,$i,$j,0,$white,$black);//$y[$i][$j] = 0;
		}
		
	subtr($y,$image,$xdim,$ydim,$white,$black);

	
/* Sub iteration 2: */
		for ($i=0; $i<$xdim; $i++)
		  for ($j=0; $j<$ydim; $j++) {		/* Scan the entire image */
			if (imagecolorat($image,$i,$j) == 0) {
				//$y[$i][$j] = 0;
				imagecolorsettrue($y,$i,$j,0,$white,$black);
				continue;
			}
			$ar = t1a ($image, $i, $j, $a, $br,$xdim,$ydim);	/* Function A */
			$p1 = $a[0]*$a[2]*$a[6];
			$p2 = $a[0]*$a[4]*$a[6];
			if ( ($ar == 1) && (($br>=2) && ($br<=6)) &&
				($p1 == 0) && ($p2 == 0) )  {
					//$y[$i][$j] = 1;
					imagecolorsettrue($y,$i,$j,1,$white,$black);
					$cont = 1;
			}
			else imagecolorsettrue($y,$i,$j,0,$white,$black); //$y[$i][$j] = 0;
		}
		subtr($y, $image,$xdim,$ydim,$white,$black);

	
		$ccc++;

	}
	return $image;
}



function subtr (&$a, &$b, $n,$m,&$white,&$black)
{
	for ($i=0; $i<$n; $i++)
		for ($j=0; $j<$m; $j++) 
		{
			$bc = imagecolorattrue($b,$i,$j);
			$ac = imagecolorattrue($a,$i,$j);
			imagecolorsettrue($b,$i,$j,($bc - $ac),$white,$black);
			//b[i][j] -= a[i][j];
		}
}


function t1a (&$image, $i, $j, &$a, &$b,$nn,$mm)
{
/*	Return the number of 01 patterns in the sequence of pixels
	P2 p3 p4 p5 p6 p7 p8 p9.					*/

	for ($n=0; $n<8; $n++) $a[$n] = 0;

	if ($i-1 >= 0) {
		$a[0] = imagecolorattrue($image,$i - 1, $j); //image[i-1][j];
		if ($j+1 < $mm) $a[1] = imagecolorattrue($image, $i-1, $j+1);
		if ($j-1 >= 0) $a[7] = imagecolorattrue($image,$i-1,$j-1);
	}
	if ($i+1 < $nn) {
		$a[4] = imagecolorattrue($image,$i+1,$j); //image[i+1][j];
		if ($j+1 < $mm) $a[3] = imagecolorattrue($image,$i+1,$j+1);//image[i+1][j+1];
		if ($j-1 >= 0) $a[5] = imagecolorattrue($image,$i+1,$j-1);//image[i+1][j-1];
	}
	if ($j+1 < $mm) $a[2] = imagecolorattrue($image,$i,$j+1);//image[i][j+1];
	if ($j-1 >= 0) $a[6] = imagecolorattrue($image,$i,$j-1);//image[i][j-1];

	$m=0;
	$b=0;
	for ($n=0; $n<7; $n++) {
		if (($a[$n]==0) && ($a[$n+1]==1)) $m++;
		$b = $b + $a[$n];
	}
	if (($a[7] == 0) && ($a[0] == 1)) $m++;
	$b = $b + $a[7];
	return $m;
}

/*		End of method B					*/



/**
 * Image skeletonizing converted to PHP from il98
 * 
 * @param mixed $pImg The Image to skeletonize
 * 
 * @return mixed The skeletonized image
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-11
 */
function k_Skeletonizing($pImg)
{
	$PointList = array();
	$RemoveList = array();
	$iCountX = 0;
	$iCountY = 0;
	$tempPoint = array();
	$NoPointsRemoved=0;
	$Iter=0;
	$prem = 0;
	$pntinpic=0;

	$xdim = imagesx($pImg);    
	$ydim = imagesy($pImg);
	/*$im2 = imagecreate($xdim + 2,$ydim + 2);
	$border_color = imagecolorallocate($im2, 255, 255, 255);
	imagefilledrectangle($im2,0,0,$xdim + 2, $ydim + 2, $border_color);
	imagecopy($im2,$pImg,1,1,0,0,$xdim,$ydim);
	$pImg = $im2;*/

	$white = imagecolorallocate($pImg, 255, 255, 255);
	$black = imagecolorallocate($pImg, 0, 0, 0);


	//print "$ydim:$xdim  \n";
	//print imagesy($pImg) . ":" . imagesx($pImg);

	//return $pImg;

	//k_SetBorder(1,1,pImg);
	//k_InitGroup(&PointList);
	//k_InitGroup(&RemoveList);

	/* Collecting black points */
	for($iCountY=1;$iCountY<$ydim - 1;$iCountY++)
	{
		for($iCountX=1;$iCountX<$xdim - 1;$iCountX++)
		{
			$tempPoint["x"]=$iCountX;
			$tempPoint["y"]=$iCountY;
			if ((imagecolorat($pImg,$iCountX,$iCountY))==0)
			{
				print "1";
				$pntinpic++;
				//k_Add2DPosToGroup(tempPoint,&PointList);
				$PointList[] = $tempPoint;
			}
			else
				print "0";
		}
		print "<br/>";
	}
	print("Black points in picture=$pntinpic\n");

	/* All 8 patterns have to remove 0 points before leaving */
	while (true)
	{
		/*printf("Iteration %d\n",++Iter);*/
		/* Testing picture with pattern B1 */
		$prem=0;
		if (count($PointList)==0) break;
		for($iCountX=0; $iCountX<count($PointList); $iCountX++)
		{
			$tempPoint=$PointList[$iCountX];
			if (!empty($tempPoint) && k_SkeletonCheckB1($tempPoint,$pImg))
			{
				$prem++;
				//k_RemovePosFromGroup(iCountX,&PointList);
				unset($PointList[$iCountX]);
				//Add2DPosToGroup(tempPoint,&RemoveList);
				$RemoveList[] = $tempPoint;
				//iCountX--; /* Must decrease iCountX when a point has been removed */
			}
		}
		/* Set all pixels positions in RemoveList in image to white */
		for($iCountX=0; $iCountX<count($RemoveList); $iCountX++)
			//k_SetPixel1bp(RemoveList.pPos[iCountX].x,RemoveList.pPos[iCountX].y,1,*pImg);
			imagesetpixel($pImg,$RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],$white);


		//k_EmptyGroup(&RemoveList);
		$RemoveList = array();
		/*printf("  Result of B1 check: removed points=%d\n",prem);*/

		if ($prem==0)
			$NoPointsRemoved++;
		else
			$NoPointsRemoved=0;

		if ($NoPointsRemoved>=8) break;

		/* Testing picture with pattern B2 */
		$prem=0;
		if (count($PointList)==0) break;
		for($iCountX=0; $iCountX<count($PointList); $iCountX++)
		{
			$tempPoint=$PointList[$iCountX];
			if (!empty($tempPoint) && k_SkeletonCheckB2($tempPoint,$pImg))
			{
				$prem++;
				//k_RemovePosFromGroup(iCountX,&PointList);
				unset($PointList[$iCountX]);
				//Add2DPosToGroup(tempPoint,&RemoveList);
				$RemoveList[] = $tempPoint;
				//iCountX--; /* Must decrease iCountX when a point has been removed */
			}
		}
		for($iCountX=0; $iCountX<count($RemoveList); $iCountX++)
			//k_SetPixel1bp(RemoveList.pPos[iCountX].x,RemoveList.pPos[iCountX].y,1,*pImg);
			imagesetpixel($pImg,$RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],$white);

		//k_EmptyGroup(&RemoveList);
		$RemoveList = array();
		/*printf("  Result of B2 check: removed points=%d\n",prem);*/

		if ($prem==0)
			$NoPointsRemoved++;
		else
			$NoPointsRemoved=0;

		if ($NoPointsRemoved>=8) break;

		/* Testing picture with pattern B3 */
		$prem=0;
		if (count($PointList)==0) break;
		for($iCountX=0; $iCountX<count($PointList); $iCountX++)
		{
			$tempPoint=$PointList[$iCountX];
			if (!empty($tempPoint) && k_SkeletonCheckB3($tempPoint,$pImg))
			{
				$prem++;
				//k_RemovePosFromGroup(iCountX,&PointList);
				unset($PointList[$iCountX]);
				//Add2DPosToGroup(tempPoint,&RemoveList);
				$RemoveList[] = $tempPoint;
				//iCountX--; /* Must decrease iCountX when a point has been removed */
			}
		}
		for($iCountX=0; $iCountX<count($RemoveList); $iCountX++)
			//k_SetPixel1bp(RemoveList.pPos[iCountX].x,RemoveList.pPos[iCountX].y,1,*pImg);
			imagesetpixel($pImg,$RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],$white);

		//k_EmptyGroup(&RemoveList);
		$RemoveList = array();
		/*printf("  Result of B3 check: removed points=%d\n",prem);*/

		if ($prem==0)
			$NoPointsRemoved++;
		else
			$NoPointsRemoved=0;

		if ($NoPointsRemoved>=8) break;

		/* Testing picture with pattern B4 */
		$prem=0;
		if (count($PointList)==0) break;
		for($iCountX=0; $iCountX<count($PointList); $iCountX++)
		{
			$tempPoint=$PointList[$iCountX];
			if (!empty($tempPoint) && k_SkeletonCheckB4($tempPoint,$pImg))
			{
				$prem++;
				//k_RemovePosFromGroup(iCountX,&PointList);
				unset($PointList[$iCountX]);
				//Add2DPosToGroup(tempPoint,&RemoveList);
				$RemoveList[] = $tempPoint;
				//iCountX--; /* Must decrease iCountX when a point has been removed */
			}
		}
		for($iCountX=0; $iCountX<count($RemoveList); $iCountX++)
			//k_SetPixel1bp(RemoveList.pPos[iCountX].x,RemoveList.pPos[iCountX].y,1,*pImg);
			imagesetpixel($pImg,$RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],$white);

		//k_EmptyGroup(&RemoveList);
		$RemoveList = array();
		/*printf("  Result of B4 check: removed points=%d\n",prem);*/

		if ($prem==0)
			$NoPointsRemoved++;
		else
			$NoPointsRemoved=0;

		if ($NoPointsRemoved>=8) break;

		/* Testing picture with pattern B5 */
		$prem=0;
		if (count($PointList)==0) break;
		for($iCountX=0; $iCountX<count($PointList); $iCountX++)
		{
			$tempPoint=$PointList[$iCountX];
			if (!empty($tempPoint) && k_SkeletonCheckB5($tempPoint,$pImg))
			{
				$prem++;
				//k_RemovePosFromGroup(iCountX,&PointList);
				unset($PointList[$iCountX]);
				//Add2DPosToGroup(tempPoint,&RemoveList);
				$RemoveList[] = $tempPoint;
				//iCountX--; /* Must decrease iCountX when a point has been removed */
			}
		}
		for($iCountX=0; $iCountX<count($RemoveList); $iCountX++)
			//k_SetPixel1bp(RemoveList.pPos[iCountX].x,RemoveList.pPos[iCountX].y,1,*pImg);
			imagesetpixel($pImg,$RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],$white);

		//k_EmptyGroup(&RemoveList);
		$RemoveList = array();
		/*printf("  Result of B5 check: removed points=%d\n",prem);*/

		if ($prem==0)
			$NoPointsRemoved++;
		else
			$NoPointsRemoved=0;

		if ($NoPointsRemoved>=8) break;

		/* Testing picture with pattern B6 */
		$prem=0;
		if (count($PointList)==0) break;
		for($iCountX=0; $iCountX<count($PointList); $iCountX++)
		{
			$tempPoint=$PointList[$iCountX];
			if (!empty($tempPoint) && k_SkeletonCheckB6($tempPoint,$pImg))
			{
				$prem++;
				//k_RemovePosFromGroup(iCountX,&PointList);
				unset($PointList[$iCountX]);
				//Add2DPosToGroup(tempPoint,&RemoveList);
				$RemoveList[] = $tempPoint;
				//iCountX--; /* Must decrease iCountX when a point has been removed */
			}
		}
		for($iCountX=0; $iCountX<count($RemoveList); $iCountX++)
			//k_SetPixel1bp(RemoveList.pPos[iCountX].x,RemoveList.pPos[iCountX].y,1,*pImg);
			imagesetpixel($pImg,$RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],$white);

		//k_EmptyGroup(&RemoveList);
		$RemoveList = array();
		/*printf("  Result of B6 check: removed points=%d\n",prem);*/

		if ($prem==0)
			$NoPointsRemoved++;
		else
			$NoPointsRemoved=0;

		if ($NoPointsRemoved>=8) break;

		/* Testing picture with pattern B7 */
		$prem=0;
		if (count($PointList)==0) break;
		for($iCountX=0; $iCountX<count($PointList); $iCountX++)
		{
			$tempPoint=$PointList[$iCountX];
			if (!empty($tempPoint) && k_SkeletonCheckB7($tempPoint,$pImg))
			{
				$prem++;
				//k_RemovePosFromGroup(iCountX,&PointList);
				unset($PointList[$iCountX]);
				//Add2DPosToGroup(tempPoint,&RemoveList);
				$RemoveList[] = $tempPoint;
				//iCountX--; /* Must decrease iCountX when a point has been removed */
			}
		}
		for($iCountX=0; $iCountX<count($RemoveList); $iCountX++)
			//k_SetPixel1bp(RemoveList.pPos[iCountX].x,RemoveList.pPos[iCountX].y,1,*pImg);
			imagesetpixel($pImg,$RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],$white);

		//k_EmptyGroup(&RemoveList);
		$RemoveList = array();
		/*printf("  Result of B7 check: removed points=%d\n",prem);*/

		if ($prem==0)
			$NoPointsRemoved++;
		else
			$NoPointsRemoved=0;

		if ($NoPointsRemoved>=8) break;

		/* Testing picture with pattern B8 */
		$prem=0;
		if (count($PointList)==0) break;
		for($iCountX=0; $iCountX<count($PointList); $iCountX++)
		{
			$tempPoint=$PointList[$iCountX];
			if (!empty($tempPoint) && k_SkeletonCheckB8($tempPoint,$pImg))
			{
				$prem++;
				//k_RemovePosFromGroup(iCountX,&PointList);
				unset($PointList[$iCountX]);
				//Add2DPosToGroup(tempPoint,&RemoveList);
				$RemoveList[] = $tempPoint;
				//iCountX--; /* Must decrease iCountX when a point has been removed */
			}
		}
		for($iCountX=0; $iCountX<count($RemoveList); $iCountX++)
			//k_SetPixel1bp(RemoveList.pPos[iCountX].x,RemoveList.pPos[iCountX].y,1,*pImg);
			imagesetpixel($pImg,$RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],$white);

		//k_EmptyGroup(&RemoveList);
		$RemoveList = array();
		/*printf("  Result of B8 check: removed points=%d\n",prem);*/

		if ($prem==0)
			$NoPointsRemoved++;
		else
			$NoPointsRemoved=0;

		if ($NoPointsRemoved>=8) break;


	}

		print("<br/>");
		$pntinpic =0;
	for($iCountY=1;$iCountY<$ydim - 1;$iCountY++)
	{
		for($iCountX=1;$iCountX<$xdim - 1;$iCountX++)
		{
			$tempPoint["x"]=$iCountX;
			$tempPoint["y"]=$iCountY;
			if ((imagecolorat($pImg,$iCountX,$iCountY))==0)
			{
				print "1";
				$pntinpic++;
				//k_Add2DPosToGroup(tempPoint,&PointList);
				$PointList[] = $tempPoint;
			}
			else
				print "0";
		}
		print "<br/>";
	}
	
	print("Black points in picture=$pntinpic\n");

	return $pImg;
}

function k_SkeletonCheckB1($pnt, &$pImg)
{
	$x=$pnt["x"];
	$y=$pnt["y"];
	if ((imagecolorattrue2($x-1,$y-1,$pImg)==1) && (imagecolorattrue2($x,$y-1,$pImg)==1) &&
	   (imagecolorattrue2($x+1,$y-1,$pImg)==1) && (imagecolorattrue2($x-1,$y+1,$pImg)==0) &&
	   (imagecolorattrue2($x,$y+1,$pImg)==0) && (imagecolorattrue2($x+1,$y+1,$pImg)==0))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function k_SkeletonCheckB2($pnt, &$pImg)
{
	$x=$pnt["x"];
	$y=$pnt["y"];
	if ((imagecolorattrue2($x-1,$y,$pImg)==0) && (imagecolorattrue2($x-1,$y+1,$pImg)==0) &&
	   (imagecolorattrue2($x,$y+1,$pImg)==0) && (imagecolorattrue2($x,$y-1,$pImg)==1) &&
	   (imagecolorattrue2($x+1,$y-1,$pImg)==1) && (imagecolorattrue2($x+1,$y,$pImg)==1))
	{
		return true;
	}
	else{
		return false;
	}
}

function k_SkeletonCheckB3($pnt, &$pImg)
{
	$x=$pnt["x"];
	$y=$pnt["y"];
	if ((imagecolorattrue2($x+1,$y-1,$pImg)==1) && (imagecolorattrue2($x+1,$y,$pImg)==1) &&
	   (imagecolorattrue2($x+1,$y+1,$pImg)==1) && (imagecolorattrue2($x-1,$y-1,$pImg)==0) &&
	   (imagecolorattrue2($x-1,$y,$pImg)==0) && (imagecolorattrue2($x-1,$y+1,$pImg)==0))
	{
		return true;
	}
	else{
		return false;
	}
}

function k_SkeletonCheckB4($pnt, &$pImg)
{
	$x=$pnt["x"];
	$y=$pnt["y"];
	if ((imagecolorattrue2($x+1,$y,$pImg)==1) && (imagecolorattrue2($x,$y+1,$pImg)==1) &&
	   (imagecolorattrue2($x+1,$y+1,$pImg)==1) && (imagecolorattrue2($x-1,$y-1,$pImg)==0) &&
	   (imagecolorattrue2($x,$y-1,$pImg)==0) && (imagecolorattrue2($x-1,$y,$pImg)==0))
	{
		return true;
	}
	else{
		return false;
	}
}

function k_SkeletonCheckB5($pnt, &$pImg)
{
	$x=$pnt["x"];
	$y=$pnt["y"];
	if ((imagecolorattrue2($x-1,$y+1,$pImg)==1) && (imagecolorattrue2($x,$y+1,$pImg)==1) &&
	   (imagecolorattrue2($x+1,$y+1,$pImg)==1) && (imagecolorattrue2($x-1,$y-1,$pImg)==0) &&
	   (imagecolorattrue2($x,$y-1,$pImg)==0) && (imagecolorattrue2($x+1,$y-1,$pImg)==0))
	{
		return true;
	}
	else{
		return false;
	}
}

function k_SkeletonCheckB6($pnt, &$pImg)
{
	$x=$pnt["x"];
	$y=$pnt["y"];
	if ((imagecolorattrue2($x-1,$y,$pImg)==1) && (imagecolorattrue2($x-1,$y+1,$pImg)==1) &&
	   (imagecolorattrue2($x,$y+1,$pImg)==1) && (imagecolorattrue2($x,$y-1,$pImg)==0) &&
	   (imagecolorattrue2($x+1,$y-1,$pImg)==0) && (imagecolorattrue2($x+1,$y,$pImg)==0))
	{
		return true;
	}
	else{
		return false;
	}
}

function k_SkeletonCheckB7($pnt, &$pImg)
{
	$x=$pnt["x"];
	$y=$pnt["y"];
	if ((imagecolorattrue2($x-1,$y-1,$pImg)==1) && (imagecolorattrue2($x-1,$y,$pImg)==1) &&
	   (imagecolorattrue2($x-1,$y+1,$pImg)==1) && (imagecolorattrue2($x+1,$y-1,$pImg)==0) &&
	   (imagecolorattrue2($x+1,$y,$pImg)==0) && (imagecolorattrue2($x+1,$y+1,$pImg)==0))
	{
		return true;
	}
	else{
		return false;
	}
}

function k_SkeletonCheckB8($pnt, &$pImg)
{
	$x=$pnt["x"];
	$y=$pnt["y"];
	if ((imagecolorattrue2($x-1,$y-1,$pImg)==1) && (imagecolorattrue2($x,$y-1,$pImg)==1) &&
	   (imagecolorattrue2($x-1,$y,$pImg)==1) && (imagecolorattrue2($x+1,$y,$pImg)==0) &&
	   (imagecolorattrue2($x,$y+1,$pImg)==0) && (imagecolorattrue2($x+1,$y+1,$pImg)==0))
	{
		return true;
	}
	else{
		return false;
	}
}


/**
 * Image thinning algorithm
 * Based on the function k_Thinning from ipl98
 * @link http://www.mip.sdu.dk/ipl98/
 * 
 * @param mixed $pImg 
 * 
 * @return The image thinned
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-11
 * @link
 */
function k_Thinning($pImg)
{
	$PointsRemoved = "";
	$m_Matrix22 = array(); //[3][3];
	$Iter=0;
	$prem1;	$prem2;
	$iCountX;
	$iCountY;
	$tempPoint;
	$pntinpic=0;
	$PointList = array();
	$RemoveList = array();
	$white = imagecolorallocate($pImg, 255, 255, 255);
	$black = imagecolorallocate($pImg, 0, 0, 0);
	//add a border of 1 pixel width, black to the image increasing its size
/*	$xdim = imagesx($pImg);    
	$ydim = imagesy($pImg);
	$im2 = imagecreate($xdim + 2,$ydim + 2);
	$border_color = imagecolorallocate($im2, 0, 0, 0);
	imagefilledrectangle($im2,0,0,$xdim + 2, $ydim + 2, $border_color);
	imagecopy($im2,$pImg,1,1,0,0,$xdim,$ydim);
	$pImg = $im2;
*/
	//return $pImg;

	//k_SetBorder(1,1,pImg);
	
	$PointsRemoved=false;
	$Iter++;
	/* step 1 Collecting the Black point in a list */
	$prem1 = 0;
	$prem2 = 0;
	for($iCountY=0;$iCountY<imagesy($pImg);$iCountY++)
		{
			for($iCountX=0;$iCountX<imagesx($pImg);$iCountX++)
			{
				if (imagecolorat($pImg,$iCountX,$iCountY)==0) /* if pixel is black */
				{
					$tempPoint["x"]=$iCountX;
					$tempPoint["y"]=$iCountY;
					$pntinpic++;
					if (k_ThinningSearchNeighbors($iCountX,$iCountY,$pImg,$m_Matrix22) &&
						k_ThinningCheckTransitions($m_Matrix22) &&
						k_ThinningStep1cdTests($m_Matrix22))
					{
						$prem1++;
						$PointsRemoved=true;
						$RemoveList[] = $tempPoint;
					}
					else
					{
						$PointList[] = $tempPoint;
						//k_Add2DPosToGroup($tempPoint,&PointList);
					}
				}
			}
		}
		//print_r($RemoveList);
		//print("Total black points: $pntinpic\n");
		/* Set all pixels positions in RemoveList in image to white */
		for($iCountX=0; $iCountX<count($RemoveList); $iCountX++)
		{
				imagesetpixel($pImg,$RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],$white);
		}
		//k_EmptyGroup(&RemoveList);
		$RemoveList = array();
		/* step 2 after step 1 which inserted points in list */
		if ($PointsRemoved)
		{
			for($iCountX=0; $iCountX<count($PointList); $iCountX++)
			{
				$tempPoint=$PointList[$iCountX];
				if (!empty($tempPoint) && k_ThinningSearchNeighbors($tempPoint["x"],$tempPoint["y"],$pImg,$m_Matrix22) &&
					k_ThinningCheckTransitions($m_Matrix22) &&
					k_ThinningStep2cdTests($m_Matrix22))
				{
					$prem2++;
					$PointsRemoved=true;
					/*k_RemovePosFromGroupSlow(iCountX,&PointList);*/
					//k_RemovePosFromGroup(iCountX,&PointList);
					unset($PointList[$iCountX]);
					//k_Add2DPosToGroup(tempPoint,&RemoveList);
					$RemoveList[] = $tempPoint;
					//$iCountX--; /* Must decrease iCountX when a point has been removed */
				}
			}
		}
		/* Set all pixels positions in RemoveList in image to white */
		for($iCountX=0; $iCountX<count($RemoveList); $iCountX++)
		{
			//k_SetPixel1bp($RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],1,$pImg);
				imagesetpixel($pImg,$RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],$white);
		}
		//k_EmptyGroup(&RemoveList);
		$RemoveList = array();
		//print("Iteration $Iter: Points removed: $prem1+$prem2=" . ($prem1+$prem2) . "\n");
		/* step 1 */
		while($PointsRemoved)
		{
			$prem1=0;
			$prem2=0;
			$Iter++;
			$PointsRemoved=false;
			for($iCountX=0; $iCountX<count($PointList); $iCountX++)
			{
				$tempPoint=$PointList[$iCountX];
				if ((!empty($tempPoint) && k_ThinningSearchNeighbors($tempPoint["x"],$tempPoint["y"],$pImg,$m_Matrix22)) &&
					(k_ThinningCheckTransitions($m_Matrix22[0])) &&
					(k_ThinningStep1cdTests($m_Matrix22[0])))
				{
					$prem1++;
					$PointsRemoved=true;
					/*k_RemovePosFromGroupSlow(iCountX,&PointList);*/
					//k_RemovePosFromGroup(iCountX,&PointList);
					unset($PointList[$iCountX]);
					//k_Add2DPosToGroup(tempPoint,&RemoveList);
					$RemoveList[] = $tempPoint;
					//$iCountX--; /* Must decrease iCountX when a point has been removed */
				}
			}
			/* Set all pixels positions in RemoveList in image to white */
			for($iCountX=0; $iCountX<count($RemoveList); $iCountX++)
			{
				//k_SetPixel1bp($RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],1,$pImg);
					imagesetpixel($pImg,$RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],$white);
			}
			$RemoveList = array();
			/* step 2 */
			for($iCountX=0; $iCountX < count($PointList); $iCountX++)
			{
				$tempPoint=$PointList[$iCountX];
				if (!empty($tempPoint) && k_ThinningSearchNeighbors($tempPoint["x"],$tempPoint["y"],$pImg,$m_Matrix22) &&
					k_ThinningCheckTransitions($m_Matrix22) &&
					k_ThinningStep2cdTests($m_Matrix22))
				{
					$prem2++;
					$PointsRemoved=true;
					/*k_RemovePosFromGroupSlow(iCountX,&PointList);*/
					//k_RemovePosFromGroup(iCountX,&PointList);
					unset($PointList[$iCountX]);
					//k_Add2DPosToGroup(tempPoint,&RemoveList);
					$RemoveList[] = $tempPoint;
					//$iCountX--; /* Must decrease iCountX when a point has been removed */
				}
			}
		
		
			/* Set all pixels positions in RemoveList in image to white */
			for($iCountX=0; $iCountX<count($RemoveList); $iCountX++)
			{
				//k_SetPixel1bp($RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],1,&$pImg);
					imagesetpixel($pImg,$RemoveList[$iCountX]["x"],$RemoveList[$iCountX]["y"],$white);
			}
			$RemoveList = array();
			//k_EmptyGroup(&RemoveList);
			//print("Iteration $Iter: Points removed: $prem1+$prem2=" . ($prem1 + $prem2) . "\n");
			//if ($Iter >20)
			//	return $pImg;
		}
	return $pImg;
}
/* performes the tests (c') and (d') in step 2 as explained in Gonzales and Woods page 493 */
/**
 * TODO: short description.
 * 
 * @param mixed $m_Matrix22 
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-11
 */
function k_ThinningStep2cdTests(&$m_Matrix22)
{
	if (($m_Matrix22[1][0]+$m_Matrix22[2][1]+$m_Matrix22[0][1]) &&
		($m_Matrix22[1][0]+$m_Matrix22[1][2]+$m_Matrix22[0][1]))
		return true;
	else
		return false;
}

/* performes the tests (c) and (d) in step 1 as explained in Gonzales and Woods page 492 */
/**
 * TODO: short description.
 * 
 * @param mixed $m_Matrix22 
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-11
 */
function k_ThinningStep1cdTests(&$m_Matrix22)
{
	if (($m_Matrix22[1][0]+$m_Matrix22[2][1]+$m_Matrix22[1][2]) &&
		($m_Matrix22[2][1]+$m_Matrix22[1][2]+$m_Matrix22[0][1]))
		return true;
	else
		return false;
}
/* returns true if there is exactly one transition
	in the region around the actual pixel */
/**
 * TODO: short description.
 * 
 * @param mixed $m_Matrix22 
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-11
 */
function k_ThinningCheckTransitions(&$m_Matrix22)
{
	$iTransitions=0;
	if (($m_Matrix22[0][0]==1) && ($m_Matrix22[1][0]==0)){
		++$iTransitions;}
	if (($m_Matrix22[1][0]==1) && ($m_Matrix22[2][0]==0)){
		++$iTransitions;}
	if (($m_Matrix22[2][0]==1) && ($m_Matrix22[2][1]==0)){
		++$iTransitions;}
	if (($m_Matrix22[2][1]==1) && ($m_Matrix22[2][2]==0)){
		++$iTransitions;}
	if (($m_Matrix22[2][2]==1) && ($m_Matrix22[1][2]==0)){
		++$iTransitions;}
	if (($m_Matrix22[1][2]==1) && ($m_Matrix22[0][2]==0)){
		++$iTransitions;}
	if (($m_Matrix22[0][2]==1) && ($m_Matrix22[0][1]==0)){
		++$iTransitions;}
	if (($m_Matrix22[0][1]==1) && ($m_Matrix22[0][0]==0)){
		++$iTransitions;}
	//print "\niTransitions: $iTransitions\n";
	if ($iTransitions==1)
		return true;
	else
		return false;
}

/**
 * TODO: short description.
 * 
 * @param int   $image 
 * @param mixed $x     
 * @param mixed $y     
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-11
 */
function imagecolorattrue2($x,$y,&$image)
{
	$r = imagecolorat($image,$x,$y);
//	print "$x,$y: $r<br/>";
	if ($r == 0) return 1;
	return 0;
}


/**
 * TODO: short description.
 * 
 * @param int   $image 
 * @param mixed $x     
 * @param mixed $y     
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-11
 */
function imagecolorattrue(&$image,$x,$y)
{
	$r = imagecolorat($image,$x,$y);
	if ($r != 0) return 1;
	return 0;
}


function k_ThinningSearchNeighbors($x, $y, &$pImg, &$m_Matrix22)
/* As (a) in Gonzales and Woods, between 2 and 6 black neighbors */
{
	//print "\n$x $y \n";
	//print_r($m_Matrix22);
	$BlackNeighbor=0;
	if (($m_Matrix22[0][0]=imagecolorattrue($pImg,$x-1,$y-1)) == 0){++$BlackNeighbor;}
	if (($m_Matrix22[1][0]=imagecolorattrue($pImg,$x  ,$y-1)) == 0){++$BlackNeighbor;}
	if (($m_Matrix22[2][0]=imagecolorattrue($pImg,$x+1,$y-1)) == 0){++$BlackNeighbor;}
	if (($m_Matrix22[0][1]=imagecolorattrue($pImg,$x-1,$y  )) == 0){++$BlackNeighbor;}
	if (($m_Matrix22[2][1]=imagecolorattrue($pImg,$x+1,$y  )) == 0){++$BlackNeighbor;}
	if (($m_Matrix22[0][2]=imagecolorattrue($pImg,$x-1,$y+1)) == 0){++$BlackNeighbor;}
	if (($m_Matrix22[1][2]=imagecolorattrue($pImg,$x  ,$y+1)) == 0){++$BlackNeighbor;}
	if (($m_Matrix22[2][2]=imagecolorattrue($pImg,$x+1,$y+1)) == 0){++$BlackNeighbor;}
	//print "\nBLACKNEIGH:$BlackNeighbor\n";
	if (($BlackNeighbor>=2) && ($BlackNeighbor<=6))
		return true;
	else
		return false;
}

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

/**
 * TODO: short description.
 * 
 * @param int $image 
 * 
 * @return TODO
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2011-01-14
 */
function image_to_text($image)
{
	$sx = imagesx($image);
	$sy = imagesy($image);

	for ($y = 0; $y < $sy; $y++)
	{
		for ($x = 0; $x < $sx; $x++)
			print imagecolorattrue($image,$x,$y);
		
		print "<br/>";
	}
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
	//unlink("$tmpfname.tif");
	unlink("$tmpfname");

	//return first character in file
	//print "OCR: $ocr<br/>";
	return substr(trim($ocr),0,1);

}








?>
