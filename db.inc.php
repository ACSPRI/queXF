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

include_once(dirname(__FILE__) . '/config.inc.php');
include_once(dirname(__FILE__) . '/lang.inc.php');

/* DB FILE */

if (!(include_once(ADODB_DIR . 'adodb.inc.php')))
{
	print "<p>" . T_("ERROR: Please modify config.inc.php to point to your ADODb installation") . "</p>";
}
//if (!(include_once(ADODB_DIR . 'session/adodb-session2.php')))
//{
//	print "<p>" . T_("ERROR: Please modify config.inc.php to point to your ADODb installation") . "</p>";
//}


//global database variable
$db = newADOConnection(DB_TYPE);
$db->Connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->SetFetchMode(ADODB_FETCH_ASSOC);

//store session in database (see sessions2 table)
//ADOdb_Session::config(DB_TYPE, DB_HOST, DB_USER, DB_PASS, DB_NAME,$options=false);


?>
