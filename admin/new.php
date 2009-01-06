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

include_once("../config.inc.php");
include_once("../db.inc.php");
include('../functions/functions.barcode.php');
include('../functions/functions.image.php');
include('../functions/functions.xhtml.php');


xhtml_head("Add new questionnaire");

/* Add a questionnaire to the database
 *
 */

function newquestionnaire($filename,$desc = "",$type="pngmono"){

	global $db;

	if ($desc == "") $desc = $filename;

	//generate temp file
	$tmp = tempnam(TEMPORARY_DIRECTORY, "FORM");

	//print "Creating PNG files<br/>";

	//use ghostscript to convert to PNG
	exec(GS_BIN . " -sDEVICE=$type -r300 -sOutputFile=$tmp%d.png -dNOPAUSE -dBATCH $filename");
	//print("gs -sDEVICE=pngmono -r300 -sOutputFile=$tmp%d.png -dNOPAUSE -dBATCH $filename");
	
	//print "Creating PNG files<br/>";

	//add to questionnaire table
	//
	//create form entry in DB
	//

	$db->StartTrans();

	$sql = "INSERT INTO questionnaires (qid,description)
		VALUES (NULL,'$desc')";

	$db->Execute($sql);

	$qid = $db->Insert_Id();


	//read pages from 1 to n - stop when n does not exist
	$n = 1;
	$file = $tmp . $n . ".png";
	while (file_exists($file))
	{
		//print "PAGE $n: ";
		//open file
		$data = file_get_contents($file);
		$image = imagecreatefromstring($data);
		$barcode = crop($image,array("tlx" => BARCODE_TLX, "tly" => BARCODE_TLY, "brx" => BARCODE_BRX, "bry" => BARCODE_BRY));

		//imagepng($barcode,"/mnt/iss/tmp/temp$n.png");

		//check for barcode
		$pid = barcode($barcode);
		if ($pid)
		{
			print "<p>BARCODE: $pid</p>";

			//calc offset
			$offset = offset($image,0,0);

			//calc rotation
			$rotation = calcrotate($offset);
	
			//save image to db including offset and rotation
			$sql = "INSERT INTO pages
				(pid,qid,pidentifierbgid,pidentifierval,tlx,tly,trx,try,blx,bly,brx,bry,image,rotation)
				VALUES (NULL,'$qid','1','$pid','{$offset[0]}','{$offset[1]}','{$offset[2]}','{$offset[3]}','{$offset[4]}','{$offset[5]}','{$offset[6]}','{$offset[7]}','" . addslashes($data) . "','$rotation')";
	
			//print $sql;
	
			$db->Execute($sql);

		}
		else
			print "<p>INVALID - IGNORING BLANK PAGE</p>";

		//delete temp file
		unlink($file);

		$n++;
		$file = $tmp . $n . ".png";
		unset($data);
		unset($image);
		unset($barcode);	
	}


	//check if we have created conflicting

	return $db->CompleteTrans();

}

$a = false;

if (isset($_FILES['form']))
{
	$a = true;
	$filename = $_FILES['form']['tmp_name'];
	$desc = $_POST['desc'];

	$r = newquestionnaire($filename,$desc);



}


if ($a)
{
	if ($r)
	{
		print "<h1>SUCCESSFULLY INSERTED NEW QUESTIONNAIRE</h1>";
	}else
	{
		print "<h1>FAILED to insert new questionnaire. Could have conflicting page id's</h1>";
	}


}

?>


<h1>New questionnaire</h1>
<p>You will get the best results if you:</p>
<ul>
<li>Print out the form using the same method that you will for all the printed forms</li>
<li>Scan the (blank) form to a PDF using the same options that you will for the filled forms</li>
<li>Best options for scanning in are:
<ul><li>Monochrome (1 bit)</li>
<li>300DPI Resolution</li></ul>
</li>
</ul>

<form enctype="multipart/form-data" action="" method="post">
	<p><input type="hidden" name="MAX_FILE_SIZE" value="1000000000" /></p>
	<p>Select PDF file to create form from: <input name="form" type="file" /></p>
	<p>Enter description of form: <input name="desc" type="text"/><br/></p>
	<p><input type="submit" value="Upload form" /></p>
</form>

<?

xhtml_foot();

?>
