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

/* See the file: config.default.php for more configuration directives
 *
 * If there is a directive that you wish to change, DO NOT modify it on config.default.php
 *
 * Copy the define part of the directive to this file and edit it here.
 *
 */

define('DB_USER', 'quexf');
define('DB_PASS', 'quexf');
define('DB_HOST', 'database.dcarf');
define('DB_NAME', 'quexf');

define('ADODB_DIR', '/usr/share/php/adodb/');

define('BLANK_PAGE_DETECTION', true);

define('PROCESS_MISSING_PAGES',true);

//REQUIRED: Ghostscript binary
define('GS_BIN', "/usr/bin/gs");

//Temporary directory
define('TEMPORARY_DIRECTORY', "/tmp");

//OCR requires CONVERT_BIN (imagemagick convert binary) and TESSERACT_BIN (tessearct binary) to be enabled
define('OCR_ENABLED', false);

define('CONVERT_BIN', "/usr/bin/convert");
define('TESSERACT_BIN', "/usr/bin/tesseract");


define('PAGE_WIDTH',2480);
define('PAGE_HEIGHT',3508);
define('TL_VERT_TLX',135);
define('TL_VERT_TLY',90);
define('TL_VERT_BRX',366);
define('TL_VERT_BRY',504);
define('TL_HORI_TLX',135);
define('TL_HORI_TLY',93);
define('TL_HORI_BRX',531);
define('TL_HORI_BRY',288);
define('TR_VERT_TLX',2208);
define('TR_VERT_TLY',90);
define('TR_VERT_BRX',2397);
define('TR_VERT_BRY',480);
define('TR_HORI_TLX',1980);
define('TR_HORI_TLY',90);
define('TR_HORI_BRX',2370);
define('TR_HORI_BRY',279);
define('BL_VERT_TLX',114);
define('BL_VERT_TLY',2865);
define('BL_VERT_BRX',351);
define('BL_VERT_BRY',3333);
define('BL_HORI_TLX',117);
define('BL_HORI_TLY',3105);
define('BL_HORI_BRX',618);
define('BL_HORI_BRY',3360);
define('BR_VERT_TLX',2145);
define('BR_VERT_TLY',2853);
define('BR_VERT_BRX',2376);
define('BR_VERT_BRY',3315);
define('BR_HORI_TLX',1872);
define('BR_HORI_TLY',3141);
define('BR_HORI_BRX',2364);
define('BR_HORI_BRY',3378);
define('BARCODE_TLX',1500);
define('BARCODE_TLY',3);
define('BARCODE_BRX',2325);
define('BARCODE_BRY',198);


//Do not remove the following line:
include(dirname(__FILE__) . '/config.default.php');
?>
