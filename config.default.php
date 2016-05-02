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



/* DEFAULT CONFIGURATION FILE */

/* NOTE: Do not modify this file
 * Make your changes in the file: config.inc.php by defining any directive you wish to change
 *
 * For example, if you wish to change the ADODB_DIR, do the following in config.inc.php:
 *
 * define('ADODB_DIR', dirname(__FILE__).'/../adodb/');
 *
 */

if (!defined('DB_USER')) define('DB_USER', 'quexf');
if (!defined('DB_PASS')) define('DB_PASS', 'quexf');
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'quexf');
if (!defined('DB_TYPE')) define('DB_TYPE', 'mysqlt');

if (!defined('ADODB_DIR')) define('ADODB_DIR', '/usr/share/php/adodb/');

//Fill ratios of boxes (1 is empty, 0 is filled completely)
if (!defined('MULTIPLE_CHOICE_MIN_FILLED')) define('MULTIPLE_CHOICE_MIN_FILLED', 0.85);
if (!defined('MULTIPLE_CHOICE_MAX_FILLED')) define('MULTIPLE_CHOICE_MAX_FILLED', 0.60);
if (!defined('SINGLE_CHOICE_MIN_FILLED')) define('SINGLE_CHOICE_MIN_FILLED', 0.85);
if (!defined('SINGLE_CHOICE_MAX_FILLED')) define('SINGLE_CHOICE_MAX_FILLED', 0.60);

//Blank page detection
if (!defined('BLANK_PAGE_DETECTION')) define('BLANK_PAGE_DETECTION', true);
if (!defined('PROCESS_MISSING_PAGES')) define('PROCESS_MISSING_PAGES',true);

//REQUIRED: Ghostscript binary
if (!defined('GS_BIN')) define('GS_BIN', "/usr/bin/gs");

//PHP Executables (for forking when running background processes)
if (!defined('WINDOWS_PHP_EXEC')) define('WINDOWS_PHP_EXEC', "start /b php");
if (!defined('PHP_EXEC')) define('PHP_EXEC', "php");

//Temporary directory
if (!defined('TEMPORARY_DIRECTORY')) define('TEMPORARY_DIRECTORY', "/tmp");

//ICR
if (!defined('ICR_ENABLED')) define('ICR_ENABLED', true);
if (!defined('ICR_FILL_MIN')) define('ICR_FILL_MIN', 0.935);
if (!defined('ICR_TRAIN_LIMIT')) define('ICR_TRAIN_LIMIT', 2000);
if (!defined('BOX_EDGE')) define('BOX_EDGE',8); //take this many pixels of the side due to form box drawing

//Old OCR Stuff
if (!defined('CONVERT_BIN')) define('CONVERT_BIN', "/usr/bin/convert");

//Use Tesseract OCR for form identification / OCR on page
if (!defined('TESSERACT_BIN')) define('TESSERACT_BIN', "/usr/bin/tesseract");
if (!defined('OCR_ENABLED')) define('OCR_ENABLED',false);

//Page size
if (!defined('PAGE_WIDTH')) define('PAGE_WIDTH',2480);
if (!defined('PAGE_HEIGHT')) define('PAGE_HEIGHT',3508);

//Display variables
if (!defined('DISPLAY_PAGE_WIDTH')) define('DISPLAY_PAGE_WIDTH',800);
if (!defined('DISPLAY_GAP')) define('DISPLAY_GAP',40); //number of pixels higher than the box group for top of page

//Box group
if (!defined('BOX_GROUP_BACKGROUND_COLOUR')) define('BOX_GROUP_BACKGROUND_COLOUR','orange'); //colour for the box group background (HTML colours)
if (!defined('BOX_GROUP_BACKGROUND_OPACITY')) define('BOX_GROUP_BACKGROUND_OPACITY',0.40);
if (!defined('BOX_OPACITY')) define('BOX_OPACITY',0.25); //opacity of a box
if (!defined('BOX_SELECT_COLOUR')) define('BOX_SELECT_COLOUR','blue');
if (!defined('BOX_FOCUS_COLOUR')) define('BOX_FOCUS_COLOUR','yellow');
if (!defined('BOX_BACKGROUND_COLOUR')) define('BOX_BACKGROUND_COLOUR','white');

//Banding 
if (!defined('TEMPORARY_COLOUR')) define('TEMPORARY_COLOUR','red');
if (!defined('SINGLECHOICE_COLOUR')) define('SINGLECHOICE_COLOUR','green');
if (!defined('MULTIPLECHOICE_COLOUR')) define('MULTIPLECHOICE_COLOUR','blue');
if (!defined('TEXT_COLOUR')) define('TEXT_COLOUR','purple');
if (!defined('NUMBER_COLOUR')) define('NUMBER_COLOUR','orange');
if (!defined('BARCODE_COLOUR')) define('BARCODE_COLOUR','brown');
if (!defined('LONGTEXT_COLOUR')) define('LONGTEXT_COLOUR','pink');
if (!defined('BAND_OPACITY')) define('BAND_OPACITY',0.60);
if (!defined('BAND_DEFAULT_ZOOM')) define('BAND_DEFAULT_ZOOM',3);

//Approximate widths of page guide lines (in pixels)
if (!defined('HORI_WIDTH')) define('HORI_WIDTH',6);
if (!defined('VERT_WIDTH')) define('VERT_WIDTH',6);

//Approximate widths of page guide boxes (in pixels)
if (!defined('HORI_WIDTH_BOX')) define('HORI_WIDTH_BOX',54);
if (!defined('VERT_WIDTH_BOX')) define('VERT_WIDTH_BOX',54);

