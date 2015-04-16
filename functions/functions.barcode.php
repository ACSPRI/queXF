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


/* Given a 1 bit image containing a barcode
 * Return an array of bar widths
 */
function barWidth($image,$scany)
{
	$xdim = imagesx($image);
	$b = array();
	$count = 0;

	$col = imagecolorat($image, 0, $scany);
	for ($x = 0; $x < $xdim; $x++) {
		$rgb = imagecolorat($image, $x, $scany);
		if ($rgb != $col)
		{
			$b[] = $count;
			//$b[]['colour'] = $rgb;
			$count = 0;
			$col = $rgb;
			//print("$col<br/>");
		}
		$count++;	
	}

	return $b;

}


/* Given an array of widths, return the guess of
 * the width of narrow and wide bars
 */
function nwWidth($array)
{
	$a = array();
	sort($array);
	$elements = count($array);

	if ($elements <= 1)
	{
		$a['n'] = 0;
		$a['w'] = 0;
		return $a;
	}

	$a['n'] = $array[(($elements/4)+1)];
	$a['w'] = $array[($elements-(($elements/4)+1))];

	//print ("N: {$a['n']} W: {$a['w']}<br/>");

	return $a;
}


/* Given an array of widths, an estimate of the widths
 * of wide and narrow bars, return narrow/wide rep as string
 */
function widthsToNW($widths,$narrow,$wide)
{
	//give a large tolerance

	$tolerance = (($wide - $narrow) - 1) / 2;
	$string = "";

	$nmin = ($narrow - $tolerance);
	if ($nmin <= 0) $nmin = 1;


	foreach($widths as $width)
	{
		if (($width >= ($nmin)) && ($width <= ($narrow + $tolerance))) $string .= "N";
		else if (($width >= ($wide - $tolerance)) && ($width <= ($wide + $tolerance))) $string .= "W";
		else $string .= "J"; //J for junk
	}

	//remove junk bits from start and end of string
	$firstJ = strpos($string,'J');
	if ($firstJ <= ((strlen($string) /4)))
		$string = substr($string,$firstJ + 1);

	$lastJ = strpos($string,'J', ((strlen($string) / 4) * 3));
	if ($lastJ >= ((strlen($string)/4)*3))
		$string = substr($string,0,$lastJ);

	return $string;
}

/* Given a string of n's and w's return a code (for CodaBar)
 * Bar widthds sourced from: http://www.barcodesymbols.com/codabar.htm
 *
 * Added an extra "N" to the end of each to account for intercode spacing (always narrow)
 */
function NWtoCodeCodaBar($s)
{
	$hash = array();

	$hash['NNNNNWWN'] = 0;
	$hash['NNNNWWNN'] = 1;
	$hash['NNNWNNWN'] = 2;
	$hash['WWNNNNNN'] = 3;
	$hash['NNWNNWNN'] = 4;
	$hash['WNNNNWNN'] = 5;
	$hash['NWNNNNWN'] = 6;
	$hash['NWNNWNNN'] = 7;
	$hash['NWWNNNNN'] = 8;
	$hash['WNNWNNNN'] = 9;
	$hash['NNNWWNNN'] = '-';
	$hash['NNWWNNNN'] = '$';
	$hash['WNNNWNWN'] = ':';
	$hash['WNWNNNWN'] = '/';
	$hash['WNWNWNNN'] = '.';
	$hash['NNWNWNWN'] = '+';
	$hash['NNWWNWNN'] = 'A';
	$hash['NWNWNNWN'] = 'B';
	$hash['NNNWNWWN'] = 'C';
	$hash['NNNWWWNN'] = 'D';
	
	$code = "";
	for ($i = 0; $i < strlen($s); $i+= 8)
	{
		$b1 = substr($s,$i,8);
		if (!isset($hash[$b1]))
			return "false";
		else
			$code .= $hash[$b1];
	}

	return $code;
}



/* Given a string of n's and w's return a code (for Interleaved 2 of 5)
 */
