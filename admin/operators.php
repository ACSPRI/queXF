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


include_once("../config.inc.php");
include_once("../db.inc.php");
include_once("../functions/functions.xhtml.php");

global $db;

$a = false;

xhtml_head(T_("Add an operator"));

if (isset($_POST['operator']) && isset($_POST['d']))
{
	$operator = $db->qstr($_POST['operator'],get_magic_quotes_gpc());
	$d = $db->qstr($_POST['d'],get_magic_quotes_gpc());
	if ($d == "") $d = $operator;
	if (!empty($_POST['operator']))
	{
		$sql = "INSERT INTO verifiers
			(`vid` ,`description` ,`currentfid` ,`http_username`)
			VALUES (NULL , $d, NULL , $operator);";
	
		if ($db->Execute($sql))
		{
			$a = T_("Added") . ": $operator";	
		}else
		{
			$a = T_("Could not add") . " $operator.". T_("There may already be an operator of this name");
		}
	}
}

if ($a)
{
?>
	<h3><?php echo $a; ?></h3>
<?php
}
?>
<h1><?php echo T_("Add an operator"); ?></h1>
<p><?php echo T_("Adding an operator here will give the user the ability to verify forms once they have assigned a form using the");?> <a href="verifierquestionnaire.php"><?php echo T_("Assign Verifier to Questionnaire"); ?></a> <?php echo T_("tool"); ?>.</p>
<p><?php echo T_("Use this form to enter the username of a user based on your directory security system. For example, if you have secured the base directory of queXF using Apache file based security, enter the usernames of the users here. When the user accesses the verification page, they will uniquely be assigned a form."); ?></p>
<form enctype="multipart/form-data" action="" method="post">
<p><?php echo T_("Enter the username (as in the security system, eg: azammit) of an operator to add:"); ?> <input name="operator" type="text"/></p>
<p><?php echo T_("Enter the name of the operator (eg Adam):"); ?> <input name="d" type="text"/></p>
<p><input type="submit" value="<?php echo T_("Add user"); ?>" /></p>
</form>
</body>
</html>
