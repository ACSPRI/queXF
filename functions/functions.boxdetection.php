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


/* Find boxes in a horizontal selection
 * given an array containing the widths of horizontal lines
 *
 */
function horiBoxDetection($lw)
{
	//get most common width, assume it is box size
	$e = array_count_values($lw[0]);
	arsort($e);
	$tmp = array();

	//step through close values
	foreach ($e as $key => $val)
	{
		$nkey = "";
		for ($i = -9; $i < 10; $i++)
		{
			if (isset($tmp[$key + $i])) 
			{
				$nkey  = $key + $i;
				break;
			}
		}
		if ($nkey == "")
			$tmp[$key] = $val;
		else
			$tmp[$nkey] = $tmp[$nkey] + $val;

	}

	arsort($tmp);

	//Make sure that we are not using unworkable size boxes

	for ($i = 0; $i <= count($tmp); $i++)
	{
		$asize = key($tmp);
		if ($asize >= MIN_BOX_WIDTH)
			break;
		next($tmp);
	}


	$min = $asize - ($asize / 8);
	$max = $asize + ($asize / 8);

	$startx = array();

	for ($i = 0; $i < count($lw[0]); $i++)
	{
		if ($lw[0][$i] > $min && $lw[0][$i] < $max)
		{
			@$startx[$lw[1][$i]]++;
			@$starty[$lw[2][$i]]++;
		}
	}
	
	//print "<br/>Starting X values:<br/>";
	//arsort($startx);
	ksort($startx);

	//remove array values where there is the lowest number of them, and there is another within +-width/4
	//use bubble sort like tactic

	//print "<br/>Starting Y values: $min<br/>";
	ksort($starty);
	$tly = key($starty);
	end($starty);
	$bry = key($starty);


	//print_r($startx);
	//print "<br/>$tly $bry<br/>";

	$aliasArray =& $startx;

	foreach($aliasArray as $key => $value)
	{
		if (current($aliasArray))
		{
			//if there is a next element, move to it
			$key2 = key($aliasArray);
			$value2 = current($aliasArray);	

			//print "$key2 - $key < $asize / 4<br/>";
	
			//if they are close
			if (($key2 - $key) < ($asize / 3))
			{
				//remove the record with the lowest value
				if ($value < $value2){
					$aliasArray[$key] = 0;
				}
				else
				{
					$aliasArray[$key2] = 0;
				}		
			}
	
		}
			
	}

	$atlx = array();
	$atly = array();
	$abrx = array();
	$abry = array();

	foreach($aliasArray as $key => $value)
	{
		//ignore small boxes
		if ($value >= MIN_BOX_WIDTH)
		{
			//print "HORI BOX: $key,$tly : " . ($key + $asize) . ",$bry<br/>";
			$atlx[] = $key;
			$atly[] = $tly;
			$abrx[] = $key + $asize;
			$abry[] = $bry;
		}
	}

	$a = array();
	$a[] = $atlx;
	$a[] = $atly;
	$a[] = $abrx;
	$a[] = $abry;

	return $a;

}

/* vasBoxDetection
 * Handle specific case of a Visual Analog Scale
 *
 */
function vasBoxDetection($lw)
{
	//A VAS looks like this:
	//
	//
	//      |						|
	//	|						|
	//	|						|
	//	| --------------------------------------------- |
	//	|						|
	//	|						|
	//	|						|
	//
	//

	//Therefore LW will return these lines:
	//
	//       -----------------------------------------------
	//	 -----------------------------------------------
	//	 -----------------------------------------------
	//	 -                                             -
	//	 -----------------------------------------------
	//	 -----------------------------------------------
	//	 -----------------------------------------------


	//get most common width, make sure it is approx 1218
	//$e = array_count_values($lw[0]);
	//arsort($e);
	//$asize = key($e);

	$e = array_count_values($lw[0]);
	krsort($e);

	$asize = key($e);
	$ksize = $e[$asize];

	if ($ksize < 15)
	{
		$ksize = next($e);
		$asize = key($e);
	}


	if ($asize >= VAS_LENGTH_MIN && $asize <= VAS_LENGTH_MAX)
	{
		//length of line will be 1200px

		//find x of start of line
			//find most common x + gap
		$e = array_count_values($lw[1]);
		arsort($e);
		$xstart = key($e);

		$count = 0;
		$av = 0;
		foreach($lw[1] as $key => $val)
		{
			if ($val == $xstart)
			{
				if ($lw[0][$key] < 20)
				{
					$count++;
					$av += $lw[0][$key];
				}
			}
		}
		$gap = round($av/$count);

		$xstart += $gap;
				
		//find y of top of border
			//first y
		$ystart = reset($lw[2]);

		//find x of end of line
			//x of start + length of line
		$xend = $xstart + VAS_LENGTH_MIN;

		//find y of bottom of border
			//last y
		$yend = end($lw[2]);


		//draw 100 boxes each of size length of line/100 =~ 12px
		//print("$xstart,$ystart - $xend,$yend - $gap");

		$atlx = array();
		$atly = array();
		$abrx = array();
		$abry = array();		

		for ($i = 0; $i < VAS_BOXES; $i++)
		{
			$atlx[] = $xstart;
			$atly[] = $ystart;
			$xstart += VAS_BOX_WIDTH;
			$abrx[] = $xstart;
			$abry[] = $yend;
	
		}

		$a = array();
		$a[] = $atlx;
		$a[] = $atly;
		$a[] = $abrx;
		$a[] = $abry;

		return $a;
	

	}else
		return false; //not a VAS


}


