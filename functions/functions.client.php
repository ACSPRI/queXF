<?php
/**
 * Client functions
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
 * Return the current client id based on PHP_AUTH_USER
 *
 * @return bool|int False if none otherwise the client id
 *
 */
function get_client_id()
{
	global $db;

	$sql = "SELECT cid
		FROM clients
		WHERE username = '{$_SERVER['PHP_AUTH_USER']}'";

	$o = $db->GetRow($sql);

	if (empty($o)) 	return false;

	return $o['cid'];

}

/**
 * Return a list of questionnaires assigned to this client
 *
 * @param int $client_id Client id
 * @return bool|array False if nothing assigned otherwise an array of questionnaire assigned
 *
 */
function get_client_questionnaire($client_id)
{
	global $db;

	$sql = "SELECT qid
		FROM clientquestionnaire
		WHERE cid = '$client_id'";

	$o = $db->GetAll($sql);

	if (empty($o)) 	return false;

	return $o;


}


