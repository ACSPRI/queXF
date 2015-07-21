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
include("../functions/functions.xhtml.php");
include("../functions/functions.image.php");
include("../functions/functions.barcode.php");

$tb = array('t','b');
$lr = array('l','r');
$vh = array('vert','hori');
$el = array('tlx','tly','brx','bry');

function definetomap($zoom,$pid,$page)
{
	global $tb,$lr,$vh;

	$image = imagecreatefromstring($page['image']);

        $offset = offset($image,0,0,$page);
	$width = $page['width'];
	$height = $page['height'];

	//draw lines of corner edges
	$vert = true;
	$linewidth = 5;
	$lc = 0;
	foreach($offset as $coord)
	{
		if ($vert == true)
		{
			$top = 0;
			if ($lc > 3) $top = ($height / $zoom) - (($height / 4) / $zoom);
			//drawing a vertical line so use $coord as $x
			print "<div style='position: absolute; top:". $top ."px; left:". ($coord / $zoom) ."px; width:". ($linewidth / $zoom) ."px; height:". (($height / 4) / $zoom) . "px; background-color: blue; opacity: 0.8;'></div>";
			$vert = false;	
		}
		else
		{
			//drawing a horizontal line so use $coord as $y
			$left = 0;
			if ($lc == 3 || $lc == 7) $left = ($width / $zoom) - (($width / 4) / $zoom);
			print "<div style='position: absolute; top:". ($coord/$zoom) ."px; left:". ($left) ."px; width:". (($width / 4) / $zoom) ."px; height:". ($linewidth / $zoom) . "px; background-color: blue; opacity: 0.8;'></div>";


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
					

	//Don't display barcode anymore as it is detected on a system level
	/*
	$vname = "barcode_";

	$tlx = constant(strtoupper($vname . "tlx"));
	$tly = constant(strtoupper($vname . "tly"));
	$brx = constant(strtoupper($vname . "brx"));
	$bry = constant(strtoupper($vname . "bry"));					
			
        $barcodeimage = crop($image,array("tlx" => BARCODE_TLX, "tly" => BARCODE_TLY, "brx" => BARCODE_BRX, "bry" => BARCODE_BRY));
	$barcode = barcode($barcodeimage);

	
	print "<div id='$vname'  style='position: absolute; top:" . $tly / $zoom. "px; left: " . $tlx / $zoom. "px; width:" . ($brx-$tlx)/ $zoom. "px; height:" . ($bry-$tly)/ $zoom. "px; background-color: brown; opacity: 0.6;' class='drsElement'><div class='drsMoveHandle'>" . $vname . "</div>$barcode</div>";
	*/
}

//Update the page border elements in the db
if (isset($_GET['update']))
{
	$qid = intval($_GET['qid']);
	$pid = intval($_GET['pid']);
	
	//Update the page elements given the GET requests
	foreach($tb as $a)
		foreach($lr as $b)
			foreach($vh as $c)					
				foreach ($el as $d)
				{
					$vname = strtoupper("$a$b" . "_" . $c ."_" . $d);
					$val = intval($_GET[$vname]);
				
					$sql = "UPDATE pages
						SET `$vname` = $val
						WHERE qid = '$qid' AND pid = '$pid'";

					$db->Execute($sql);
				}

	//return updated imageboxes
	$sql = "SELECT *
		FROM pages 
		WHERE pid = $pid";

	$page = $db->GetRow($sql);

	$zoom = intval($_GET['zoom']);

	definetomap($zoom,$pid,$page);

	print "<script type='text/javascript'>dragresize.apply(document);</script>";

	die();

}


if (isset($_GET['copy']))
{
	$qid = intval($_GET['qid']);
	$pid = intval($_GET['pid']);

	$sql = "SELECT *
		FROM pages
		WHERE pid = '$pid'";

	$copy = $db->GetRow($sql);

	foreach($tb as $a)
		foreach($lr as $b)
			foreach($vh as $c)					
				foreach ($el as $d)
				{
					$vname = strtoupper("$a$b" . "_" . $c ."_" . $d);
					$val = intval($copy[$vname]);
				
					//update for all pages
					$sql = "UPDATE pages
						SET `$vname` = $val
						WHERE qid = '$qid'
						AND pid != '$pid'";

					$db->Execute($sql);
				}

  $sql = "UPDATE pages SET usepagesetup = '{$copy['usepagesetup']}' WHERE qid = '$qid' and pid != '$pid'";
  $db->Execute($sql);
}


$error = "";

if (isset($_GET['done']))
{
	//Recalculate page edges for all pages

	$qid = intval($_GET['qid']);

	$sql = "SELECT *
		FROM pages
		WHERE qid = '$qid'";

	$pages = $db->GetAll($sql);

	$off = array("tlx","tly","trx","try","blx","bly","brx","bry");

	foreach($pages as $page)
	{
		$image = imagecreatefromstring($page['image']);
	        $offset = offset($image,0,0,$page);

		$pid = $page['pid'];

		$c = 0;

		foreach($offset as $o)
		{
			if (is_null($o))
			{
				//error
				$error += "<div><a href='?pid=$pid&amp;qid=$qid>" . T_("Cannot detect page edge on page") . " $pid</a></div>";
			}
			else
			{
				$sql = "UPDATE pages
					SET `" . $off[$c] . "` = '$o'
					WHERE pid = '$pid'";

				$db->Execute($sql);
			}
			$c++;
		}
		unset($image);
  }

  $usepagesetup = 0;
  if (isset($_GET['enable'])) $usepagesetup = 1;

  $sql = "UPDATE pages SET usepagesetup = '$usepagesetup' WHERE qid = '$qid'";
  $db->Execute($sql);
}



xhtml_head(T_("Set page layout"),true,array("../css/dragresize.css","../css/pagesetup.css"),array("../js/prototype-1.6.0.2.js","../js/dragresize.js","../js/pagelayout.js"));

print "<div id='content'>";

print $error;

if (isset($_GET['qid']))
{
	$qid = intval($_GET['qid']);
	$zoom = BAND_DEFAULT_ZOOM;
	if (isset($_GET['zoom'])) $zoom = intval($_GET['zoom']);

	$zoomup = $zoom - 1; if ($zoomup < 1) $zoomup = 1;
	$zoomdown = $zoom + 1;

	$sql = "SELECT *
		FROM pages
		WHERE qid = $qid";

	$pages = $db->GetAssoc($sql);

	$p = 1;
	foreach($pages as $ppid => $val)
	{
		$pid = 0;
		if (isset($_GET['pid'])) $pid = intval($_GET['pid']);
		if ($ppid == $pid)
		{	
			print " <span style=\"font-size:150%;\">$p</span> ";
		}
		else
		{
			print " <a href=\"?pid={$ppid}&amp;zoom=$zoom";
			print "&amp;qid=" . $qid;
			print "\">$p</a> ";
		}
		$p++;
	}
	print "<br/>";

	//print "<div id='configarea'><p>" . T_("Configuration settings (copy and paste in to config.inc.php)") . "</p><div id='pagesize'><div>define('PAGE_WIDTH',$fwidth);</div><div>define('PAGE_HEIGHT',$fheight);</div></div><div id='config'></div></div>";


	if (isset($_GET['pid']))
	{
		$pid = intval($_GET['pid']);

		print " <a href=\"?zoom=$zoomup&amp;pid=$pid";
		print "&amp;qid=" . $qid;
		print "\">" . T_("Increase zoom") . "</a> <a href=\"?zoom=$zoomdown&amp;pid=$pid";
		print "&amp;qid=" . $qid;
		print "\">" . T_("Decrease zoom") . "</a><br/> ";

		print "<div><a href='?zoom=$zoom&amp;pid=$pid&amp;qid=$qid&amp;copy=copy'>" . T_("Copy settings from this page to all other pages") . "</a><br/></div>";

    $ups = $db->GetOne("SELECT usepagesetup FROM pages WHERE pid = '$pid'");

    if ($ups == 0)
  		print "<div><a href='?zoom=$zoom&amp;pid=$pid&amp;qid=$qid&amp;done=done&amp;enable=enable'>" . T_("Page setup disabled (click to enable)") . "</a><br/></div>";
    else
  		print "<div><a href='?zoom=$zoom&amp;pid=$pid&amp;qid=$qid&amp;done=done'>" . T_("Page setup ENABLED (click to disable)") . "</a><br/></div>";



		//show image with no coords selected
		print "<div id=\"imagearea\" style=\"position:relative;\">";
		print "<div id=\"imageboxes\">";

		definetomap($zoom,$pid,$pages[$pid]);

		print "</div>";
	
		$fwidth = $pages[$pid]['width'];
		$fheight = $pages[$pid]['height'];


		print "<div id=\"imageimage\">";
		$w = floor(($fwidth - 1) / $zoom);
		$h = floor(($fheight - 1) / $zoom);
		print "<img id=\"sampleid\" src=\"../showpage.php?";
		print "pid=$pid";
		print "\" style=\"border:0; z-index:0;\" width=\"$w\" height=\"$h\" alt=\"page $pid image\"/>";
		print "</div>";
		print "</div>";

		print "<div><p>&nbsp;</p></div>";

		print "<script type='text/javascript'>
//<![CDATA[
// Using DragResize is simple!
// You first declare a new DragResize() object, passing its own name and an object
// whose keys constitute optional parameters/settings:

var dragresize = new DragResize('dragresize',
 { minWidth: 20, minHeight: 20, minLeft: 1, minTop: 1, maxLeft: $w, maxTop: $h });

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
	var s = 'update=update&';
	v = getUrlVars();
	var z = v['zoom'];
	s += 'zoom=';
	s += z;
	s += '&';
	var pid = v['pid'];
	var qid = v['qid'];
	for (var i=0; i < l.length; i++)
	{
		s += l[i].id.toUpperCase();
		s += 'TLX='
		s += parseInt(l[i].style.left.replace('px','')) * z;
		s += '&'

		s += l[i].id.toUpperCase();
		s += 'TLY='
		s += parseInt(l[i].style.top.replace('px','')) * z;
		s += '&'

		s += l[i].id.toUpperCase();
		s += 'BRX='
		s += (parseInt(l[i].style.width.replace('px','')) + parseInt(l[i].style.left.replace('px',''))) * z;
		s += '&'

		s += l[i].id.toUpperCase();
		s += 'BRY='
		s += (parseInt(l[i].style.height.replace('px','')) + parseInt(l[i].style.top.replace('px',''))) * z;
		s += '&'			

	}

	s += 'pid=' + pid + '&qid=' + qid;

	s = 'pagesetup.php?' + s;

	new Ajax.Updater('imageboxes', s, {method: 'get'});

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
	//form to choose a questionnaire/form
	$sql = "SELECT qid,description
		FROM questionnaires";
	
	$qs = $db->GetAll($sql);

	foreach($qs as $q)
	{
		print "<a href=\"?qid={$q['qid']}\">". T_("Page setup") . ": {$q['description']}</a>";
		print "<br/>";
	}

}


print "</div>";
xhtml_foot();

?>
