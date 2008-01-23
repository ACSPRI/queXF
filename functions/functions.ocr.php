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
