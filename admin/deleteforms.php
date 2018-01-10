<?php
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
xhtml_head(T_("Delete forms"),true,array("../css/table.css"));
$sql = "SELECT q.description, f.fid, f.pfid, f.assigned_vid, f.done, CONCAT('<input type=\'checkbox\' name=\'fid_',f.fid,'\'/>') as deleteme
	FROM forms as f
	LEFT JOIN questionnaires as q on (f.qid = q.qid)
	ORDER BY f.qid,f.pfid"; 
$fs = $db->GetAll($sql);
print "<h1>" . T_("Delete form") . "</h1>";
print "Got " . count($fs) . " form(s)<br>";
//if form not assigned or done, allow for it's deletion
//automatically select the first form within a pfid that hasn't been assigned or verified
//
if (!empty($fs))
{
  $pfid = $fs[0]['pfid'];
  for ($i = 0; $i < count($fs); $i++)
  {
    $r = $fs[$i];
   
    if ($r['done'] == 0 && empty($r['assigned_vid']))
    {
      $fs[$i]['deleteme'] = "<input type='checkbox' checked='checked' name='fid_{$r['fid']}'/>";
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