//Page guide lines bounding boxes as portions instead of specified manually
if (!defined('PAGE_GUIDE_X_PORTION')) define('PAGE_GUIDE_X_PORTION',0.25); //portion of width of page for edge detection
if (!defined('PAGE_GUIDE_Y_PORTION')) define('PAGE_GUIDE_Y_PORTION',0.17); //portion of height of page for edge detection

//Barcode positions on page defined as portions of the page
//Defaults to the top right hand side of the page, from half the page width down to 20% of the height of the page
if (!defined('BARCODE_TLX_PORTION')) define('BARCODE_TLX_PORTION',0.5); //Top left X
if (!defined('BARCODE_TLY_PORTION')) define('BARCODE_TLY_PORTION',0); //Top left Y
if (!defined('BARCODE_BRX_PORTION')) define('BARCODE_BRX_PORTION',1); //Bottom right X
if (!defined('BARCODE_BRY_PORTION')) define('BARCODE_BRY_PORTION',0.2); //Bottom right Y

//Barcode positions on page defined as portions of the page (second location)
if (!defined('BARCODE_TLX_PORTION2')) define('BARCODE_TLX_PORTION2',0); //Top left X
if (!defined('BARCODE_TLY_PORTION2')) define('BARCODE_TLY_PORTION2',0.8); //Top left Y
if (!defined('BARCODE_BRX_PORTION2')) define('BARCODE_BRX_PORTION2',0.5); //Bottom right X
if (!defined('BARCODE_BRY_PORTION2')) define('BARCODE_BRY_PORTION2',1); //Bottom right Y

//Line widths
if (!defined('VAS_LENGTH_MIN')) define('VAS_LENGTH_MIN',1200); //Length in pixels of a VAS line
if (!defined('VAS_LENGTH_MAX')) define('VAS_LENGTH_MAX',1236); //Length in pixels of a VAS line
if (!defined('VAS_BOXES')) define('VAS_BOXES',100); //Number of boxes to generate for a VAS line
if (!defined('VAS_BOX_WIDTH')) define('VAS_BOX_WIDTH',12); //width of VAS boxes generated in pixels

//Box widths
if (!defined('MIN_BOX_WIDTH')) define('MIN_BOX_WIDTH',20); //minimum width of a box in pixels

//Time to wait before checking directory for new files (in seconds)
if (!defined('PROCESS_SLEEP')) define('PROCESS_SLEEP',3600); 

//Whether to check if a scan is to be split (side by side scanning, i.e. A3 size with two pages side by side, to be split to 2 x A4)
if (!defined('SPLIT_SCANNING')) define('SPLIT_SCANNING',true);
if (!defined('SPLIT_SCANNING_THRESHOLD')) define('SPLIT_SCANNING_THRESHOLD',10);

//The length of a barcode for the page id
if (!defined('BARCODE_LENGTH_PID')) define('BARCODE_LENGTH_PID',8); //Length in chars of a barcode identifying a page
if (!defined('BARCODE_LENGTH_PID2')) define('BARCODE_LENGTH_PID2',8); //Length in chars of a barcode identifying a page (second location)

//Whether to assign a form if there are undetected pages for this form
if (!defined('MISSING_PAGE_ASSIGN')) define('MISSING_PAGE_ASSIGN',false); 

//Whether to assign a form if pages are missing from the scan
if (!defined('VERIFY_WITH_MISSING_PAGES')) define('VERIFY_WITH_MISSING_PAGES',false); 

/**
 * Date time format for displaying 
 * 
 * see http://dev.mysql.com/doc/refman/5.0/en/date-and-time-functions.html#function_date-format 
 * for configuration details for DATE_TIME_FORMAT and TIME_FORMAT
 */
if (!defined('DATE_TIME_FORMAT')) define('DATE_TIME_FORMAT','%a %d %b %I:%i%p'); 

/**
 * Number of log records to display
 */
if (!defined('PROCESS_LOG_LIMIT')) define('PROCESS_LOG_LIMIT', 500);


/*
 * The width and height in pixels of the popup boxes when banding for editing the values and labels of a box
 */
if (!defined('LABEL_HEIGHT')) define('LABEL_HEIGHT', 15);
if (!defined('LABEL_WIDTH')) define('LABEL_WIDTH', 85);
if (!defined('VALUE_HEIGHT')) define('VALUE_HEIGHT', 15);
if (!defined('VALUE_WIDTH')) define('VALUE_WIDTH', 15);

//Debugging
if (!defined('DEBUG')) define('DEBUG', false);

//Automatic verification for single choice questions where there is at least 2 choices
if (!defined('SINGLE_CHOICE_AUTOMATIC_VERIFICATION')) define('SINGLE_CHOICE_AUTOMATIC_VERIFICATION', true);

//Store images in database by default (otherwise files in images directory)
//Note this will only apply to new imports - old imports will remain either as 
//files or in the database - but queXF will be able to read them regardless
if (!defined('IMAGES_IN_DATABASE')) define('IMAGES_IN_DATABASE', true);
if (!defined('IMAGES_DIRECTORY')) define('IMAGES_DIRECTORY', dirname(__FILE__) . '/images/');

//Whether to delete system detected records on verification (default)
if (!defined('DELETE_ON_VERIFICATION')) define('DELETE_ON_VERIFICATION', true);

//The gray/grey scale level to cut off at when converting to monochrome (0-255)
if (!defined('IMAGE_THRESHOLD')) define('IMAGE_THRESHOLD', 221);



?>
