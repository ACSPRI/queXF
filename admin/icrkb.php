<?php

/*	Copyright Australian Consortium for Social and Political Research Incorporated (ACSPRI) 2010
 *	Written by Adam Zammit - adam.zammit@acspri.org.au
 *	For ACSPRI: http://www.acspri.org.au/
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

include_once("../config.inc.php");
include_once("../db.inc.php");
include('../functions/functions.xhtml.php');
include('../functions/functions.import.php');

if (isset($_GET['kb']))
{
	$kb = intval($_GET['kb']);
	include_once("../functions/functions.output.php");
	export_icr($kb);
	die();
}

xhtml_head(T_("Import ICR KB from XML"),true,array("../css/table.css"));

if (isset($_FILES['icrxml']))
{
	$a = true;
	$xmlname = $_FILES['icrxml']['tmp_name'];
	$r =  import_icr(file_get_contents($xmlname));

	if ($a)
	{
		if ($r)
			print "<h2>" . T_("Successfully loaded ICR XML file") . "</h2>";
		else
			print "<h2>" . T_("Failed to load ICR XML file") . "</h2>";
	}
}

print "<h2>" . T_("Import ICR KB from XML") . "</h2>";

$sql = "SELECT CONCAT('<a href=\"?kb=',IFNULL(kb,'KB'),'\">', description,'</a>') as link
	FROM ocrkb";

$rs = $db->GetAll($sql);

?>

<form enctype="multipart/form-data" action="" method="post">
	<p><input type="hidden" name="MAX_FILE_SIZE" value="1000000000" /></p>
	<p><?php echo T_("Select ICR KB XML file"); ?>: <input name="icrxml" type="file" /></p>
	<p><input type="submit" value="<?php echo T_("Upload XML"); ?>" /></p>
</form>

<?php

print "<h2>" . T_("Export ICR KB to XML") . "</h2>";
xhtml_table($rs,array("link"),array(T_("ICR KB")));

xhtml_foot();
?>
