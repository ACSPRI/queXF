<?php

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

if (isset($_POST['submit']))
{
  foreach($_POST as $key => $val)
  {
    if (substr($key,0,4) == "fid_")
    {
      $fid = intval(substr($key,4));

      $sql = "DELETE FROM formboxes
              WHERE fid = '$fid'";

      $db->Execute($sql);

      $sql = "DELETE FROM formboxverifychar
              WHERE fid = '$fid' AND vid = 0";

      $db->Execute($sql);

      $sql = "DELETE FROM formboxverifytext
              WHERE fid = '$fid' AND vid = 0";
      
      $db->Execute($sql);

      $sql = "DELETE FROM formpages
              WHERE fid = '$fid'";

      $db->Execute($sql);

      $sql = "DELETE FROM forms
              WHERE fid = '$fid'";

      $db->Execute($sql);
    }
  }
}

xhtml_head(T_("Listing of duplicate forms"),true,array("../css/table.css"));

$sql = "SELECT q.description, f.fid, f.pfid, f.assigned_vid, f.done, CASE WHEN f.assigned_vid IS NULL AND f.done = 0 THEN CONCAT('<input type=\'checkbox\' name=\'fid_',f.fid,'\'/>') ELSE '" . T_("Already verified") . "' END as deleteme
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

//if form not assigned or done, allow for it's deletion
//automatically select the first form within a pfid that hasn't been assigned or verified
//

if (!empty($fs))
{
  $pfid = $fs[0]['pfid'];
  $nvcount = 0;

  for ($i = 0; $i < count($fs); $i++)
  {
    $r = $fs[$i];
    
    if ($pfid != $r['pfid'])
      $nvcount = 0;
   
    if ($r['done'] == 0 && empty($r['assigned_vid']) &&  $nvcount == 0)
    {
      $fs[$i]['deleteme'] = "<input type='checkbox' checked='checked' name='fid_{$r['fid']}'/>";
      $nvcount++;
    }
    
    $pfid = $r['pfid'];
  }

}

print "<form action='' method='post'>";
xhtml_table($fs,array('description','fid','pfid','deleteme'),array(T_("Questionnaire"),T_("Formid"),T_("PFID"),T_("Delete")));
print "<input type='submit' id='submit' name='submit' value='" . T_("Delete selected forms") . "'/>";
print "</form>";

xhtml_foot();

?>
