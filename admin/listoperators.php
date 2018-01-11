<?php

/*	Copyright Australian Consortium for Social and Political Research Inc. 2018
 *	Written by Adam Zammit - adam.zammit@acspri.org.au
 *	For ACSPRI: https://www.acspri.org.au
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
include_once("../functions/functions.xhtml.php");

global $db;

$a = false;

xhtml_head(T_("List operators"));

if (isset($_POST['operatorid']) && isset($_POST['d']))
{
	$operatorid = $db->qstr($_POST['operatorid'],get_magic_quotes_gpc());
	$operator = $db->qstr($_POST['operator'],get_magic_quotes_gpc());
	$d = $db->qstr($_POST['d'],get_magic_quotes_gpc());
	if (!empty($_POST['operatorid']))
    {
      if (isset($_POST['password']) && empty($_POST['password'])) {
        $a = T_("Password cannot be blank"); 
      } else {
        if (!empty($_POST['operator']) && stripos($_POST['operator'],' ') === false) {
	        $oop = $db->GetOne("SELECT http_username FROM verifiers WHERE vid = $operatorid");
	
			$sql = "UPDATE verifiers SET `description` = $d, `http_username` = $operator WHERE vid = $operatorid";
		
			if ($db->Execute($sql))
	        {
	            if (HTPASSWD_PATH !== false && HTGROUP_PATH !== false) {
	    	        //Get password and add it to the configured htpassword
	    	        include_once("../functions/functions.htpasswd.php");
	    	        $htp = New Htpasswd(HTPASSWD_PATH);
	    	        $htg = New Htgroup(HTGROUP_PATH);
	    	
		            //old operator
	    	        $htp->deleteUser($oop);
	    	        $htg->deleteUserFromGroup($oop,HTGROUP_VERIFIER);
	    	        if ($operatorid != 1) {
		              $htg->deleteUserFromGroup($oop,HTGROUP_ADMIN);
	    	   	    }
	    	        $htp->addUser($_POST['operator'],$_POST['password']);
	    	        $htg->addUserToGroup($_POST['operator'],HTGROUP_VERIFIER);
	    	        if (isset($_POST['s'])) {
	    	          $htg->addUserToGroup($_POST['operator'],HTGROUP_ADMIN);
	                }
				}
	 			$a = T_("Updated") . ": $operator";	
			} else {
				$a = T_("Could not update") . " $operator.". T_("There may already be an operator of this name");
			}
		} else {
			$a= T_("The username must not be empty or contain a space");
		}
      }
    }
}

if ($a)
{
?>
	<h3><?php echo $a; ?></h3>
<?php
}

if (isset($_GET['operatorid'])) {

$operatorid = intval($_GET['operatorid']);

$sql = "SELECT * FROM verifiers where vid = $operatorid";

$rs = $db->GetRow($sql);

?>
<h1><?php echo T_("Update an operator"); ?></h1>
<form enctype="multipart/form-data" action="" method="post">
<p><?php echo T_("Enter the username (as in the security system, eg: azammit) of an operator to add:"); ?> <input name="operator" type="text" value="<?php echo $rs['http_username']; ?>"/></p>
<?php if (HTPASSWD_PATH !== false) {?>
  <p><?php echo T_("Enter the password to set:"); ?> <input name="password" type="text"/></p>
  <p><?php echo T_("Is this operator an administrator?"); ?> <input name="s" type="checkbox"/></p>
<?php }?>
<p><?php echo T_("Enter the name of the operator (eg Adam):"); ?> <input name="d" type="text" value="<?php echo $rs['description']; ?>"/></p>
<p><input type="hidden" name="operatorid" value="<?php echo $operatorid; ?>" /></p>
<p><input type="submit" value="<?php echo T_("Update user"); ?>" /></p>
</form>

<?php } else {

$sql = "SELECT * FROM verifiers";

$rs = $db->GetAll($sql);

foreach($rs as $r) {
  print "<p>Edit: <a href='?operatorid={$r['vid']}'>{$r['description']}</a></p>";

}


}?>


</body>
</html>
