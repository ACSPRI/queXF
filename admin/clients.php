<?php
/**
 * Create a client and link to a webserver username for authentication
 *
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
 *
 * @author Adam Zammit <adam.zammit@deakin.edu.au>
 * @copyright Deakin University 2007,2008
 * @package queXF
 * @subpackage admin
 * @link http://www.deakin.edu.au/dcarf/ queXF was writen for DCARF - Deakin Computer Assisted Research Facility
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL) Version 2
 * 
 *
 *
 */

/**
 * Configuration file
 */
include ("../config.inc.php");

/**
 * Database file
 */
include ("../db.inc.php");

/**
 * XHTML functions
 */
include ("../functions/functions.xhtml.php");


global $db;

$a = false;

if (isset($_POST['client']))
{
	$client = $db->qstr($_POST['client'],get_magic_quotes_gpc());
	$description = $db->qstr($_POST['description'],get_magic_quotes_gpc());
	
	if (!empty($_POST['client']))
	{
		$sql = "INSERT INTO clients
			(`cid` ,`username` ,`description`)
			VALUES (NULL , $client, $description);";
	
		if ($db->Execute($sql))
			$a = (T_("Added") . ": $client");	
		else
			$a = T_("Could not add") . " " . $client . ". " . T_("There may already be an client of this name");
	}
}


xhtml_head(T_("Add a client"));

if ($a)
{
?>
	<h3><?php echo $a; ?></h3>
<?php
}
?>
<h1><?php echo T_("Add a client"); ?></h1>
<p><?php echo T_("Adding a client here will allow them to access project information in the client subdirectory. You can assign a client to a particular project using the"); ?> <a href="clientquestionnaire.php"><?php echo T_("Assign client to Form"); ?></a> <?php echo T_("tool."); ?></p>
<p><?php echo T_("Use this form to enter the username of a user based on your directory security system. For example, if you have secured the base directory of queXF using Apache file based security, enter the usernames of the users here."); ?></p>
<form enctype="multipart/form-data" action="" method="post">
	<p><?php echo T_("Enter the username of a client to add:"); ?> <input name="client" type="text"/></p>
	<p><?php echo T_("Enter the description of the client to add:"); ?> <input name="description" type="text"/></p>
	<p><input type="submit" value="<?php echo T_("Add user"); ?>" /></p>
</form>

<?php

xhtml_foot();

?>
