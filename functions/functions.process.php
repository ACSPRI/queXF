<?php
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

/**
 * Configuration file
 */
include_once(dirname(__FILE__).'/../config.inc.php');

/**
 * Database file
 */
include_once(dirname(__FILE__).'/../db.inc.php');


/**
 * Determine if a process is already running
 *
 * @param int $type Defaults to 1 - specify the process type (class) to search for
 * @return bool|int Return false if no process already running, else return the process_id
 */
function is_process_running($type = 1)
{
	global $db;

	$sql = "SELECT `process_id`
		FROM `process`
		WHERE `stop` IS NULL
		AND type = '$type'";

	$rs = $db->GetRow($sql);

	if (!empty($rs))
		return $rs['process_id'];
	
	return false;
}

/**
 * Determine if this process should be killed
 *
 * @param int $process_id The process id 
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
 * @param int $type The type (class) of process (so we can run multiple processes at the same time) defaults to 1
 * @return bool|int False if we couldnt start a process, else the process id from the process table
 * 
 * @link http://www.djkaty.com/php/fork Cross platform process tutorial (this code adapted from here)
 */
function start_process($filename,$type = 1)
{
	//create a record only if no process already running
	global $db;

	$db->StartTrans();

	$process = is_process_running($type);

	$args = 0;

	if ($process == false)
	{
		$sql = "INSERT INTO `process` (`process_id`,`type`,`start`,`stop`,`kill`)
			VALUES (NULL,'$type',NOW(),NULL,0)";

		$rs = $db->Execute($sql);
		$args = $db->Insert_ID();


		//execute the process in the background - pass the process_id as the first argument
		if (substr(PHP_OS, 0, 3) == 'WIN')
			$proc = popen(WINDOWS_PHP_EXEC . ' ' . $filename . ' ' . $args, 'r');
		else
			$proc = popen(PHP_EXEC . ' ' . $filename . ' ' . $args . ' &', 'r');
	
		pclose($proc);
	}
	else
		$db->FailTrans();


	$db->CompleteTrans();


	if ($args != 0)
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

	$sql = "INSERT INTO `process_log` (process_log_id,process_id,datetime,data)
		VALUES (NULL,'$process_id',NOW(),$data)";

	$db->Execute($sql);

}


/**
 * Get data from a process
 *
 * @param int $process_id The process id
 * @return string Data from this process or an empty string if none available
 *
 */
function process_get_data($process_id)
{
	global $db;

	$sql = "SELECT process_log_id,DATE_FORMAT(datetime,'" . DATE_TIME_FORMAT ."') as datetime,data
		FROM `process_log`
		WHERE `process_id` = '$process_id'
		ORDER BY process_log_id DESC
		LIMIT " . PROCESS_LOG_LIMIT;

	$rs = $db->GetAll($sql);

	if (!empty($rs))
		return $rs;	

	return false;
}

/**
 * Get data from the last process run
 *
 * @param int $type The last processes class (type) defaults to 1
 * @return string Data from the last process, or an empty string if not available
 *
 */
function process_get_last_data($type = 1)
{
	global $db;

	$sql = "SELECT process_id
		FROM `process`
		WHERE type = '$type'
		ORDER BY `process_id` DESC
		LIMIT 1";

	$rs = $db->GetRow($sql);

	if (!empty($rs))
		return process_get_data($rs['process_id']);

	return false;
}

?>
