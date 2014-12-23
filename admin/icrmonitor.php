<?php

/*	Copyright Australian Consortium for Social and Political Research Incorporated (ACSPRI) 2011
 *	Written by Adam Zammit - adam.zammit@acspri.org.au
 *	For ACSPRI: http://www.acspri.org.au
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


include("../functions/functions.import.php");
include("../functions/functions.xhtml.php");
include("../functions/functions.process.php");

xhtml_head(T_("Monitor ICR process"),true,array("../css/table.css"),false,false);

if (isset($_GET['p']))
{
	$p = intval($_GET['p']);
	
	if (isset($_GET['kill']))
		kill_process($p);

	if (isset($_GET['force_kill']))
		end_process($p);

			
	print "<h1>" . T_("Process") . " $p</h1>";

	if (is_process_killed($p))
	{
		print "<h3>" . T_("Kill signal sent: Please wait..." ) . "</h3>";
		print "<p><a href='?force_kill'>" . T_("Mark the proces as killed (i.e. when the server is rebooted)"). "</a></p>";
	}
	else
		print "<p><a href='?kill=kill'>" . T_("Kill the running process") . "</a> (" . T_("may take up to a few minutes to take effect") .")</p>";

        $d = process_get_data($p);
        if ($d !== false)
        {
                xhtml_table($d,array('process_log_id','datetime','data'),array(T_("Log id"), T_("Date"), T_("Log entry")));
        }

}
else
{
	global $db;

	$sql = "SELECT process_id
		FROM process
		WHERE stop IS NULL
		AND type = 2";
	
	$rs = $db->GetAll($sql);

	if (!empty($rs))
	{
		foreach($rs as $r)
		{
			print "<p><a href='?p={$r['process_id']}'>" . T_("Process") . " {$r['process_id']} " . T_("running...") . "</a></p>";
		}
	}
	else
	{	
		print "<h2>" . T_("No process running") . "</h2>";
	}
	
	print "<h2>" . T_("Outcome of last process run (if any)") . "</h2>";
	
	$d = process_get_last_data(2);
	if ($d !== false)
        {
                xhtml_table($d,array('process_log_id','datetime','data'),array(T_("Log id"), T_("Date"), T_("Log entry")));
        }

}
xhtml_foot();
?>
