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


include("../functions/functions.import.php");
include("../functions/functions.xhtml.php");

xhtml_head("Import a directory of PDF files");

if (isset($_POST['dir']))
{
	$dir = $_POST['dir'];
	import_directory($dir);
}

?>	
<h1>Directory</h1>
<form enctype="multipart/form-data" action="" method="post">
<p>Enter directory local to the server (eg /mnt/iss/tmp/images): <input name="dir" type="text" value="<? echo realpath("../doc/filled"); ?>"/></p>
<p><input type="submit" value="Process directory" /></p>
</form>
<?
xhtml_foot();
?>
