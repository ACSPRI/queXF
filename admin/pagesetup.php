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
include("../functions/functions.xhtml.php");
include("../functions/functions.image.php");


if (isset($_FILES['form']))
{
	$a = true;
	$filename = $_FILES['form']['tmp_name'];
	$type = "pngmono";

	//generate temp file
	$tmp = tempnam(TEMPORARY_DIRECTORY, "FORM");

	//use ghostscript to convert to PNG
	exec(GS_BIN . " -sDEVICE=$type -r300 -sOutputFile=$tmp%d.png -dNOPAUSE -dBATCH $filename");
}


function definetomap($zoom)
{
	$tb = array('t','b');
	$lr = array('l','r');
	$vh = array('vert','hori');
	$el = array('tlx','tly','brx','bry');

	foreach($tb as $a)
		foreach($lr as $b)
			foreach($vh as $c)					
			{
				$vname = "$a$b" . "_" . $c ."_";
				$tlx = constant(strtoupper($vname . "tlx"));
				$tly = constant(strtoupper($vname . "tly"));
				$brx = constant(strtoupper($vname . "brx"));
				$bry = constant(strtoupper($vname . "bry"));					

				print "<div id='$vname' style='position: absolute; top:" . $tly / $zoom . "px; left: " . $tlx / $zoom . "px; width:" . ($brx-$tlx) / $zoom . "px; height:" . ($bry-$tly) / $zoom . "px; background-color: green; opacity: 0.6;' class='drsElement'><div class='drsMoveHandle'>" . $vname . "</div></div>";
			}
					

	$vname = "barcode_";

	$tlx = constant(strtoupper($vname . "tlx"));
	$tly = constant(strtoupper($vname . "tly"));
	$brx = constant(strtoupper($vname . "brx"));
	$bry = constant(strtoupper($vname . "bry"));					
					
	print "<div id='$vname'  style='position: absolute; top:" . $tly / $zoom. "px; left: " . $tlx / $zoom. "px; width:" . ($brx-$tlx)/ $zoom. "px; height:" . ($bry-$tly)/ $zoom. "px; background-color: brown; opacity: 0.6;' class='drsElement'><div class='drsMoveHandle'>" . $vname . "</div></div>";

}

xhtml_head(T_("Set page layout"),true,array("../css/dragresize.css","../css/pagesetup.css"),array("../js/prototype-1.6.0.2.js","../js/dragresize.js","../js/pagelayout.js"));

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

	print "<div id='configarea'><p>" . T_("Configuration settings (copy and paste in to config.inc.php)") . "</p><div id='pagesize'><div>define('PAGE_WIDTH',$fwidth);</div><div>define('PAGE_HEIGHT',$fheight);</div></div><div id='config'></div></div>";


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

		definetomap($zoom);

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

		print "<script type='text/javascript'>
//<![CDATA[
// Using DragResize is simple!
// You first declare a new DragResize() object, passing its own name and an object
// whose keys constitute optional parameters/settings:

var dragresize = new DragResize('dragresize',
 { minWidth: 20, minHeight: 20, minLeft: 0, minTop: 0, maxLeft: $w, maxTop: $h });

// Optional settings/properties of the DragResize object are:
//  enabled: Toggle whether the object is active.
//  handles[]: An array of drag handles to use (see the .JS file).
//  minWidth, minHeight: Minimum size to which elements are resized (in pixels).
//  minLeft, maxLeft, minTop, maxTop: Bounding box (in pixels).

// Next, you must define two functions, isElement and isHandle. These are passed
// a given DOM element, and must 'return true' if the element in question is a
// draggable element or draggable handle. Here, I'm checking for the CSS classname
// of the elements, but you have have any combination of conditions you like:

dragresize.isElement = function(elm)
{
 if (elm.className && elm.className.indexOf('drsElement') > -1) return true;
};
dragresize.isHandle = function(elm)
{
 if (elm.className && elm.className.indexOf('drsMoveHandle') > -1) return true;
};


function getUrlVars()
{
var vars = [], hash;
var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
 
for(var i = 0; i < hashes.length; i++)
{
hash = hashes[i].split('=');
vars.push(hash[0]);
vars[hash[0]] = hash[1];
}
 
return vars;
}


// You can define optional functions that are called as elements are dragged/resized.
// Some are passed true if the source event was a resize, or false if it's a drag.
// The focus/blur events are called as handles are added/removed from an object,
// and the others are called as users drag, move and release the object's handles.
// You might use these to examine the properties of the DragResize object to sync
// other page elements, etc.

dragresize.ondragfocus = function() {};
dragresize.ondragstart = function(isResize) { };
dragresize.ondragmove = function(isResize) {  };
dragresize.ondragend = function(isResize) {
	var l = $$('div .drsElement');
	var s = '';
	v = getUrlVars();
	var z = v['zoom'];
	for (var i=0; i < l.length; i++)
	{
		s += '<div>define(\''
		s += l[i].id.toUpperCase();
		s += 'TLX\','
		s += parseInt(l[i].style.left.replace('px','')) * z;
		s += ');</div>'

		s += '<div>define(\''
		s += l[i].id.toUpperCase();
		s += 'TLY\','
		s += parseInt(l[i].style.top.replace('px','')) * z;
		s += ');</div>'

		s += '<div>define(\''
		s += l[i].id.toUpperCase();
		s += 'BRX\','
		s += (parseInt(l[i].style.width.replace('px','')) + parseInt(l[i].style.left.replace('px',''))) * z;
		s += ');</div>'

		s += '<div>define(\''
		s += l[i].id.toUpperCase();
		s += 'BRY\','
		s += (parseInt(l[i].style.height.replace('px','')) + parseInt(l[i].style.top.replace('px',''))) * z;
		s += ');</div>'			

	}

	$('config').update(s);

};
dragresize.ondragblur = function() { };



// Finally, you must apply() your DragResize object to a DOM node; all children of this
// node will then be made draggable. Here, I'm applying to the entire document.
dragresize.apply(document);
//]]>
</script>";
	}
}
else
{
	//form to upload a document
?>
<h1><? echo T_("Page setup"); ?></h1>
<p><? echo T_("You will get the best results if you:"); ?></p>
<ul>
<li><? echo T_("Print out the form using the same method that you will for all the printed forms"); ?></li>
<li><? echo T_("Scan the form to a PDF using the same options that you will for the filled forms"); ?></li>
<li><? echo T_("Best options for scanning in are:"); ?>
<ul><li><? echo T_("Monochrome (1 bit)"); ?></li>
<li><? echo T_("300DPI Resolution"); ?></li></ul>
</li>
</ul>

<form enctype="multipart/form-data" action="" method="post">
	<p><input type="hidden" name="MAX_FILE_SIZE" value="1000000000" /></p>
	<p><? echo T_("Select PDF file to upload:"); ?><input name="form" type="file" /></p>
	<p><input type="submit" value="<? echo T_("Upload form"); ?>"/></p>
</form>

<?

}


print "</div>";
xhtml_foot();

?>
