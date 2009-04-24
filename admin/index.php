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
include ("../lang.inc.php");

xhtml_head(T_("queXF Admin Functions"),true,array("../css/admin.css"),array("../js/link.js"));

?>

<div id="menu">
<h1><? echo T_("queXF Admin Functions"); ?></h1>
<ul>
<li><h3><? echo T_("Form setup"); ?></h3>
<ul><li><a href="javascript:link('mainobj','new.php');"><? echo T_("Import a new form from a PDF file"); ?></a></li>
<li><a href="javascript:link('mainobj','delete.php');"><? echo T_("Delete a form (only if no forms yet imported)"); ?></a></li>
<li><a href="javascript:link('mainobj','band.php');"><? echo T_("Band a form"); ?></a></li>
<li><a href="javascript:link('mainobj','bandajax.php');"><? echo T_("Band a form using interactive banding"); ?></a></li>
<li><a href="javascript:link('mainobj','reorder.php');"><? echo T_("Order variables on the form"); ?></a></li></ul></li>
<li><h3><? echo T_("Users"); ?></h3>
<ul><li><a href="javascript:link('mainobj','operators.php');"><? echo T_("Add operators"); ?></a></li>
<li><a href="javascript:link('mainobj','verifierquestionnaire.php');"><? echo T_("Assign forms to operators"); ?></a></li></ul></li>
<li><h3><? echo T_("Importing"); ?></h3>
<ul><li><a href="javascript:link('mainobj','import.directory.php');"><? echo T_("Import a directory of PDF files"); ?></a></li>
<li><a href="javascript:link('mainobj','listfiles.php?status=1');"><? echo T_("Successfully imported files"); ?></a></li>
<li><a href="javascript:link('mainobj','listfiles.php?status=2');"><? echo T_("Failed imported files"); ?></a></li>
<li><a href="javascript:link('mainobj','listduplicates.php');"><? echo T_("Duplicate forms"); ?></a></li>
<li><a href="javascript:link('mainobj','missingpages.php');"><? echo T_("Handle missing pages"); ?></a></li></ul></li>
<li><h3><? echo T_("Output"); ?></h3>
<ul><li><a href="javascript:link('mainobj','output.php');"><? echo T_("Output data/ddi"); ?></a></li></ul></li>
<li><h3><? echo T_("Progress"); ?></h3>
<ul><li><a href="javascript:link('mainobj','progress.php');"><? echo T_("Display progress of form verification"); ?></a></li>
<li><a href="javascript:link('mainobj','performance.php');"><? echo T_("Display performance of verifiers (Completions per hour)"); ?></a></li></ul></li>
<li><h3><? echo T_("Clients"); ?></h3>
<ul><li><a href="javascript:link('mainobj','clients.php');"><? echo T_("Add clients"); ?></a></li>
<li><a href="javascript:link('mainobj','clientquestionnaire.php');"><? echo T_("Assign clients to forms"); ?></a></li></ul></li>
<li><h3><? echo T_("System setup"); ?></h3>
<ul><li><a href="javascript:link('mainobj','pagesetup.php');"><? echo T_("Page setup"); ?></a></li></ul></li>
</ul>
</div>
<div id='main'><object class='embeddedobject' id='mainobj' data='new.php' standby='<? echo T_("Loading panel..."); ?>' type='application/xhtml+xml'><div><? echo T_("Error, try with Firefox"); ?></div></object></div>
<?

xhtml_foot();

?>
