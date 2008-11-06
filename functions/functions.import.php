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


include_once(dirname(__FILE__).'/../config.inc.php');
include_once(dirname(__FILE__).'/../db.inc.php');
include('functions.barcode.php');
include('functions.image.php');



/* Process the given page
 *
 *
 *
 */
function processpage($pid,$fid,$image,$offset)
{
	//fill boxes for this page
	fillboxes($pid,$image,$fid,$offset[0],$offset[1]);

	//char boxes
	charboxes($pid,$image,$fid,$offset[0],$offset[1]);

	//number boxes
	numberboxes($pid,$image,$fid,$offset[0],$offset[1]);

	//barcode boxes
	barcodeboxes($pid,$image,$fid,$offset[0],$offset[1]);

	//singlechoiceguess
	singlechoiceguess($pid,$fid);

	//multiplechoiceguess
	multiplechoiceguess($pid,$fid);
}


function charbox($bid,$fid,$val)
{
	global $db;
	$q = "NULL";
	if ($val != "")  $q = "'$val'";
	$db->Query("
		INSERT INTO
		formboxverifychar (vid,bid,fid,val) 
		VALUES ('0','$bid','$fid',$q)");
}

function textbox($bid,$fid,$val)
{
	global $db;
	$q = "NULL";
	if ($val != "")  $q = "'$val'";
	$db->Query("
		INSERT INTO
		formboxverifytext (vid,bid,fid,val) 
		VALUES ('0','$bid','$fid',$q)");
}




function charboxes($pid,$image,$fid,$offx,$offy)
{
	global $db;


	$boxes = $db->GetAll("
		SELECT b.bid, b.tlx, b.tly, b.brx, b.bry, b.pid, f.filled
		FROM boxeschar AS b
		LEFT JOIN formboxes AS f ON f.fid = '$fid'
		AND f.bid = b.bid
		WHERE b.pid = '$pid'
		AND f.fid = '$fid'");

	foreach ($boxes as $i)
	{
		if ($i['filled'] < OCR_FILL_MIN && OCR_ENABLED)
		{		
			include_once("functions.ocr.php");
			$ocr = ocr(crop($image,calcoffset($i,$offx,$offy)));
			if (empty($ocr)) $ocr = " ";
		}else
		{
			$ocr = " ";
		}
		//print "{$i['bid']} - :$ocr:<br/>";
		charbox($i['bid'],$fid,$ocr);
	}

}

function numberboxes($pid,$image,$fid,$offx,$offy)
{
	global $db;

	$boxes = $db->GetAll("
		SELECT b.bid, b.tlx, b.tly, b.brx, b.bry, b.pid, f.filled
		FROM boxesnumber AS b
		LEFT JOIN formboxes AS f ON f.fid = '$fid'
		AND f.bid = b.bid
		WHERE b.pid = '$pid'
		AND f.fid = '$fid'");

	foreach ($boxes as $i)
	{
		if ($i['filled'] < OCR_FILL_MIN && OCR_ENABLED)
		{		
			include_once("functions.ocr.php");
			$ocr = ocr(crop($image,calcoffset($i,$offx,$offy)));
			if (empty($ocr)) $ocr = " ";
		}else
		{
			$ocr = " ";
		}
		//print "{$i['bid']} - :$ocr:<br/>";
		charbox($i['bid'],$fid,$ocr);
	}

}


function barcodeboxes($pid,$image,$fid,$offx,$offy)
{
	global $db;

	$boxes = $db->GetAll("
		SELECT b.bid, b.tlx, b.tly, b.brx, b.bry, b.pid
		FROM boxesbarcode AS b
		WHERE b.pid = '$pid'");

	foreach ($boxes as $i)
	{
		$barval = barcode(crop($image,calcoffset($i,$offx,$offy)));

		//print "{$i['bid']} - :$barval:<br/>";
		textbox($i['bid'],$fid,$barval);
	}

}


function boxfilled($bid,$fid,$filled)
{
	global $db;
	$db->Query("
		INSERT INTO
		formboxes (bid,fid,filled) 
		VALUES ('$bid','$fid','$filled')");
}



function fillboxes($pid,$image,$fid,$offx,$offy)
{
	global $db;

	$boxes = $db->GetAll("SELECT bid,tlx,tly,brx,bry,pid FROM boxesfillable WHERE pid = '$pid'");

	foreach ($boxes as $i)
	{
		$fill = fillratio($image,calcoffset($i,$offx,$offy));
	        //print "{$i['bid']} - $fill<br/>";
		boxfilled($i['bid'],$fid,$fill);
	}

}


function singlechoiceguess($pid,$fid)
{
	$minfilled = SINGLE_CHOICE_MIN_FILLED;
	$maxfilled = SINGLE_CHOICE_MAX_FILLED;

	global $db;

	$boxes = $db->GetAll("
		SELECT boxes.bid as bid, boxes.bgid as bgid
		FROM `boxes`
		LEFT JOIN boxgroupstype ON boxgroupstype.btid = 1 and boxes.bgid = boxgroupstype.bgid
		LEFT JOIN formboxes ON formboxes.fid = '$fid'
		AND boxes.bid = formboxes.bid
		WHERE boxes.pid = '$pid'
		AND boxgroupstype.btid = 1
		ORDER BY boxes.bgid ASC
		");

	$bgid = "";

	foreach ($boxes as $i)
	{
		if ($i['bgid'] != $bgid){
			$bgid = $i['bgid'];

			$sql = "SELECT formboxes.bid as bid
				FROM `formboxes` 
				LEFT JOIN boxes ON formboxes.bid = boxes.bid
				LEFT JOIN boxgroupstype on boxes.bgid = boxgroupstype.bgid
			where boxgroupstype.bgid = '$bgid'
			and formboxes.fid = '$fid'
			and formboxes.filled < '$minfilled'
			and formboxes.filled > '$maxfilled'
			ORDER BY formboxes.filled DESC";

			$rs = $db->GetAll($sql);
			$recs = count($rs);

			if ($recs >= 1)
			{
				$Tbid = $rs[0]['bid'];
			}
			else  
			{
				$sql = "SELECT formboxes.bid as bid
				FROM `formboxes` 
				LEFT JOIN boxes ON formboxes.bid = boxes.bid
				LEFT JOIN boxgroupstype on boxes.bgid = boxgroupstype.bgid
			where boxgroupstype.bgid = '$bgid'
			and formboxes.fid = '$fid'
			and formboxes.filled < '$minfilled'
			ORDER BY formboxes.filled DESC";

				$rs = $db->GetAll($sql);
				$recs = count($rs);

				if ($recs >= 1)
				{
					$Tbid = $rs[0]['bid'];
				}else
				{
					$Tbid = "";
				}


			}

		}
		$bid = $i['bid'];
		if ($Tbid == $bid)
		{
			//print "$bid - filled<br/>";
			charbox($bid,$fid,1);
		}
		else
		{
			//print "$bid - empty<br/>";
			charbox($bid,$fid,0);
		}
	}

}



function multiplechoiceguess($pid,$fid)
{
	$minfilled = MULTIPLE_CHOICE_MIN_FILLED;
	$maxfilled = MULTIPLE_CHOICE_MAX_FILLED;

	global $db;

	$boxes = $db->GetAll("
		SELECT boxes.bid as bid, boxes.bgid as bgid, formboxes.filled as filled
		FROM `boxes`
		LEFT JOIN boxgroupstype ON boxgroupstype.btid = 2 and boxes.bgid = boxgroupstype.bgid
		LEFT JOIN formboxes ON formboxes.fid = '$fid'
		AND boxes.bid = formboxes.bid
		WHERE boxes.pid = '$pid'
		AND boxgroupstype.btid = 2
		ORDER BY boxes.bgid ASC
");

	foreach ($boxes as $i)
	{
		$bid = $i['bid'];
		$filled = $i['filled'];
		if ($filled < $minfilled && $filled > $maxfilled)
		{
			//print "multi: $bid - filled<br/>";
			charbox($bid,$fid,1);
		}
		else
		{
			//print "multi: $bid - empty<br/>";
			charbox($bid,$fid,0);
		}
	}

}



/* Import a form given in the file
 *
 *
 *
 */
function import($filename,$description = false){

	set_time_limit(240);

	if (!$description) $description = $filename;

	global $db;


	//START TRANSACTION:
	// Don't use "StartTrans and CompleteTrans"
	// as we want to use it only for stopping the form committing half way
	// not monitoring all SQL statements for errors

	$db->BeginTrans();


	//generate temp file
	$tmp = tempnam(TEMPORARY_DIRECTORY, "FORM");
	
	//use ghostscript to convert to individual PNG pages
	exec(GS_BIN . " -sDEVICE=pngmono -r300 -sOutputFile=$tmp%d.png -dNOPAUSE -dBATCH $filename");

	//$qid = 1;

	$qid = "";
	$fid = "";

	$pages = array();
	
	//read pages from 1 to n - stop when n does not exist
	$n = 1;
	$file = $tmp . $n . ".png";
	while (file_exists($file))
	{
		//open file
		$data = file_get_contents($file);
		$image = imagecreatefromstring($data);
		$pages[] = array($image,$data);

		//print "GOT PAGE: $n<br/>";

		//delete temp file
		unlink($file);

		$n++;
		$file = $tmp . $n . ".png";	
	}

	
	//find the qid
	foreach ($pages as $imagearray)
	{
		print "<p>Finding qid...</p>";

		$image = $imagearray[0];

		$barcode = crop($image,array("tlx" => BARCODE_TLX, "tly" => BARCODE_TLY, "brx" => BARCODE_BRX, "bry" => BARCODE_BRY));

		//check for barcode
		$pid = barcode($barcode);
		if ($pid)
		{
			//print "BARCODE: $pid<br/>";

			//get the page id from the page table
			$sql = "SELECT qid FROM pages
				WHERE pidentifierval = '$pid'";

			$page = $db->GetRow($sql);

			if (isset($page['qid']))
			{
				$qid = $page['qid'];
				break;
			}
		}
	}


	if ($qid != "")
	{
		print "<p>Got qid: $qid...</p>";

		//create form entry in DB
		$sql = "INSERT INTO forms (fid,qid,description)
			VALUES (NULL,'$qid','$description')";

		$db->Execute($sql);

		$fid = $db->Insert_Id();
	


		//process each page
		foreach ($pages as $imagearray)
		{
			$image = $imagearray[0];
			$data = $imagearray[1];

			//check for barcode
			$barcode = crop($image,array("tlx" => BARCODE_TLX, "tly" => BARCODE_TLY, "brx" => BARCODE_BRX, "bry" => BARCODE_BRY));
			$pid = barcode($barcode);
			if ($pid)
			{
				print "<p>Processing pid: $pid...</p>";

				//get the page id from the page table
				$sql = "SELECT * FROM pages
					WHERE pidentifierval = '$pid'";

				$page = $db->GetRow($sql);

				if ($page['store'] == 1)
				{

					//calc offset
					$offset = offset($image,$page,1);
	
					//save image to db including offset
					$sql = "INSERT INTO formpages
						(fid,pid,filename,image,offx,offy)
						VALUES ('$fid','{$page["pid"]}','','" . addslashes($data) . "','{$offset[0]}','{$offset[1]}')";
		
					$db->Execute($sql);
				}
	
				if ($page['process'] == 1)
				{		
					//process variables on this page
					processpage($page["pid"],$fid,$image,$offset);
				}
			}
			else
			{
				if(BLANK_PAGE_DETECTION && is_blank_page($image))
				{
					print "<p>Blank page: ignoring</p>";
					//let this page dissolve into the ether
				}
				else
				{
					print "<p>Could not get pid, inserting into missing pages...</p>";

					//store in missing pages table
					$sql = "INSERT INTO missingpages
						(mpid,fid,image)
						VALUES (NULL,'$fid','" . addslashes($data) . "')";
		
					$db->Execute($sql);
				}
			}
		}
	}
	else
	{
		//form could not be identified...
		//do nothing?
		print "<p>Could not get qid...</p>";
	}

	//complete transaction
	$db->CommitTrans();
}


/**
 * Import a directory of files and rename them once done
 *
 * @param string $dir The directory to look for files to import
 */
function import_directory($dir)
{

	if ($handle = opendir($dir)) {
	
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && substr($file,-4) != "done")
			{
				if (substr($file,-3) == "pdf")
				{
					print "<p>$file</p>";
			                 import("$dir/$file");
					 //unlink($file);
					 rename("$dir/$file","$dir/$file.done");
				}
			}
		}
	
		closedir($handle);
	
	}

}



?>
