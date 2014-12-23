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

xhtml_head(T_("Update banding from XML"));

if (isset($_FILES['bandingxml']) && isset($_POST['qid']) && !empty($_POST['qid']))
{
	$qid = intval($_POST['qid']);
	$a = true;
	$xmlname = $_FILES['bandingxml']['tmp_name'];
	$r =  import_bandingxml(file_get_contents($xmlname),$qid,true);

	if ($a)
	{
		if ($r)
			print "<h2>" . T_("Successfully loaded banding XML file") . "</h2>";
		else
			print "<h2>" . T_("Failed to load banding XML file") . "</h2>";
	}
}

print "<h1>" . T_("Update banding from XML") . "</h1>";
print "<p>" . T_("WARNING: All previous banding will be erased") . "</p>";

$sql = "SELECT description,qid as value, '' AS selected
	FROM questionnaires";

$rs = $db->GetAll($sql);

?>

<form enctype="multipart/form-data" action="" method="post">
	<p><input type="hidden" name="MAX_FILE_SIZE" value="1000000000" /></p>
	<p><?php echo T_("Select banding XML file"); ?>: <input name="bandingxml" type="file" /></p>
	<p><?php echo T_("Select questionnaire"); ?>: <?php display_chooser($rs, 'qid', 'qid', true, false, false, false,false);  ?><br/></p>
	<p><input type="submit" value="<?php echo T_("Upload XML"); ?>" /></p>
</form>

<?php

xhtml_foot();
?>
