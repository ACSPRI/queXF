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


include(dirname(__FILE__) . "/../functions/functions.import.php");
include(dirname(__FILE__) . "/../functions/functions.process.php");

function update_callback($buffer)
{
	global $process_id;

	process_append_data($process_id,"<div>".$buffer."</div>");

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

print "Processing directory: $dir";

$sleeptime = PROCESS_SLEEP;
$sleepinterval = 10;

while (!is_process_killed($process_id)) //check if process killed every $sleepinterval
{
	if ($sleeptime >= PROCESS_SLEEP)
	{
		//read directory listing and process one file at a time
		$handle = opendir($dir);
	
		if ($handle) 
		{
			print date(DATE_RFC822) . " Watching...";
			$filedone = 0;
			while ((false !== ($file = readdir($handle))) && $filedone == 0 ) 
			{
				if ($file != "." && $file != ".." && substr($file,-4) != "done")
				{
					if (substr($file,-3) == "pdf")
						{
				                $r = import("$dir/$file");
						//unlink($file);
						//rename("$dir/$file","$dir/$file.done");
						if ($r != false)
							$filedone = 1;
					}
				}
			}
			closedir($handle);
	
			$sleeptime = 0; //reset sleep counter
		}
		else
		{
			print "Cannot process this directory - check that it is valid and permissions are correct";
			break; //break the loop
		}
	}
	else
	{
		sleep($sleepinterval);
		$sleeptime += $sleepinterval;
	}
}

ob_get_contents();
ob_end_clean();

?>