/* Find boxes in a vertical selection
 * given an array containing the widths of horizontal lines
 *
 */

function vertBoxDetection($lw)
{
	//get most common width, assume it is box size
	$e = array_count_values($lw[0]);
	arsort($e);

	//step through close values
	foreach ($e as $key => $val)
	{
		$nkey = "";
		for ($i = -9; $i < 10; $i++)
		{
			if (isset($tmp[$key + $i])) 
			{
				$nkey  = $key + $i;
				break;
			}
		}
		if ($nkey == "")
			$tmp[$key] = $val;
		else
			$tmp[$nkey] = $tmp[$nkey] + $val;

	}

	arsort($tmp);

	//Make sure that we are not using unworkable size boxes

	for ($i = 0; $i <= count($tmp); $i++)
	{
		$asize = key($tmp);
		if ($asize >= MIN_BOX_WIDTH)
			break;
		next($tmp);
	}

	$min = $asize - ($asize / 8);
	$max = $asize + ($asize / 8);

	$startx = array();
	$starty = array();

	for ($i = 0; $i < count($lw[0]); $i++)
	{
		if ($lw[0][$i] > $min && $lw[0][$i] < $max)
		{
			@$startx[$lw[1][$i]]++;
			@$starty[$lw[2][$i]]++;
		}
	}
	
	//print "<br/>Starting X values:<br/>";
	//arsort($startx);
	//print_r($startx);

	ksort($startx);
	$tlx = key($startx);
	end($startx);
	$brx = key($startx);

	//print_r($startx);


	//remove array values where there is the lowest number of them, and there is another within +-width/4
	//use bubble sort like tactic

	//print "<br/>Starting Y values: $min<br/>";
	ksort($starty);

	//print_r($starty);

	//print_r($startx);
	//print "<br/><br/>";

	$bsy = array(); //box start
	$bey = array(); //box end

	$bsy[] = key($starty);

	$aliasArray = &$starty;

	foreach($aliasArray as $key => $value)
	{
		if (current($aliasArray))
		{
			//if there is a next element, move to it
			$key2 = key($aliasArray);
			$value2 = current($aliasArray);
	
		//	print "$key2 - $key < $asize / 4<br/>";
	
			//if they are close
			if (($key2 - $key) < ($asize / 3))
			{
				//remove the record with the lowest value
				if ($value < $value2){
					$aliasArray[$key] = 0;
				}
				else
				{
					$aliasArray[$key2] = 0;
				}		
			}else
			{
				//start box
				$bey[] = $key;
				$bsy[] = $key2;
			//	print "sb: $key2 - $key < $asize / 4<br/>";

			}
	
		}
			
	}

	end($aliasArray);
	$bey[] = key($aliasArray);

//	print_r($bsy);
//	print_r($bey);

	$atlx = array();
	$atly = array();
	$abrx = array();
	$abry = array();

	for ($i = 0; $i < count($bsy); $i++)
	{
		$atlx[] = $tlx;
		$atly[] = $bsy[$i];
		$abrx[] = $tlx + $asize;
		$abry[] = $bey[$i];
	}

	/*

	foreach($aliasArray as $key => $value)
	{
		if ($value != 0)
		{
			print "VERT BOX: $tlx,$key : " . ($brx + $asize) . ",$key<br/>";
			print "VERT BOX: tlx: $tlx key: $key value: $value brx: $brx<br/>";
			$atlx[] = $tlx;
			$atly[] = $key;
			$abrx[] = $brx + $asize;
			$abry[] = $key;
		}
	}
	 */

	$a = array();
	$a[] = $atlx;
	$a[] = $atly;
	$a[] = $abrx;
	$a[] = $abry;

	return $a;

}



/* Given an image and a bounding box
 * return an array containing the widths of lines in each row of pixels
 * not including the first line
 *
 */

function lineWidth($tlx,$tly,$brx,$bry,$image)
{
	$b = array();
	$al = array();
	$ax = array();
	$ay = array();
	$count = 0;
	
	$startx = $tlx;
	$starty = $tly;

	$col = imagecolorat($image, $tlx, $tly);
	for ($y = $tly; $y < $bry; $y++){
		
		$lc = 0;

		for ($x = $tlx; $x < $brx; $x++) {
			$rgb = imagecolorat($image, $x, $y);
			if ($rgb != $col)
			{
				if ($rgb == 0)
				{
					//don't return the first line on the row
					if ($lc != 0){
						$al[] = $count;
						$ax[] = $startx;
						$ay[] = $starty;
					}
				}
				$count = 0;
				$startx = $x;
				$starty = $y;
				$col = $rgb;
				$lc++;
				//print("$col<br/>");
			}
			$count++;	
		}
		//new line
	}
	$b[] = $al;
	$b[] = $ax;
	$b[] = $ay;
	return $b;

}




?>
