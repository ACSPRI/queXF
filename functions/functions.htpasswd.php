<?php 
/**
 * htpasswd/htgroup functions for authentication integration
 *
 *
 *	This file is part of queXS
 *	
 *	queXS is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *	
 *	queXS is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *	
 *	You should have received a copy of the GNU General Public License
 *	along with queXS; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @copyright Australian Consortium for Social and Political Research Incorporated (ACSPRI) 2007,2008,2009,2010,2011
 * @package queXS
 * @subpackage functions
 * @link http://www.acspri.org.au/software queXS was writen for ACSPRI
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL) Version 2
 * 
 */

/**
 * Configuration file
 */
include_once(dirname(__FILE__).'/../config.inc.php');

/**
 * Class Htpasswd from:
 * @link http://www.kavoir.com/backyard/showthread.php?28-Use-PHP-to-generate-edit-and-update-.htpasswd-and-.htgroup-authentication-files
 */
class Htpasswd {
    
    private $file = '';
    
    public function __construct($file) {
        if (file_exists($file)) {
            $this -> file = $file;
        } else {
            return false;
        }
    }
    
    private function write($pairs = array()) {
        $str = '';
        foreach ($pairs as $username => $password) {
            $str .= "$username:{SHA}$password\n";
        }
        file_put_contents($this -> file, $str);
    }
    
    private function read() {
        $pairs = array();
        $fh = fopen($this -> file, 'r');
        while (!feof($fh)) {
            $pair_str = str_replace("\n", '', fgets($fh));
            $pair_array = explode(':{SHA}', $pair_str);
            if (count($pair_array) == 2) {
                $pairs[$pair_array[0]] = $pair_array[1];
            }
        }
        return $pairs;
    }
    
    public function addUser($username = '', $clear_password = '') {
        if (!empty($username) && !empty($clear_password)) {
            $all = $this -> read();
            if (!array_key_exists($username, $all)) {
                $all[$username] = $this -> getHash($clear_password);
                $this -> write($all);
            }
        } else {
            return false;
        }
    }
    
    public function deleteUser($username = '') {
        $all = $this -> read();
        if (array_key_exists($username, $all)) {
            unset($all[$username]);
            $this -> write($all);
        } else {
            return false;
        }
    }
    
    public function doesUserExist($username = '') {
        $all = $this -> read();
        if (array_key_exists($username, $all)) {
            return true;
        } else {
            return false;
        }
    }
    
    private function getHash($clear_password = '') {
        if (!empty($clear_password)) {
            return base64_encode(sha1($clear_password, true));
        } else {
            return false;
        }
    }
    
}  

/**
 * Class Htgroup from:
 * @link http://www.kavoir.com/backyard/showthread.php?28-Use-PHP-to-generate-edit-and-update-.htpasswd-and-.htgroup-authentication-files
 */
class Htgroup {
    
    private $file = '';
    
    public function __construct($file) {
        if (file_exists($file)) {
            $this -> file = $file;
        } else {
            return false;
        }
    }
    
    private function write($groups = array()) {
        $str = '';
        foreach ($groups as $group => $users) {
            $users_str = '';
            foreach ($users as $user) {
                if (!empty($users_str)) {
                    $users_str .= ' ';
                }
                $users_str .= $user;
            }
            $str .= "$group: $users_str\n";
        }
        file_put_contents($this -> file, $str);
    }
    
    private function read() {
        $groups = array();
        $groups_str = file($this -> file, FILE_IGNORE_NEW_LINES);
        foreach ($groups_str as $group_str) {
            if (!empty($group_str)) {
                $group_str_array = explode(': ', $group_str);
                if (count($group_str_array) == 2) {
                    $users_array = explode(' ', $group_str_array[1]);
                    $groups[$group_str_array[0]] = $users_array;
                }
            }
        }
        return $groups;
    }
    
    public function addUserToGroup($username = '', $group = '') {
        if (!empty($username) && !empty($group)) {
            $all = $this -> read();
            if (isset($all[$group])) {
                if (!in_array($username, $all[$group])) {
                    $all[$group][] = $username;
                }
            } else {
                $all[$group][] = $username;
            }
            $this -> write($all);
        } else {
            return false;
        }
    }
    
    public function deleteUserFromGroup($username = '', $group = '') {
        $all = $this -> read();
        if (array_key_exists($group, $all)) {
            $user_index = array_search($username, $all[$group]);
            if ($user_index !== false) {
                unset($all[$group][$user_index]);
                if (count($all[$group]) == 0) {
                    unset($all[$group]);
                }
                $this -> write($all);
            }
        } else {
            return false;
        }
    }

}  

?>
