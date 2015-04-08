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


include("../functions/functions.import.php");

if (isset($_FILES['form']))
{
	$filename = $_FILES['form']['tmp_name'];
	$description = $_POST['descr'];
	import($filename,$description);
	exit();
}


?>


<html>
<head>
<title>Import questionnaire</title>
</head>
<body>

<h1>Import</h1>
<form enctype="multipart/form-data" action="" method="post">
	<input type="hidden" name="MAX_FILE_SIZE" value="1000000000" />
	Select PDF file to create form from: <input name="form" type="file" /><br/>
	Form Description: <input name="descr" type="text" /><br/>
	<input type="submit" value="Upload form" />
</form>

</body>

</html>



