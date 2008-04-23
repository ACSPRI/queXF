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


if (!(include_once(dirname(__FILE__).'/../adodb/adodb.inc.php')))
{
	print "<p>ERROR: Please modify config.inc.php to point to your ADODb installation</p>";
}
if (!(include_once(dirname(__FILE__).'/../adodb/session/adodb-session2.php')))
{
	print "<p>ERROR: Please modify config.inc.php to point to your ADODb installation</p>";
}


define('DB_USER', 'quexf');
define('DB_PASS', 'quexf');
define('DB_HOST', 'databasedev.dcarf');
define('DB_NAME', 'quexf');
define('DB_TYPE', 'mysqlt');

define('MULTIPLE_CHOICE_MIN_FILLED', 0.85);
define('MULTIPLE_CHOICE_MAX_FILLED', 0.60);
define('SINGLE_CHOICE_MIN_FILLED', 0.85);
define('SINGLE_CHOICE_MAX_FILLED', 0.60);

//Blank page detection
define('BLANK_PAGE_DETECTION', true);

//REQUIRED: Ghostscript binary
define('GS_BIN', "/usr/bin/gs");

//OCR requires CONVERT_BIN (imagemagick convert binary) and TESSERACT_BIN (tessearct binary) to be enabled
define('OCR_ENABLED', false);
define('BOX_EDGE',5); //take this many pixels of the side due to form box drawing
define('CONVERT_BIN', "/usr/bin/convert");
define('TESSERACT_BIN', "/usr/bin/tesseract");

//global database variable
$db = newADOConnection(DB_TYPE);
$db->Connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->SetFetchMode(ADODB_FETCH_ASSOC);

//store session in database (see sessions2 table)
ADOdb_Session::config(DB_TYPE, DB_HOST, DB_USER, DB_PASS, DB_NAME,$options=false);


?>