function NWtoCode($s)
{
	$hash = array();
	$hash['NNWWN'] = 0;
	$hash['WNNNW'] = 1;
	$hash['NWNNW'] = 2;
	$hash['WWNNN'] = 3;
	$hash['NNWNW'] = 4;
	$hash['WNWNN'] = 5;
	$hash['NWWNN'] = 6;
	$hash['NNNWW'] = 7;
	$hash['WNNWN'] = 8;
	$hash['NWNWN'] = 9;

	$code = "";
	//ignore the first 4 and last 3
	for ($i = 4; $i < (strlen($s) - 3); $i+= 10)
	{
		$b1 = $s[$i] . $s[$i + 2] . $s[$i + 4] . $s[$i + 6] . $s[$i + 8];
		$b2 = $s[$i + 1] . $s[$i + 3] . $s[$i + 5] . $s[$i + 7] . $s[$i + 9];
		if (!isset($hash[$b1]) || !isset($hash[$b2]))
			return "false";
		else
			$code .= $hash[$b1] . $hash[$b2];
	}

	return $code;
}

function validate($s)
{
	//length must be 10 * count + 7
	//must start with nnnn
	//must end with wnn
	
	if ( (fmod((strlen($s)-7.0),10.0) == 0) && (strncmp($s,"NNNN",4) == 0) && (strncmp(substr($s,(strlen($s)-3),3),"WNN",3) == 0)) return true;

	return false;

}


function validateCodaBar($s)
{
	//length + 1 must be a multiple of 8 (each character is 7 bars/spaces and a space)
	//must start and end with a start/stop character (A,B,C or D)
	
	if ( (fmod((strlen($s) + 1),8.0) == 0))
	{
		$start = NWtoCodeCodaBar(substr($s,0,8));
		$end = NWtoCodeCodaBar(substr($s,-7) . "N");
		if (($start == 'A' || $start  == 'B' || $start == 'C' || $start == 'D') && ($end == 'A' || $end  == 'B' || $end == 'C' || $end == 'D'))
			return true;	
	}
	return false;

}



/* Given a GD image, Find an interleaved 2 of 5 barcode and return it otherwise
 * return false
 *
 * Currently steps pixel by pixel (step = 1)
 *
 */
function barcode($image, $step = 1, $length = false, $numsonly = false)
{
	if (function_exists('imagefilter') &&
		function_exists('imagetruecolortopalette') &&
		function_exists('imagecolorset') &&
		function_exists('imagecolorclosest'))
	{
		//Gaussian blur to fill in holes from dithering
		imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
		//force two colors; dithering = FALSE
		imagetruecolortopalette($image, FALSE, 2);
		//find the closest color to black and replace it with actual black
		imagecolorset($image, imagecolorclosest($image, 0, 0, 0), 0, 0, 0);
		//find the closest color to white and replace it with actual white
		imagecolorset($image, imagecolorclosest($image, 255, 255, 255), 255, 255, 255);
	}
	//search

	$height = imagesy($image);

	for ($i = ($step); $i < $height - ($step); $i += ($step))
	{
		$a = barWidth($image,$i);
		$w = nwWidth($a);
		if ($w['n'] != 0 && $w['w'] != 0){
			$s = widthsToNW($a,$w['n'],$w['w']);
			if(validate($s)){
				$code = NWtoCode($s);
				if ($code != "false" && (!$length || strlen($code) == $length))
					return $code;
			}
			else if (validateCodaBar($s))
			{
				$code = NWtoCodeCodaBar($s . "N"); //remember to add the last space
				if ($code != "false")
				{
					$code = substr($code,1,-1); //remove the start and stop characters
					if (!$length || strlen($code) == $length)
						return $code;
				}
			}
		}
  }

  if (OCR_ENABLED)
  {
    //use tesseract to find the "barcode" or any text as OCR
    $tmp = tempnam(TEMPORARY_DIRECTORY, "BARCODE");
    imagepng($image,$tmp);
    exec(TESSERACT_BIN . " $tmp $tmp -psm 3"); //run tessearct in single line mode
    $result = file_get_contents($tmp . ".txt");
    unlink($tmp);
    unlink($tmp . ".txt");
  
    if (!empty($result))
    {
      //check length if set to check
      //
      //
      if ($length && !$numsonly)
      {
        $res = preg_match("/([0-9]{".$length."})/",$result,$matches);
        if ($res)
          return $matches[0];
      }

      $nums = preg_replace('/\D+/', '', $result); // numbers only

      if ($length && strlen($nums) == $length)
        return $nums; //if length is set and matching in length then return
      else if (!$length && !$numsonly)
        return $result; //if no length is set just return what is read by OCR
      else if (!$length && $numsonly)
        return $nums; //if no length is set just return what is read by OCR
    }
  }

	return false;
}

?>
