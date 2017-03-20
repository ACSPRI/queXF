<?php
/*	Copyright Australian Consortium for Social and Political Research Incorporated 2017
 *	Written by Adam Zammit - adam.zammit@acspri.org.au
 *	For the ACSPRI: https://www.acspri.org.au/
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

include("../functions/functions.xhtml.php");
include("../lang.inc.php");
include("../config.inc.php");

xhtml_head();
if (is_writeable(SCANS_DIRECTORY)) {
?>

  <title><?php echo(T_("Upload forms"));?></title>


<script src="dropzone.min.js"></script>
<link rel="stylesheet" href="dropzone.min.css">
<script type="text/javascript">Dropzone.options.upl = {paramName:'userfile',acceptedFiles:'application/pdf'};</script>

<p><?php echo(T_("Upload your forms here"));?>
</p>

<!-- Change /upload-target to your upload address -->
<form action="upload.php" class="dropzone" id="upl"></form>

<?php
} else {
  print "<p>" . T_("Error, the scans directory is not writeable.") . " " . SCANS_DIRECTORY . "</p>";
}
xhtml_foot();
