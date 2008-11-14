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

/**
 * XHTML functions
 */
include ("../functions/functions.xhtml.php");

xhtml_head("queXF Administrative Functions",true,array("../css/admin.css"),array("../js/link.js"));

?>

<div id="menu">
<h1>queXF Admin Functions</h1>
<h3>Setup</h3>
<div><a href="javascript:link('mainobj','new.php');">Import a new form from a PDF file</a></div>
<div><a href="javascript:link('mainobj','delete.php');">Delete a form (only if no forms yet imported)</a></div>
<div><a href="javascript:link('mainobj','band.php');">Band and order variables for a form</a></div>
<div><a href="javascript:link('mainobj','bandajax.php');">Band and order variables for a form (using interactive banding)</a></div>
<h3>Users</h3>
<div><a href="javascript:link('mainobj','operators.php');">Add operators</a></div>
<div><a href="javascript:link('mainobj','verifierquestionnaire.php');">Assign forms to operators</a></div>
<h3>Importing</h3>
<div><a href="javascript:link('mainobj','import.directory.php');">Import a directory of PDF files</a></div>
<div><a href="javascript:link('mainobj','missingpages.php');">Handle missing pages</a></div>
<h3>Output</h3>
<div><a href="javascript:link('mainobj','output.php');">Output data/ddi</a></div>
<h3>Progress</h3>
<div><a href="javascript:link('mainobj','progress.php');">Display progress of form verification</a></div>
<div><a href="javascript:link('mainobj','performance.php');">Display performance of verifiers (Completions per hour)</a></div>
<h3>Clients</h3>
<div><a href="javascript:link('mainobj','clients.php');">Add clients</a></div>
<div><a href="javascript:link('mainobj','clientquestionnaire.php');">Assign clients to forms</a></div>
</div>
<div id='main'><object class='embeddedobject' id='mainobj' data='new.php' standby='Loading panel...' type='application/xhtml+xml'><div>Error, try with Firefox</div></object></div>
<?

xhtml_foot();

?>
