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
include("../functions/functions.process.php");

if (isset($_POST['dir']) && isset($_POST['watch']))
{
	$dir = $_POST['dir'];
	//start watching process
	start_process(realpath(dirname(__FILE__) . "/process.php") . " $dir");
}

$p = is_process_running();

if ($p)
{
	if (isset($_GET['kill']))
		kill_process($p);

	xhtml_head(T_("Import a directory of PDF files"),true,false,false,false,10);

	print "<h1>" . T_("Process") . " $p " . T_("running...") . "</h1>";

	print "<h2>" . T_("Note: This page will automatically refresh every 10 seconds") . "</h2>";

	if (is_process_killed($p))
		print "<h3>" . T_("Kill signal sent: Please wait..." ) . "</h3>";
	else
		print "<p><a href='?kill=kill'>" . T_("Kill the running process") . "</a> (" . T_("may take up to a few minutes to take effect") .")</p>";

	print process_get_data($p);
}
else
{
	xhtml_head(T_("Import a directory of PDF files"));

	if (isset($_POST['dir']) && isset($_POST['process']))
	{
		$dir = $_POST['dir'];
		import_directory($dir);
	}

	?>	
	<h1><? echo T_("Directory"); ?></h1>
	<form enctype="multipart/form-data" action="?" method="post">
	<p><? echo T_("Enter directory local to the server (eg /mnt/iss/tmp/images)"); ?>: <input name="dir" type="text" value="<? echo realpath("../doc/filled"); ?>"/></p>
	<p><input name='process' id='process' type="submit" value="<? echo T_("Process directory: browser window must remain open"); ?>" /></p>
	<p><input name='watch' id='watch' type="submit" value="<? echo T_("Watch this directory in the background (recommended)"); ?>" /></p>
	</form>
	<?

	print "<h2>" . T_("Outcome of last process run (if any)") . "</h2>";
	print process_get_last_data();
}
xhtml_foot();
?>
