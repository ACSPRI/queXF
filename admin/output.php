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


include ("../config.inc.php");
include ("../functions/functions.output.php");

if (isset($_GET['ddi']))
{
	export_ddi(intval($_GET['ddi']));
	exit();
}

if (isset($_GET['data']))
{
	outputdata(intval($_GET['data']));
	exit();
}


?>

<html>
<head>
<title>Output data</title>
</head>
<body>

<?


$sql = "SELECT qid,description
	FROM questionnaires
	ORDER BY qid ASC";


$qs = $db->GetAll($sql);


foreach ($qs as $q)
{
	print "{$q['description']}: <a href=\"{$_SERVER['PHP_SELF']}?data={$q['qid']}\">Data</a> <a href=\"{$_SERVER['PHP_SELF']}?ddi={$q['qid']}\">DDI</a><br/>";
}



?>
</body>
</html>

