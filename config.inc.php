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



/* CONFIGURATION FILE */

define('DB_USER', 'phpteleform');
define('DB_PASS', 'phpteleform');
define('DB_HOST', 'database.dcarf');
define('DB_NAME', 'phpteleform');
define('DB_TYPE', 'mysqlt');

define('ADODB_DIR', dirname(__FILE__).'/../adodb/');

//Fill ratios of boxes (1 is empty, 0 is filled completely)
define('MULTIPLE_CHOICE_MIN_FILLED', 0.85);
define('MULTIPLE_CHOICE_MAX_FILLED', 0.60);
define('SINGLE_CHOICE_MIN_FILLED', 0.85);
define('SINGLE_CHOICE_MAX_FILLED', 0.60);

//Blank page detection
define('BLANK_PAGE_DETECTION', true);

//REQUIRED: Ghostscript binary
define('GS_BIN', "/usr/bin/gs");

//Temporary directory
define('TEMPORARY_DIRECTORY', "/tmp");

//OCR requires CONVERT_BIN (imagemagick convert binary) and TESSERACT_BIN (tessearct binary) to be enabled
define('OCR_ENABLED', false);
define('OCR_FILL_MIN', 0.95);
define('BOX_EDGE',5); //take this many pixels of the side due to form box drawing
define('CONVERT_BIN', "/usr/bin/convert");
define('TESSERACT_BIN', "/usr/bin/tesseract");

//Page size
define('PAGE_WIDTH',2480);
define('PAGE_HEIGHT',3508);

//Display variables
define('DISPLAY_PAGE_WIDTH',800);
define('DISPLAY_GAP',40); //number of pixels higher than the box group for top of page

//Box group
define('BOX_GROUP_BACKGROUND_COLOUR','orange'); //colour for the box group background (HTML colours)
define('BOX_GROUP_BACKGROUND_OPACITY',0.40);
define('BOX_OPACITY',0.25); //opacity of a box
define('BOX_SELECT_COLOUR','green');
define('BOX_FOCUS_COLOUR','yellow');
define('BOX_BACKGROUND_COLOUR','white');

//Banding 
define('TEMPORARY_COLOUR','red');
define('SINGLECHOICE_COLOUR','green');
define('MULTIPLECHOICE_COLOUR','blue');
define('TEXT_COLOUR','purple');
define('NUMBER_COLOUR','orange');
define('BARCODE_COLOUR','brown');
define('LONGTEXT_COLOUR','pink');
define('BAND_OPACITY',0.60);

//Approximate widths of page guide lines (in pixels)
define('HORI_WIDTH',6);
define('VERT_WIDTH',6);

//Locations of page guide lines bounding box (in pixels)
//Top left horizontal
define('TL_HORI_TLX',90);
define('TL_HORI_TLY',90);
define('TL_HORI_BRX',480);
define('TL_HORI_BRY',280);

//Top left vertical
define('TL_VERT_TLX',90);
define('TL_VERT_TLY',90);
define('TL_VERT_BRX',280);
define('TL_VERT_BRY',480);

//Top right horizontal
define('TR_HORI_TLX',1980);
define('TR_HORI_TLY',90);
define('TR_HORI_BRX',2370);
define('TR_HORI_BRY',280);

//Top right vertical
define('TR_VERT_TLX',2210);
define('TR_VERT_TLY',90);
define('TR_VERT_BRX',2400);
define('TR_VERT_BRY',480);

//Bottom left horizontal
define('BL_HORI_TLX',90);
define('BL_HORI_TLY',3250);
define('BL_HORI_BRX',480);
define('BL_HORI_BRY',3400);

//Bottom left vertical
define('BL_VERT_TLX',90);
define('BL_VERT_TLY',3000);
define('BL_VERT_BRX',280);
define('BL_VERT_BRY',3390);

//Bottom right horizontal
define('BR_HORI_TLX',1980);
define('BR_HORI_TLY',3250);
define('BR_HORI_BRX',2370);
define('BR_HORI_BRY',3400);

//Bottom right vertical
define('BR_VERT_TLX',2210);
define('BR_VERT_TLY',3000);
define('BR_VERT_BRX',2400);
define('BR_VERT_BRY',3390);

//Barcode position on page (bounding box)
define('BARCODE_TLX',1500); //Top left X
define('BARCODE_TLY',5); //Top left Y
define('BARCODE_BRX',2327); //Bottom right X
define('BARCODE_BRY',200); //Bottom right Y

//Line widths
define('VAS_LENGTH_MIN',1200); //Length in pixels of a VAS line
define('VAS_LENGTH_MAX',1236); //Length in pixels of a VAS line
define('VAS_BOXES',100); //Number of boxes to generate for a VAS line
define('VAS_BOX_WIDTH',12); //width of VAS boxes generated in pixels

?>
