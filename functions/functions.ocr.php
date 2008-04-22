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


/**
 *Character recognition algorithm from here:
 *  http://www.cs.berkeley.edu/~fateman/kathey/char_recognition.html
 */


/**
 * Return the most likely character given the box data
 */
function quexf_ocr($boxes)
{
	include_once(dirname(__FILE__).'/../config.inc.php');

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
		FROM ocrtrain
		ORDER BY confidence ASC
		LIMIT 1";

	$rs = $db->GetRow($sql);

	print $rs['val'] . ": " . $rs['confidence'] . "<br/>";

	if ($rs['confidence'] < 10000000)
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

	$edge = 5; //take this many pixels of the side due to form box drawing

	for ($x = $a['tlx'] + $edge; $x < $a['brx'] - $edge; $x++) {
		for ($y = $a['tly'] + $edge; $y < $a['bry'] - $edge; $y++) {
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
