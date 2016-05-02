<?php

/*	Copyright Australian Consortium for Social and Political Research Incorporated (ACSPRI) 2012
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
include_once("../functions/functions.xhtml.php");
include_once("../functions/functions.image.php");
include_once("../functions/functions.import.php");
include_once("../functions/functions.barcode.php");


if (isset($_FILES['form']))
{
	$a = true;
	$filename = $_FILES['form']['tmp_name'];
	$type = "pnggray";

	//generate temp file
	$tmp = tempnam(TEMPORARY_DIRECTORY, "FORM");

	//use ghostscript to convert to PNG
  exec(GS_BIN . " -sDEVICE=$type -r300 -sOutputFile=\"$tmp\"%d.png -dNOPAUSE -dBATCH \"$filename\"");
}


function definetomap($zoom,$pid,$filename)
{
	$tb = array('t','b');
	$lr = array('l','r');
	$vh = array('vert','hori');
	$el = array('tlx','tly','brx','bry');

  $image = imagecreatefrompng($filename . $pid . ".png");	
  //convert to monochrome
  $image = convertmono($image);
  imagepng($image,$filename . $pid .".png");

	$width = imagesx($image);
	$height = imagesy($image);
	$page = defaultpage($width - 1,$height - 1);
        $offset = offset($image,false,0,$page);

	//draw lines of corner edges
	$vert = true;
	$linewidth = 8;
	$lc = 0;
	foreach($offset as $coord)
	{
		if ($vert == true)
		{
			$top = 0;
			if ($lc > 3) $top = ($height / $zoom) - (($height / 4) / $zoom);
			//drawing a vertical line so use $coord as $x
			print "<div style='position: absolute; top:". $top ."px; left:". ($coord / $zoom) ."px; width:". ($linewidth / $zoom) ."px; height:". (($height / 4) / $zoom) . "px; background-color: blue;'></div>";
			$vert = false;	
		}
		else
		{
			//drawing a horizontal line so use $coord as $y
			$left = 0;
			if ($lc == 3 || $lc == 7) $left = ($width / $zoom) - (($width / 4) / $zoom);
			print "<div style='position: absolute; top:". ($coord/$zoom) ."px; left:". ($left) ."px; width:". (($width / 4) / $zoom) ."px; height:". ($linewidth / $zoom) . "px; background-color: blue;'></div>";


			$vert = true;
		}	
		$lc++;
	}


        foreach($tb as $a)
                foreach($lr as $b)
                        foreach($vh as $c)
                        {
                                $vname = "$a$b" . "_" . $c ."_";
                                $tlx = $page[strtoupper($vname . "tlx")];
                                $tly = $page[strtoupper($vname . "tly")];
                                $brx = $page[strtoupper($vname . "brx")];
                                $bry = $page[strtoupper($vname . "bry")];

                                print "<div id='$vname' style='position: absolute; top:" . $tly / $zoom . "px; left: " . $tlx / $zoom . "px; width:" . ($brx-$tlx) / $zoom . "px; height:" . ($bry-$tly) / $zoom . "px; background-color: green; opacity: 0.6;' class='drsElement'><div class='drsMoveHandle'>" . $vname . "</div></div>";
                        }



	$btlx = floor(BARCODE_TLX_PORTION * $width);
	if ($btlx <= 0) $btlx = 1;

	$btly = floor(BARCODE_TLY_PORTION * $height);
	if ($btly <= 0) $btly = 1;

	$bbrx = floor(BARCODE_BRX_PORTION * $width);
	if ($bbrx <= 0) $bbrx = 1;

	$bbry = floor(BARCODE_BRY_PORTION * $height);
	if ($bbry <= 0) $bbry = 1;

	$barcodeimage = crop($image,array("tlx" => $btlx, "tly" => $btly, "brx" => $bbrx, "bry" => $bbry));

	$barcode = barcode($barcodeimage);

	if ($barcode === false) 
		$barcode = T_("NO BARCODE DETECTED");
	else
	{
		if (strlen($barcode) != BARCODE_LENGTH_PID)
			$barcode = T_("Detected but not BARCODE_LENGTH_PID length") . ": " . $barcode;
		else
			$barcode = T_("Detected") . ": " . $barcode;
	}
	

	print "<div id='barcodebox'  style='position: absolute; top:" . $btly / $zoom. "px; left: " . $btlx / $zoom. "px; width:" . ($bbrx-$btlx)/ $zoom. "px; height:" . ($bbry-$btly)/ $zoom. "px; background-color: brown; opacity: 0.6;' class='drsElement'><div class='drsMoveHandle'>$barcode</div></div>";


  $btlx = floor(BARCODE_TLX_PORTION2 * $width);
	if ($btlx <= 0) $btlx = 1;

	$btly = floor(BARCODE_TLY_PORTION2 * $height);
	if ($btly <= 0) $btly = 1;

	$bbrx = floor(BARCODE_BRX_PORTION2 * $width);
	if ($bbrx <= 0) $bbrx = 1;

	$bbry = floor(BARCODE_BRY_PORTION2 * $height);
	if ($bbry <= 0) $bbry = 1;

	$barcodeimage = crop($image,array("tlx" => $btlx, "tly" => $btly, "brx" => $bbrx, "bry" => $bbry));

	$barcode = barcode($barcodeimage);

	if ($barcode === false) 
		$barcode = T_("NO BARCODE DETECTED");
	else
	{
		if (strlen($barcode) != BARCODE_LENGTH_PID2)
			$barcode = T_("Detected but not BARCODE_LENGTH_PID2 length") . ": " . $barcode;
		else
			$barcode = T_("Detected") . ": " . $barcode;
	}
	

	print "<div id='barcodebox'  style='position: absolute; top:" . $btly / $zoom. "px; left: " . $btlx / $zoom. "px; width:" . ($bbrx-$btlx)/ $zoom. "px; height:" . ($bbry-$btly)/ $zoom. "px; background-color: brown; opacity: 0.6;' class='drsElement'><div class='drsMoveHandle'>$barcode</div></div>";

}

xhtml_head(T_("Page test"),true,array("../css/dragresize.css","../css/pagetest.css"),array("../js/prototype-1.6.0.2.js","../js/pagelayout.js"));

print "<div id='content'>";

if (isset($tmp)) $_GET['filename'] = $tmp;

if (isset($_GET['filename']))
{
	$zoom = BAND_DEFAULT_ZOOM;
	if (isset($_GET['zoom'])) $zoom = intval($_GET['zoom']);

	$zoomup = $zoom - 1; if ($zoomup < 1) $zoomup = 1;
	$zoomdown = $zoom + 1;

	$n = 1;
	$file = $_GET['filename'] . $n . ".png";

	if (SPLIT_SCANNING)
	{
		while(file_exists($file))
		{
			$n++;
			$file = $_GET['filename'] . $n . ".png";
		}

		$filecount =  $n - 1;

		$n = 1;
		
		while($n <= $filecount)
		{
			$file = $_GET['filename'] . $n . ".png";
			//split all the files
			$data = file_get_contents($file);
      $image = imagecreatefromstring($data);
      $image = convertmono($image);
			$images = split_scanning($image);
			if (count($images) == 2)
			{		
				imagepng($images[0],$_GET['filename'] . $n . ".png");
				imagepng($images[1],$_GET['filename'] . ($n + $filecount) . ".png");
			}
			$n++;
		}
	}

	$n = 1;
	$file = $_GET['filename'] . $n . ".png";
	$pages = array();
	while (file_exists($file))
	{
		$page = array();
		$page['pid'] = $n;
		$page['filename'] = $_GET['filename'];
		$pages[] = $page;
		if ($n == 1) //get the page size
		{
			list($fwidth, $fheight, $ftype, $fattr) = getimagesize($file);
		}
		$n++;
		$file = $_GET['filename'] . $n . ".png";
	}

	$p = 1;
	foreach($pages as $page)
	{
		$pid = 0;
		if (isset($_GET['pid'])) $pid = intval($_GET['pid']);
		if ($page['pid'] == $pid)
		{	
			print " <span style=\"font-size:150%;\">$p</span> ";
		}else
		{
			print " <a href=\"?pid={$page['pid']}&amp;zoom=$zoom";
			if (isset($_GET['filename'])) print "&amp;filename=" . $page['filename'];
			print "\">$p</a> ";
		}
		$p++;
	}
	print "<br/>";


	if (isset($_GET['pid']))
	{
		$pid = intval($_GET['pid']);

		print " <a href=\"?zoom=$zoomup&amp;pid=$pid";
		if(isset($_GET['filename'])) 
			print "&amp;filename=" . $_GET['filename'];
		print "\">" . T_("Increase zoom") . "</a> <a href=\"?zoom=$zoomdown&amp;pid=$pid";
		if(isset($_GET['filename'])) 
			print "&amp;filename=" . $_GET['filename'];
		print "\">" . T_("Decrease zoom") . "</a><br/> ";


		//show image with no coords selected
		print "<div id=\"imagearea\" style=\"position:relative;\">";
		print "<div id=\"imageboxes\">";

		definetomap($zoom,$pid,$_GET['filename']);

		print "</div>";

			

		print "<div id=\"imageimage\">";
		$w = floor($fwidth / $zoom);
		$h = floor($fheight / $zoom);
		print "<img id=\"sampleid\" src=\"../showpage.php?";
		if(isset($_GET['filename'])) 
			print "filename=" . $_GET['filename'] . "$pid.png";
		else
			print "pid=$pid";
		print "\" style=\"border:0; z-index:0;\" width=\"$w\" height=\"$h\" alt=\"page $pid image\"/>";
		print "</div>";
		print "</div>";

		print "<div id='configarea'><h3>" . T_("Page test") . "</h3>";
		print "<ul><li>" . T_("Confirm that there is a detected unique barcode for each page and that the barcode fits inside the pink box. If not, edit the BARCODE_PORTION variables in the configuration file") . "</li>";
		print "<li>" . T_("Confirm that the barcode is of length") . " " . BARCODE_LENGTH_PID . ". " . T_("Otherwise edit the BARCODE_LENGTH_PID variable in the configuration file") . "</li>";
		print "<li>" . T_("Confirm the corner edges fit within the green/brown boxes and that the blue lines appear to be drawn over the corner edges (for each 4 corners, both horizonal and vertical lines. If not, edit the PAGE_GUIDE_X_PORTION, HORI_WIDTH and VERT_WIDTH variables in the configuration file") . "</li>";
	


	}
}
else
{
	//form to upload a document
?>

<h1><?php echo T_("Page test"); ?></h1>
<h2><?php echo T_("When using banding XML:");?></h2>
<p><?php echo  T_("You must import the original PDF and banding XML file (not a scanned version)"); ?></p>
<h2><?php echo T_("When manually banding:");?></h2>
<p><?php echo T_("You will get the best results if you:"); ?></p>
<ul>
<li><?php echo T_("Print out the form using the same method that you will for all the printed forms"); ?></li>
<li><?php echo T_("Scan the form to a PDF using the same options that you will for the filled forms"); ?></li>
<li><?php echo T_("Best options for scanning in are:"); ?>
<ul><li><?php echo T_("Monochrome (1 bit)"); ?></li>
<li><?php echo T_("300DPI Resolution"); ?></li></ul>
</li>
</ul>

<form enctype="multipart/form-data" action="" method="post">
	<p><input type="hidden" name="MAX_FILE_SIZE" value="1000000000" /></p>
	<p><?php echo T_("Select PDF file to upload:"); ?><input name="form" type="file" /></p>
	<p><input type="submit" value="<?php echo T_("Upload form"); ?>"/></p>
</form>

<?php

}


print "</div>";
xhtml_foot();

?>
