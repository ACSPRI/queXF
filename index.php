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

include("functions/functions.xhtml.php");
include("lang.inc.php");

xhtml_head();
?>

<h1><?php echo T_("queXF"); ?></h1>
<p><a href="verifyjs.php"><?php echo T_("Verify"); ?></a></p>
<p><a href="review.php"><?php echo T_("Review a form"); ?></a></p>
<p><a href="./upload"><?php echo T_("Upload scanned forms (PDF)"); ?></a></p>
<p><a href="./admin"><?php echo T_("Administer queXF"); ?></a></p>

<?php
xhtml_foot();
