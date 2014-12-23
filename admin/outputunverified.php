<?php

/*	Copyright Australian Consortium for Social and Political Research Incorporated (ACSPRI) 2010
 *	Written by Adam Zammit - adam.zammit@acspri.org.au
 *	For ACSPRI: http://www.acspri.org.au/software/
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
include ("../functions/functions.output.php");
include ("../functions/functions.xhtml.php");

if (isset($_GET['ddi']))
{
	export_ddi(intval($_GET['ddi']));
	exit();
}

if (isset($_GET['data']))
{
	outputdata(intval($_GET['data']),"",true,true,true);
	exit();
}

if (isset($_GET['csvlabel']))
{
	outputdatacsv(intval($_GET['csvlabel']),"",true,true);
	exit();
}

if (isset($_GET['csv']))
{
	outputdatacsv(intval($_GET['csv']),"",false,true);
	exit();
}

if (isset($_GET['banding']))
{
	export_banding(intval($_GET['banding']));
	exit();
}

if (isset($_GET['pspp']))
{
	export_pspp(intval($_GET['pspp']),true);
	exit();
}

xhtml_head(T_("Output unverified data"),true,array("../css/table.css"));

$sql = "SELECT description,
		CONCAT('<a href=\"?data=', qid, '\">" . T_("Data") . "</a>') as data,
		CONCAT('<a href=\"?ddi=', qid, '\">" . T_("DDI") . "</a>') as ddi,
		CONCAT('<a href=\"?csv=', qid, '\">" . T_("CSV") . "</a>') as csv,
		CONCAT('<a href=\"?csvlabel=', qid, '\">" . T_("CSV Labelled") . "</a>') as csvlabel,
		CONCAT('<a href=\"?pspp=', qid, '\">" . T_("PSPP (SPSS)") . "</a>') as pspp,
		CONCAT('<a href=\"?banding=', qid, '\">" . T_("Banding XML") . "</a>') as banding
	FROM questionnaires
	ORDER BY qid DESC";


$qs = $db->GetAll($sql);

print "<h2>" . T_("Warning: the data downloaded from here is unverified") . "</h2>";

xhtml_table($qs, array('description','data','ddi','csv','csvlabel','pspp','banding'),array(T_("Questionnaire"),T_("Data"),T_("DDI"),T_("CSV"),T_("CSV Labelled"), T_("PSPP (SPSS)"), T_("Banding XML")));

xhtml_foot();

?>
