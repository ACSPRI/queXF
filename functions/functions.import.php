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
include_once('functions.barcode.php');
include_once('functions.image.php');


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

	$sql = "INSERT INTO questionnaires (qid,description,sheets)
		VALUES (NULL,'$desc',0)";

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
		
		$images = split_scanning($image);
		unset($image);
		unset($data);

		foreach($images as $image)
		{
			//get the data from the image
			ob_start();
			imagepng($image);
			$data = ob_get_contents();
			ob_end_clean();

			$barcode = crop($image,array("tlx" => BARCODE_TLX, "tly" => BARCODE_TLY, "brx" => BARCODE_BRX, "bry" => BARCODE_BRY));

			//imagepng($barcode,"/mnt/iss/tmp/temp$n.png");

			//check for barcode
			$pid = barcode($barcode,1,BARCODE_LENGTH_PID);
			if ($pid)
			{
				print "<p>" . T_("BARCODE") . ": $pid</p>";
	
				//calc offset
				$offset = offset($image,0,0);

				//check if any edges were not detected
				if (!in_array("",$offset))
				{
					//calc rotation
					$rotation = calcrotate($offset);
		
					//save image to db including offset and rotation
					$sql = "INSERT INTO pages
						(pid,qid,pidentifierbgid,pidentifierval,tlx,tly,trx,try,blx,bly,brx,bry,image,rotation)
						VALUES (NULL,'$qid','1','$pid','{$offset[0]}','{$offset[1]}','{$offset[2]}','{$offset[3]}','{$offset[4]}','{$offset[5]}','{$offset[6]}','{$offset[7]}','" . addslashes($data) . "','$rotation')";
			
					//print $sql;
			
					$db->Execute($sql);

					if ($db->HasFailedTrans()) die($sql);
				}
				else
				{
					$db->FailTrans();
					print "<p>" . T_("FAILED TO IMPORT AS COULD NOT DETECT ALL PAGE EDGES FOR PAGE") . ":$n</p>";
					break 2;
				}
	
			}
			else
				print "<p>" . T_("INVALID - IGNORING BLANK PAGE") . "</p>";

			unset($data);
			unset($image);
			unset($barcode);
		}
	
		//delete temp file
		unlink($file);

		$n++;
		$file = $tmp . $n . ".png";
		unset($images);
	}


	//check if we have created conflicting

	if ($db->CompleteTrans())
		return $qid;
	else
		return false;

}


/* Process the given page
 *
 *
 *
 */
