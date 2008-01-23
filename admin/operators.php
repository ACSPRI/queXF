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

global $db;

$a = false;

if (isset($_POST['operator']))
{
	$operator = $db->qstr($_POST['operator'],get_magic_quotes_gpc());
	if (!empty($_POST['operator']))
	{
		$sql = "INSERT INTO verifiers
			(`vid` ,`description` ,`currentfid` ,`http_username`)
			VALUES (NULL , $operator, NULL , $operator);";
	
		if ($db->Execute($sql))
		{
			$a = "Added: $operator";	
		}else
		{
			$a = "Could not add $operator. There may already be an operator of this name";
		}
	}
}


?>

<html>
<head>
<title>Add an operator</title>
</head>
<body>
<? 
if ($a)
{
?>
	<h3><? echo $a; ?></h3>
<?
}
?>
<h1>Add an operator</h1>
<p>Adding an operator here will give the user the ability to verify forms once they have assigned a form using the <a href="verifierquestionnaire.php">Assign Verifier to Questionnaire</a> tool.</p>
<p>Use this form to enter the username of a user based on your directory security system. For example, if you have secured the base directory of queXF using Apache file based security, enter the usernames of the users here. When the user accesses the verification page, they will uniquely be assigned a form.</p>
<form enctype="multipart/form-data" action="" method="post">
	Enter the username of an operator to add: <input name="operator" type="text"/><br/>
	<input type="submit" value="Add user" />
</form>

</body>
</html>

