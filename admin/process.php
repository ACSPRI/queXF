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


include(dirname(__FILE__) . "/../functions/functions.import.php");
include(dirname(__FILE__) . "/../functions/functions.process.php");
include_once(dirname(__FILE__) . "/../lang.inc.php");

function update_callback($buffer)
{
	global $process_id;

	process_append_data($process_id,$buffer);

	return ""; //empty buffer
}


//get the arguments from the command line (directory to process, and this process_id)
if ($argc != 3) exit();

$dir = $argv[1];
$process_id = $argv[2];

//register an exit function which will tell the database we have ended
register_shutdown_function('end_process',$process_id);

//start a loop importing the directory, sleeping for a while, then checking if the process
//needs to be killed and trying again

ob_start('update_callback',2);

print T_("Processing directory") . ": $dir";

$sleepinterval = 10;

while (!is_process_killed($process_id)) //check if process killed every $sleepinterval
{
	//read directory listing and process one file at a time
	$handle = opendir($dir);
	
	if ($handle) 
	{
		print date(DATE_RFC822) . " " . T_("Watching...");
		while ((false !== ($file = readdir($handle)))) 
		{
			if (is_process_killed($process_id)){break;}
			if ($file != "." && $file != ".." && substr($file,-4) != "done")
			{
				if (substr($file,-3) == "pdf")
				{
			                $r = import("$dir/$file");
					//unlink($file);
					//rename("$dir/$file","$dir/$file.done");
				}
			}
		}
		closedir($handle);
	}
	else
	{
		print T_("Cannot process this directory - check that it is valid and permissions are correct");
		break; //break the loop
	}

	for ($i = 0; $i < PROCESS_SLEEP; $i += $sleepinterval)
	{
		if (is_process_killed($process_id)){break;}
		sleep($sleepinterval);
	}
}

ob_get_contents();
ob_end_clean();

?>