function processpage($pid,$fid,$image,$transforms)
{
	//fill boxes for this page
	fillboxes($pid,$image,$fid,$transforms);

	//char boxes
	charboxes($pid,$image,$fid,$transforms);

	//number boxes
	numberboxes($pid,$image,$fid,$transforms);

	//barcode boxes
	barcodeboxes($pid,$image,$fid,$transforms);

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




function charboxes($pid,$image,$fid,$transforms)
{
	global $db;


	$boxes = $db->GetAll("
		SELECT b.bid, b.tlx, b.tly, b.brx, b.bry, b.pid, f.filled
		FROM boxes AS b
		JOIN boxgroupstype as bg ON (bg.bgid = b.bgid AND bg.btid = 3)
		LEFT JOIN formboxes AS f ON (f.fid = '$fid' AND f.bid = b.bid)
		WHERE b.pid = '$pid'
		AND f.fid = '$fid'");

	foreach ($boxes as $i)
	{
		if ($i['filled'] < OCR_FILL_MIN && OCR_ENABLED)
		{		
			include_once("functions.ocr.php");
			$ocr = ocr(crop($image,applytransforms($i,$transforms)));
			if (empty($ocr)) $ocr = " ";
		}else
		{
			$ocr = " ";
		}
		//print "{$i['bid']} - :$ocr:<br/>";
		charbox($i['bid'],$fid,$ocr);
	}

}

function numberboxes($pid,$image,$fid,$transforms)
{
	global $db;

	$boxes = $db->GetAll("
		SELECT b.bid, b.tlx, b.tly, b.brx, b.bry, b.pid, f.filled
		FROM boxes AS b
		JOIN boxgroupstype as bg ON (bg.bgid = b.bgid AND bg.btid = 4)
		LEFT JOIN formboxes AS f ON (f.fid = '$fid' AND f.bid = b.bid)
		WHERE b.pid = '$pid'
		AND f.fid = '$fid'");

	foreach ($boxes as $i)
	{
		if ($i['filled'] < OCR_FILL_MIN && OCR_ENABLED)
		{		
			include_once("functions.ocr.php");
			$ocr = ocr(crop($image,applytransforms($i,$transforms)));
			if (empty($ocr)) $ocr = " ";
		}else
		{
			$ocr = " ";
		}
		//print "{$i['bid']} - :$ocr:<br/>";
		charbox($i['bid'],$fid,$ocr);
	}

}


function barcodeboxes($pid,$image,$fid,$transforms)
{
	global $db;

	$boxes = $db->GetAll("
		SELECT b.bid, b.tlx, b.tly, b.brx, b.bry, b.pid
		FROM boxes AS b
		JOIN boxgroupstype as bg ON (bg.bgid = b.bgid AND bg.btid = 5)
		WHERE b.pid = '$pid'");

	foreach ($boxes as $i)
	{
		$barval = barcode(crop($image,applytransforms($i,$transforms)));

		//print "<p>{$i['bid']} - :$barval:</p>";
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



function fillboxes($pid,$image,$fid,$transforms)
{
	global $db;

	$boxes = $db->GetAll("	SELECT b.bid,b.tlx,b.tly,b.brx,b.bry,b.pid 
				FROM boxes as b
				JOIN boxgroupstype AS bg on (bg.bgid = b.bgid AND (bg.btid = 1 or bg.btid = 2 or bg.btid = 3 or bg.btid = 4))
				WHERE b.pid = '$pid'");

	foreach ($boxes as $i)
	{
		$fill = fillratio($image,applytransforms($i,$transforms));
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
function import($filename,$description = false)
{
	global $db;

	set_time_limit(240);
	
	$filehash = sha1_file($filename);

	//First check if this file can be imported
	$sql = "SELECT pfid,allowanother
		FROM processforms
		WHERE filehash = '$filehash'
		OR filepath = " . $db->qstr($filename);

	$pf = $db->GetAll($sql);

	$pfid = false;

	if (count($pf) >= 1)
	{
		if ($pf[0]['allowanother'] == 1) //update record instead of creating new one
			$pfid = $pf[0]['pfid'];
		else
			return false; //this form has already been processed	
	}
	

	//Import the file
	print "<p>" . T_("Importing") . ": $filename</p>";



	if (!$description) $description = $filename;



	//START TRANSACTION:
	// Don't use "StartTrans and CompleteTrans"
	// as we want to use it only for stopping the form committing half way
	// not monitoring all SQL statements for errors

	$db->BeginTrans();

	//count of missing pages
	$missingpagecount = 0;

	//generate temp file
	$tmp = tempnam(TEMPORARY_DIRECTORY, "FORM");
	
	//use ghostscript to convert to individual PNG pages
	exec(GS_BIN . " -sDEVICE=pngmono -r300 -sOutputFile=$tmp%d.png -dNOPAUSE -dBATCH $filename");

	//$qid = 1;

	$qid = "";
	$fid = "";	

	//find the qid
	$n = 1;

	$file = $tmp . $n . ".png";
	while (file_exists($file))
	{
		print "<p>" . T_("Finding qid") . "...</p>";

		//open file
		$data = file_get_contents($file);
		$image = imagecreatefromstring($data);
		unset($data);

		$images = split_scanning($image);

		foreach($images as $image)
		{
			$barcode = crop($image,array("tlx" => BARCODE_TLX, "tly" => BARCODE_TLY, "brx" => BARCODE_BRX, "bry" => BARCODE_BRY));

			//check for barcode
			$pid = barcode($barcode,1,BARCODE_LENGTH_PID);
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
					break 2;
				}
			}
	
			unset($image);
			unset($barcode);
		}
		
		unset($images);

		$n++;
		$file = $tmp . $n . ".png";	
	}


	if ($qid != "")
	{
		print "<p>" . T_("Got qid") . ": $qid...</p>";

		//create form entry in DB
		$sql = "INSERT INTO forms (fid,qid,description)
			VALUES (NULL,'$qid','$description')";

		$db->Execute($sql);

		$fid = $db->Insert_Id();
	


		//process each page
		$n = 1;
		$file = $tmp . $n . ".png";
		while (file_exists($file))
		{
			//open file
			$data = file_get_contents($file);
			$image = imagecreatefromstring($data);

			$images = split_scanning($image);
			unset($data);
			unset($image);

			foreach($images as $image)
			{
				//get the data from the image
				ob_start();
				imagepng($image);
				$data = ob_get_contents();
				ob_end_clean();

				//check for barcode
				$barcode = crop($image,array("tlx" => BARCODE_TLX, "tly" => BARCODE_TLY, "brx" => BARCODE_BRX, "bry" => BARCODE_BRY));
				$pid = barcode($barcode,1,BARCODE_LENGTH_PID);
				if ($pid)
				{
					print "<p>" . T_("Processing pid") . ": $pid...</p>";
	
					//get the page id from the page table
					$sql = "SELECT * FROM pages
						WHERE pidentifierval = '$pid'
						AND qid = '$qid'";
	
					$page = $db->GetRow($sql);
	
					if (empty($page))
					{
						print "<p>" . T_("Pid not identified for this page, inserting into missing pages...") . "</p>";
	
						//store in missing pages table
						$sql = "INSERT INTO missingpages
							(mpid,fid,image)
							VALUES (NULL,'$fid','" . addslashes($data) . "')";
			
						$db->Execute($sql);

						$missingpagecount++;
					}
					else
					{
						if ($page['store'] == 1)
						{
		
							//calc offset
							//$offset = offset($image,$page,1);
			
							//calc transforms
							$transforms = detecttransforms($image,$page);
	
							//save image to db including offset
							$sql = "INSERT INTO formpages
								(fid,pid,filename,image";
							
							foreach($transforms as $key => $val)
								$sql .= ",$key";
	
							$sql .=	")
								VALUES ('$fid','{$page["pid"]}','','" . addslashes($data) . "'";
	
							foreach($transforms as $key => $val)
								$sql .= ",'$val'";
	
							$sql .=	")";
					
							$db->Execute($sql);
						}
			
						if ($page['process'] == 1)
						{		
							//process variables on this page
							processpage($page["pid"],$fid,$image,$transforms);
						}
					}
				}
				else
				{
					if(BLANK_PAGE_DETECTION && is_blank_page($image))
					{
						print "<p>". T_("Blank page: ignoring") . "</p>";
						//let this page dissolve into the ether
					}
					else
					{
						print "<p>". T_("Could not get pid, inserting into missing pages...") . "</p>";
	
						//store in missing pages table
						$sql = "INSERT INTO missingpages
							(mpid,fid,image)
							VALUES (NULL,'$fid','" . addslashes($data) . "')";
			
						$db->Execute($sql);
						$missingpagecount++;
					}
				}
	
				unset($data);
				unset($image);
				unset($barcode);
			}	
			$n++;
			$file = $tmp . $n . ".png";	

			//unset data
			unset($images);
		}

		//Update or insert record in to processforms log database
		if ($pfid == false)
		{
			//insert a new record as no existing for this form
			$sql = "INSERT INTO processforms (pfid,filepath,filehash,date,status,allowanother)
				VALUES (NULL,'$filename','$filehash',NOW(),1,0)";

			$db->Execute($sql);

			$pfid = $db->Insert_ID();
		}
		else
		{	
			//update exisiting record
			$sql = "UPDATE processforms
				SET date = NOW(),
				filepath = '$filename',
				filehash = '$filehash',
				status = 1,
				allowanother = 0
				WHERE pfid = '$pfid'";

			$db->Execute($sql);
		}

		//Update form table with pfid
		$sql = "UPDATE forms
			SET pfid = '$pfid'
			WHERE fid = '$fid'";

		$db->Execute($sql);
	}
	else
	{
		//form could not be identified...
		//do nothing?
		print "<p>" . T_("Could not get qid...") . "</p>";
	
		//Update or insert record in to processforms log database
		if ($pfid == false)
		{
			//insert a new record as no existing for this form
			$sql = "INSERT INTO processforms (pfid,filepath,filehash,date,status,allowanother)
				VALUES (NULL,'$filename','$filehash',NOW(),2,0)";

			$db->Execute($sql);
		}
		else
		{	
			//update exisiting record
			$sql = "UPDATE processforms
				SET date = NOW(),
				filepath = '$filename',
				filehash = '$filehash',
				status = 2,
				allowanother = 0
				WHERE pfid = '$pfid'";

			$db->Execute($sql);
		}
	}


	//Delete temporary pages
	$n = 1;
	$file = $tmp . $n . ".png";
	while (file_exists($file))
	{
		//delete temp file
		unlink($file);

		$n++;
		$file = $tmp . $n . ".png";	
	}


	//If only one page is missing, and one page in the missing pages database,
	//assume this is the missing page and process it.
	if (isset($fid))
	{
		$sql = "SELECT mpid, mp.image as mpimage, p.*
			FROM forms AS f, pages AS p
			LEFT JOIN formpages AS fp ON (fp.fid = '$fid' and fp.pid = p.pid )
			LEFT JOIN missingpages as mp ON (mp.fid = '$fid')
			WHERE f.fid = '$fid'
			AND p.qid = f.qid
			AND fp.pid IS NULL
			AND mp.image is NOT NULL";

		$rs = $db->GetAll($sql);

		if (count($rs) == 1)
		{
			//There is one page in the missing database and one page missing from the form
			$row = $rs[0];
		
			print "<p>" . T_("Automatically processing the 1 missing page for this form - assuming pid:"). " {$row['pid']} - {$row['pidentifierval']}</p>";
			
			$mpid = $row['mpid'];
			$image = imagecreatefromstring($row['mpimage']);

			if ($row['store'] == 1)
			{
				//calc transforms
				$transforms = detecttransforms($image,$row);

				//save image to db including offset
				$sql = "INSERT INTO formpages
					(fid,pid,filename,image";
						
				foreach($transforms as $key => $val)
					$sql .= ",$key";
					$sql .=	")
					VALUES ('$fid','{$row["pid"]}','','" . addslashes($row['mpimage']) . "'";

				foreach($transforms as $key => $val)
					$sql .= ",'$val'";
					$sql .=	")";

				$db->Execute($sql);
			}
			if ($row['process'] == 1)
			{		
				//process variables on this page
				processpage($row["pid"],$fid,$image,$transforms);
			}

			$sql = "DELETE 
				FROM missingpages
				WHERE mpid = '$mpid'";

			$db->Execute($sql);
		}
	
		//if all pages have been entered and dected, and there are missing pages - delete them
		if ($missingpagecount > 0)
		{
			$sql = "SELECT count(*) AS c
				FROM forms AS f, pages AS p
				LEFT JOIN formpages AS fp ON ( fp.fid = '$fid' AND fp.pid = p.pid )
				WHERE f.fid = '$fid'
				AND p.qid = f.qid
				AND fp.pid IS NULL";

			$rs = $db->GetRow($sql);

			if (isset($rs['c']) && $rs['c'] == 0)
			{
				//there are missing pages in the mp table, but no missing pages in the form table... 
				$sql = "DELETE 
					FROM missingpages
					WHERE fid = '$fid'";

				$db->Execute($sql);

				print "<p>" . T_("Deleting missing pages as all form page slots filled") . "</p>";
			}
		}
	}




	//complete transaction
	$db->CommitTrans();

	return true;
}


/**
 * Import banding information for a form
 * 
 * @param string $xml The queXF Banding XML file produced using queXMLPDF
 * @param int $qid The questionnaire id
 * @param bool $erase Whether to erase previous banding on this form
 * 
 * @return bool True if successful otherwise false (including if already banded)
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-09-21
 * @link http://quexml.sourceforge.net/
 */
function import_bandingxml($xml,$qid,$erase = false)
{
	global $db;

	$db->StartTrans();

	if ($erase)
	{
		$sql = "SELECT pid 
			FROM pages 
			WHERE qid = '$qid'";

		$pages = $db->GetAll($sql);	
	
		foreach($pages as $p)
		{
			$pid = $p['pid'];

			$sql = "DELETE FROM boxes 
				WHERE pid = '$pid'";

			$db->Execute($sql);


			$sql = "DELETE FROM boxgroupstype
				WHERE pid = '$pid'";
		
			$db->Execute($sql);
		}

		$sql = "DELETE FROM sections
			WHERE qid = '$qid'";

		$db->Execute($sql);
	}


	$b = new SimpleXMLElement($xml);

	foreach ($b->questionnaire as $q)
	{
		$id = current($q->id);
		$sections = array();
		foreach ($q->section as $s)
		{
			$sid = current($s['id']);
			$label = $db->qstr(current($s->label));
			$title = $db->qstr(current($s->title));

			$sql = "INSERT INTO sections (sid,qid,description,title)
				VALUES (NULL,'$qid',$label,$title)";

			$db->Execute($sql);

			$ssid = $db->Insert_Id();

			//Keep track of queXF's copy of the sid based on the XML sid
			$sections[$sid] = $ssid;
		}

		foreach($q->page as $p)
		{
			$id = current($p->id);
			
			//Get the queXF page id given this qid and pidentifierval
			$sql = "SELECT pid
				FROM pages
				WHERE qid = '$qid'
				ANd pidentifierval LIKE '$id'";

			$rs = $db->GetRow($sql);
		
			$pid = "";

			if (empty($rs))
			{
				$db->FailTrans();
				break 2;
			}		
			else
				$pid = $rs['pid'];

			//Don't update the page location information...

			foreach ($p->boxgroup as $bg)
			{
				$width = intval($bg->width);
				$type = intval($bg->type);
				$varname = $db->qstr($bg->varname);
				$sortorder = intval($bg->sortorder);
				$label = $db->qstr($bg->label);
				$gs = current($bg->groupsection);
				$sid = $sections[$gs['idref']];

				$sql = "INSERT INTO boxgroupstype (bgid,btid,width,pid,varname,sortorder,label,sid)
					VALUES (NULL,$type,$width,$pid,$varname,$sortorder,$label,$sid)";

				$db->Execute($sql);

				$bgid = $db->Insert_Id();
				
				foreach ($bg->box as $b)
				{
					$tlx = intval($b->tlx);
					$tly = intval($b->tly);
					$brx = intval($b->brx);
					$bry = intval($b->bry);
					$value = $db->qstr($b->value);
					$label = $db->qstr($b->label);

					$sql = "INSERT INTO boxes (bid,tlx,tly,brx,bry,pid,bgid,value,label)
						VALUES (NULL,$tlx,$tly,$brx,$bry,$pid,$bgid,$value,$label)";

					$db->Execute($sql);
				}

			}

		}
	}	
	return $db->CompleteTrans();
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
					//print "<p>$file</p>";
			                $r = import("$dir/$file");
					if ($r == false)
						print "<p>" . T_("File already in database") . "</p>";
					 //unlink($file);
					 //rename("$dir/$file","$dir/$file.done");
				}
			}
		}
	
		closedir($handle);
	
	}

}



?>
