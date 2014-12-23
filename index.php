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

<?php
xhtml_foot();

//display list of jobs
//display totals for work done

$sql = "
SELECT f.assigned_vid as vid, v.description, v.fid, TIME_TO_SEC( TIMEDIFF( completed, assigned ) ) AS secondstaken, DATE( assigned ) AS dateassigned, f.qid, q.description
FROM verifiers AS v, forms AS f, questionnaires AS q
WHERE w.vid = v.vid
AND w.fid = f.fid
AND f.qid = q.qid
ORDER BY w.completed
";






?>
