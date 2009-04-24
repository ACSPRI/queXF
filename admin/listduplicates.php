<?

/*	Copyright Deakin University 2007,2008,2009
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
include("../functions/functions.database.php");
include("../functions/functions.xhtml.php");

xhtml_head(T_("Listing of duplicate forms"),true,array("../css/table.css"));

$sql = "SELECT q.description, f.fid, f.pfid
	FROM forms as f
	LEFT JOIN questionnaires as q on (f.qid = q.qid)
	WHERE f.pfid
	IN (
		SELECT pfid
		FROM forms
		GROUP BY pfid
		HAVING COUNT( * ) >1
	)
	ORDER BY f.qid,f.pfid"; 

$fs = $db->GetAll($sql);

print "<h1>" . T_("Duplicate form listing") . "</h1><p>" . T_("Forms with the same PFID are duplicates") . "</p>";

xhtml_table($fs,array('description','fid','pfid'),array(T_("Questionnaire"),T_("Formid"),T_("PFID")));


xhtml_foot();

?>
