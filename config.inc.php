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


/* CONFIGURATION FILE */

/* See the file: config.default.php for more configuration directives
 *
 * If there is a directive that you wish to change, DO NOT modify it on config.default.php
 *
 * Copy the define part of the directive to this file and edit it here.
 *
 */

define('DB_USER','root');
define('DB_PASS','example');
define('DB_HOST','mysql');
define('DB_NAME','quexf');
define('DB_SSL',null);

define('ADODB_DIR', '/usr/share/php/adodb/');

define('BLANK_PAGE_DETECTION', true);

define('PROCESS_MISSING_PAGES',true);

//REQUIRED: Ghostscript binary
define('GS_BIN', "/usr/bin/gs");

define('DEBUG', false);

define('OCR_ENABLED','true');

define('IMAGES_DIRECTORY','/images/');

define('SCANS_DIRECTORY','/forms/');

//Temporary directory
define('TEMPORARY_DIRECTORY', "/tmp");

define('PROCESS_SLEEP',600); 

define('HORI_WIDTH_BOX','58');
define('VERT_WIDTH_BOX','58');

define('BARCODE_TLX_PORTION','0.5');
define('BARCODE_TLY_PORTION','0');
define('BARCODE_BRX_PORTION','1');
define('BARCODE_BRY_PORTION','0.1');

define('BARCODE_TLX_PORTION2','0.5');
define('BARCODE_TLY_PORTION2','0.9');
define('BARCODE_BRX_PORTION2','0.25');
define('BARCODE_BRY_PORTION2','1');

define('DISPLAY_PAGE_WIDTH',800); //width of page display

define('MULTIPLE_CHOICE_MIN_FILLED','0.85');
define('MULTIPLE_CHOICE_MAX_FILLED','0.6');
define('SINGLE_CHOICE_MIN_FILLED','0.85');
define('SINGLE_CHOICE_MAX_FILLED','0.6');

define('HTPASSWD_PATH','/opt/quexf/password');
define('HTGROUP_PATH','/opt/quexf/group');



//Do not remove the following line:
include(dirname(__FILE__) . '/config.default.php');
?>
