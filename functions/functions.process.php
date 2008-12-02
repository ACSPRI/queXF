<?
/**
 * Functions related to the backgrounding of processes and the process table in the database
 *
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
 *
 * @author Adam Zammit <adam.zammit@deakin.edu.au>
 * @copyright Deakin University 2007,2008
 * @package queXF
 * @subpackage functions
 * @link http://www.deakin.edu.au/dcarf/ queXF was writen for DCARF - Deakin Computer Assisted Research Facility
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL) Version 2
 * 
 */


include_once(dirname(__FILE__).'/../config.inc.php');
include_once(dirname(__FILE__).'/../db.inc.php');


/**
 * Determine if a process is already running
 *
 * @return bool|int Return false if no process already running, else return the process_id
 */
function is_process_running()
{
	global $db;

	$sql = "SELECT `process_id`
		FROM `process`
		WHERE `stop` IS NULL";

	$rs = $db->GetRow($sql);

	if (!empty($rs))
		return $rs['process_id'];
	
	return false;
}

/**
 * Determine if this process should be killed
 *
 * @return bool Return false if not to be killed, else return true
 */
function is_process_killed($process_id)
{
	global $db;

	$sql = "SELECT `process_id`
		FROM `process`
		WHERE `kill` = 1
		AND `stop` IS NULL
		AND `process_id` = '$process_id'";

	$rs = $db->GetRow($sql);

	if (!empty($rs))
		return true;
	
	return false;
}


/**
 * Start a process
 *
 * @param string $filename The PHP file of the process to run
 * @return bool|int False if we couldnt start a process, else the process id from the process table
 * 
 * @link http://www.djkaty.com/php/fork Cross platform process tutorial (this code adapted from here)
 */
function start_process($filename)
{
	//create a record only if no process already running
	global $db;

	$db->StartTrans();

	$process = is_process_running();

	if ($process == false)
	{
		$sql = "INSERT INTO `process` (`process_id`,`start`,`stop`,`kill`,`data`)
			VALUES (NULL,NOW(),NULL,0,'')";

		$rs = $db->Execute($sql);
		$args = $db->Insert_ID();


		//execute the process in the background - pass the process_id as the first argument
		if (substr(PHP_OS, 0, 3) == 'WIN')
			$proc = popen(WINDOWS_PHP_EXEC . ' "' . $filename . '" ' . $args, 'r');
		else
			$proc = popen(PHP_EXEC . ' ' . $filename . ' ' . $args . ' &', 'r');
	
		pclose($proc);
	}
	else
		$db->FailTrans();


	$db->CompleteTrans();


	if (isset($args))
		return $args;

	return false;	
}


/**
 * Signal to kill a process
 *
 * @param int $process_id The process id
 *
 */
function kill_process($process_id)
{
	global $db;

	$sql = "UPDATE `process`
		SET `kill` = '1'
		WHERE `process_id` = '$process_id'";

	$db->Execute($sql);

}



/**
 * End a process
 *
 * @param int $process_id The process id
 *
 */
function end_process($process_id)
{
	global $db;

	$sql = "UPDATE `process`
		SET `stop` = NOW()
		WHERE `process_id` = '$process_id'";

	$db->Execute($sql);
}


/**
 * Append data to a process
 *
 * @param int $process_id The process id
 * @param string $data Data to append to this process
 *
 */
function process_append_data($process_id,$data)
{
	global $db;

	$data = $db->qstr($data,get_magic_quotes_gpc());

	$sql = "UPDATE `process`
		SET `data` = CONCAT(`data`, $data)
		WHERE `process_id` = '$process_id'";

	$db->Execute($sql);

}


/**
 * Get data from a process
 *
 * @param int $process_id The process id
 * @return string Data from this process
 *
 */
function process_get_data($process_id)
{
	global $db;

	$sql = "SELECT `data`
		FROM `process`
		WHERE `process_id` = '$process_id'";

	$rs = $db->GetRow($sql);

	if (!empty($rs))
		return $rs['data'];

	return "";
}

?>
