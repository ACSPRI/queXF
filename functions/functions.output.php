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


include_once(dirname(__FILE__).'/../config.inc.php');
include_once(dirname(__FILE__).'/../db.inc.php');

set_time_limit(600);


function csv($fields = array(), $delimiter = ',', $enclosure = '"')
{
    $str = '';
    $escape_char = '\\';
    foreach ($fields as $value)
    {
      if ($value !== null && (
          strpos($value, $delimiter) !== false ||
          strpos($value, $enclosure) !== false ||
          strpos($value, "\n") !== false ||
          strpos($value, "\r") !== false ||
          strpos($value, "\t") !== false ||
          strpos($value, ' ') !== false))
      {
        $str2 = $enclosure;
        $escaped = 0;
        $len = strlen($value);
        for ($i=0;$i<$len;$i++)
        {
          if ($value[$i] == $escape_char)
            $escaped = 1;
          else if (!$escaped && $value[$i] == $enclosure)
            $str2 .= $enclosure;
          else
            $escaped = 0;
          $str2 .= $value[$i];
        }
        $str2 .= $enclosure;
        $str .= $str2.$delimiter;
      }
      else
        $str .= $value.$delimiter;
    }
    $str = substr($str,0,-1);
    $str .= "\n";
    return $str;
}

/**
 * Upload a data record from the given fid to the Limesurvey JSON RPC
 * 
 * @param int $fid The formid to upload
 * 
 * @return
 * @author Benedickt Wurz <benedikt.wurz@gmx.net>
 * @since  2017-02-24
 */

function uploadrpcJson($fid) {

  global $db;

  //get url, qid
  $sql = "SELECT q.rpc_server_url,q.rpc_username,q.rpc_password,f.qid,q.limesurvey_sid
           FROM forms as f, questionnaires as q
           WHERE f.fid = '$fid'
           AND f.qid = q.qid";

  $rs = $db->GetRow($sql);

  if (!empty($rs['rpc_server_url'])) {
   $url = $rs['rpc_server_url'];
   $qid = $rs['qid'];
   $surveyid = $rs['limesurvey_sid'];

   include_once(dirname(__FILE__) . "/../include/JsonRPCClient.php");

   //formid not recognised by limesurvey
   unset($assoc['formid']);
   unset($assoc['rpc_id']);
   unset($assoc['filename']);

   // create Session ID
   $client = new  \org\jsonrpcphp\JsonRPCClient($url, false);

   $sessionKey = $client->get_session_key($rs['rpc_username'],$rs['rpc_password']);


   // get list from all questions
   $questionResult = $client->list_questions($sessionKey, $surveyid);

   $qidmain = array();

   foreach ($questionResult as $data) {
     if ($data['parent_qid'] == 0) {
       $qidmain[$data['qid']] = $data;
       //$qid[$data['qid']]=
     }
   }

   //var_dump($qidmain);
   //var_dump($questionResult);
   //return true;

   // build translate array
   foreach ($questionResult as $data) {
     if ($data['parent_qid'] != 0) {
      $pqid = $data['parent_qid'];

      if ($qidmain[$pqid]['type'] == 'M') {
        $que[$qidmain[$pqid]['title'] . '_' . $data['title']] = $data['sid'] . 'X' . $data['gid'] . 'X' . $data['parent_qid'] . $data['title'];
      } else {
        $que[$qidmain[$pqid]['title'] . '_' . $data['title']] = $data['sid'] . 'X' . $qidmain[$pqid]['gid'] . 'X' . $qidmain[$pqid]['qid'] . $data['title'];
      }
     } else {
        $que[$data['title']] = $data['sid'] . 'X' . $data['gid'] . 'X' . $data['qid'];
     }
    }
   
    //var_dump($que);
    // get data
    list($head, $data) = outputdatacsv($qid, $fid, false, false, true);
    $assoc = array();

    //var_dump($head);

    for ($i = 0; $i < count($head); $i++) {
         //concat if same variable name
         if (isset($assoc[$head[$i]])) {
             $assoc[$head[$i]] .= $data[$i];
             $data[$que[$head[$i]]] .= $data[$i];
         } else {
             $assoc[$head[$i]] = $data[$i];
             $data[$que[$head[$i]]] = $data[$i];
         }
     }

     //make sure token won't interfere with normal operation of questionnaire
     $data['token'] = "queXF-" . $fid;

     //var_dump($data);
     // send data to lime
     $result = $client->add_response($sessionKey, $surveyid, $data);

     #return true;
     // // error handling
     if (is_numeric($result)) {
         //echo 'OK: got '.$result;
         //update forms table with rpc_id
         $sql = "UPDATE forms
                 SET rpc_id = '" . $result . "'
                 WHERE fid = '$fid'";

         $db->Execute($sql);
     } else {
         var_dump($result);
         echo T_("Json RPC Error: ") . $result;
     }
     // exit session
     $client->release_session_key($sessionKey);
  }
}


/**
 * Upload a data record from the given fid to the RPC server
 * Currently will work with queXS 1.5.2
 * 
 * @param int $fid The formid to upload
 * 
 * @return
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2011-11-04
 */
function uploadrpc($fid)
{
	global $db;
	
	//get url, qid
	$sql = "SELECT q.rpc_server_url,q.rpc_username,q.rpc_password,f.qid,q.limesurvey_sid
		FROM forms as f, questionnaires as q
		WHERE f.fid = '$fid'
		AND f.qid = q.qid";

	$rs = $db->GetRow($sql);

	if (!empty($rs['rpc_server_url']))
	{
		$url = $rs['rpc_server_url'];
		$qid = $rs['qid'];
		$surveyid = $rs['limesurvey_sid'];

		include_once(dirname(__FILE__)."/../include/xmlrpc-3.0.0.beta/lib/xmlrpc.inc");

		list($head,$data) = outputdatacsv($qid,$fid,false,false,true);
		$assoc = array();
		for ($i = 0; $i < count($head); $i++)
		{
			//concat if same variable name
			if (isset($assoc[$head[$i]]))
				$assoc[$head[$i]] .= $data[$i];
			else
				$assoc[$head[$i]] = $data[$i];
		}

		//formid not recognised by limesurvey
		unset($assoc['formid']);
		unset($assoc['rpc_id']);
		unset($assoc['filename']);
		unset($assoc['vstatus']);

		//make sure token won't interfere with normal operation of questionnaire
		$assoc['token'] = "queXF-" . $fid;

		$xmlrpc_val=php_xmlrpc_encode($assoc);

		$client = new xmlrpc_client($url);
		$client->setSSLVerifyHost(0);
		//$client->setDebug(2);
		
		$cred = array(new xmlrpcval($rs['rpc_username']),new xmlrpcval($rs['rpc_password']));

		//First need to connect and get session key
		$message = new xmlrpcmsg("get_session_key",$cred);
		$resp = $client->send($message);
		if ($resp->faultCode()) 
		{
			echo T_("XML RPC Error: ").$resp->faultString(); 
		}
		else 
		{
			$sessionkey = $resp->value();
		
			$message = new xmlrpcmsg("add_response", array($sessionkey,new xmlrpcval($surveyid),$xmlrpc_val));
			$resp = $client->send($message);
			if ($resp->faultCode()) 
			{
				echo T_("XML RPC Error: ").$resp->faultString(); 
			}
			else 
			{
				//echo 'OK: got '.php_xmlrpc_decode($resp->value());
				//update forms table with rpc_id
				$sql = "UPDATE forms
					SET rpc_id = '" . php_xmlrpc_decode($resp->value()) . "'
					WHERE fid = '$fid'";

				$db->Execute($sql);
			}
		}
	}
}

/*
 * CSV data output */
function outputdatacsv($qid,$fid = "",$labels = false,$unverified = false, $return = false,$mergenamedfields = false)
{
	global $db;

	//first get data desc

	$sql = "SELECT b.bgid, bg.btid, count( b.bid ) as count,bg.width
		FROM boxes as b
		JOIN boxgroupstype as bg ON (b.bgid = bg.bgid)
		JOIN pages as p ON (p.pid = b.pid)
		WHERE p.qid = '$qid'
		AND bg.btid > 0
		GROUP BY b.bgid
		ORDER BY bg.sortorder";

	$desc = $db->GetAssoc($sql);

	//get completed forms for this qid

	if ($unverified)
    $sql = "SELECT 0 AS vid, f.fid as fid, f.qid as qid, f.description as description, f.rpc_id, 
            '" . T_("Unverified") . "' as vstatus
			FROM forms as f
			WHERE f.qid = '$qid'"; 
	else
    $sql = "SELECT CASE WHEN done = 1 THEN f.assigned_vid ELSE f.assigned_vid2 END AS vid, f.fid AS fid, 
                    CASE WHEN done = 1 THEN f.assigned ELSE f.assigned2 END AS assigned, 
                    CASE WHEN done = 1 THEN f.completed ELSE f.completed2 END AS completed, 
                    f.qid AS qid, f.description AS description, f.rpc_id, 
                    CASE WHEN (done =1 AND assigned_vid IS NOT NULL and assigned_vid2 IS NOT NULL) THEN '".T_("Double") . "' ELSE '".T_("Single")."' END AS vstatus
      FROM forms AS f
      WHERE f.qid = '$qid'
      AND done IN (1,3) ";

	if ($fid != "")
		$sql .= " AND f.fid = '$fid'";

	$forms = $db->GetAll($sql);

	$unv = "";
	if ($unverified) $unv = T_("unverified") . "_";

	if (!$return)
	{
		header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header ("Content-Type: text/ascii");
		header ("Content-Disposition: attachment; filename={$unv}data_$qid.csv");
	}

	$sql = "SELECT bg.varname, bg.btid, count(b.bid) as count
		FROM boxes as b
		JOIN boxgroupstype as bg ON (bg.bgid = b.bgid)
		JOIN pages as p ON (p.pid = b.pid)
		WHERE p.qid = '$qid'
		AND bg.btid > 0
		GROUP BY b.bgid
		ORDER BY bg.sortorder";

	$varnames = $db->GetAll($sql);

	$rv = array();

	$prevarname = "@";
	
	foreach($varnames as $vn)
	{
		if ($vn['btid'] == 2)
		{
			for ($i = 1; $i <= $vn['count']; $i++)
				$rv[] = $vn['varname'] . "_$i";
		}
		else
		{
			//don't add the variable name if we are merging and it matches the last varname
			if (!($mergenamedfields == true && $prevarname == $vn['varname']))
				$rv[] = $vn['varname'];

			$prevarname = $vn['varname'];
		}

		
	}

	$rv[] = "formid";
	$rv[] = "rpc_id";
	$rv[] = "filename";
	$rv[] = "vstatus";

	//print the header row
	if (!$return)
	{
		print csv($rv);
	}

	$prevarname = "@";

	foreach ($forms as $form)
	{
		$sql = "SELECT bg.btid,f.val
			FROM boxes AS b
			JOIN boxgroupstype as bg ON (bg.bgid = b.bgid)
			JOIN pages as p ON (p.pid = b.pid)
			LEFT JOIN formboxverifychar AS f ON (f.vid = '{$form['vid']}' AND f.fid = '{$form['fid']}' AND f.bid = b.bid)
			WHERE p.qid = '$qid'
			AND bg.btid > 0
			ORDER BY bg.sortorder, b.bid";


		$sql = "(SELECT b.bid,b.bgid,g.btid,f.val,sortorder,b.value,b.label,g.varname
    FROM boxes as b
    JOIN boxgroupstype as g ON (b.bgid = g.bgid AND g.btid > 0 AND g.btid < 5)
    JOIN pages as p ON (p.pid = b.pid AND p.qid = '$qid')
    LEFT JOIN formboxverifychar as f ON (f.bid = b.bid AND f.vid = '{$form['vid']}' and f.fid = '{$form['fid']}')
    )
		UNION
		(SELECT b.bid,b.bgid,g.btid,f.val,sortorder,b.value,b.label,g.varname
		FROM boxes as b
		JOIN  boxgroupstype as g on (b.bgid = g.bgid and g.btid IN (5,6))
		JOIN pages as p on  (p.pid = b.pid and p.qid = '$qid')
		LEFT JOIN formboxverifytext as f on (f.bid = b.bid and f.vid = '{$form['vid']}' and f.fid = '{$form['fid']}'))
  	ORDER BY sortorder asc,bid asc";

		$data =  $db->GetAll($sql);

		$bgid = $data[0]['bgid'];
		$btid = "";
		$count = 1;
		$done = "";

		$rr = array();

		$tmpstr = "";
		$labelval = "";
		$valueval = "";

		$data[] = array('btid' => 0,  'bgid' => 0, 'val' => "", 'varname' => "");

		$prebtid = 0;

		$varlist = array();
		$varlistc = 0;
		//print_r($data);

		foreach($data as $val)
		{
			$btid = $val['btid'];

			if ($bgid != $val['bgid']) //we have changed box groups
			{
				$varlist[] = $val['varname'];
				$varlistc++;

				if ($prebtid ==	1 || $prebtid == 3 || $prebtid == 4)
				{
					//multiple boxes -> down to one variable
					if ($prebtid == 1)
					{
						if ($done == 1)
							if ($labels)
								$rr[] = $labelval;
							else
							{
								if (strlen(trim($valueval)) == 0)
									$rr[] = $count; //if single choice, val is the number of the box selected
								else
									$rr[] = $valueval;
							}
						else
							$rr[] = ""; //blank if no val entered
					}
					else
					{

						if ($mergenamedfields == true)
						{
							if ($varlistc > 1 && $varlist[$varlistc - 2] == $varlist[$varlistc - 1])
							{}
							else
							{
								$rr[] = trim($tmpstr);
								$tmpstr = "";
							}
						}
						else
						{
							$rr[] = trim($tmpstr);
							$tmpstr = "";
						}

					}
	
					$labelval = "";
					$valueval = "";
				}

				if ($val['btid'] == 6 || $val['btid'] == 5) 
				{
					//one box per variable - just export
					$rr[] = $val['val'];
				}

				$bgid = $val['bgid']; //reset counters
				$count = 1;
				$done = 0;
			}
			else if ($val['btid'] == 6 || $val['btid'] == 5) 
			{
				//one box per variable - just export
				$rr[] = $val['val'];
			}


			if ($val['btid'] == 1)
			{
				if ($val['val'] == 1)
				{
					$done = 1;
					$labelval = $val['label'];
					$valueval = $val['value'];
				}
				if ($done != 1)
					$count++;
			}
			else if ($val['btid'] == 3 || $val['btid'] == 4)
			{
				if ($val['val'] == "")
					$tmpstr .= " ";
				else
					$tmpstr .= $val['val'];
			}
			else if ($val['btid'] == 2)
			{
				if ($val['val'] == 1)
				{
					if ($labels)
						$rr[] = $val['label'];
					else
					{
						if ($val['value'] == "")
							$rr[] = 1;
						else
							$rr[] = $val['value'];
					}
				}
				else
					$rr[] = "";
			}

			$prebtid = $val['btid'];
		}

		$rr[] = $form['fid']; //print str_pad($form['fid'], 10, " ", STR_PAD_LEFT);
		$rr[] = $form['rpc_id'];
		$rr[] = $form['description'];
		$rr[] = $form['vstatus'];

		//print_r($rr);
		if (!$return)
		{
			print csv($rr);
		}
	}
	if ($return)
	{
		return array($rv,$rr);
	}
}




/*
 * Fixed width data output */

function outputdata($qid,$fid = "", $header =true, $appendformid = true,$unverified = false)
{
	global $db;

	//first get data desc

	$sql = "SELECT b.bgid, bg.btid, count( b.bid ) as count, bg.width
		FROM boxes as b
		JOIN boxgroupstype as bg ON (bg.bgid = b.bgid)
		JOIN pages as p ON (p.pid = b.pid)
		WHERE p.qid = '$qid'
		AND bg.btid > 0
		GROUP BY b.bgid
		ORDER BY bg.sortorder";

	$desc = $db->GetAssoc($sql);

	//get completed forms for this qid
	if ($unverified)
    $sql = "SELECT 0 AS vid, f.fid as fid, f.qid as qid, f.description as description, f.rpc_id, 
            '" . T_("Unverified") . "' as vstatus
			FROM forms as f
			WHERE f.qid = '$qid'"; 
	else
    $sql = "SELECT CASE WHEN done = 1 THEN f.assigned_vid ELSE f.assigned_vid2 END AS vid, f.fid AS fid, 
                    CASE WHEN done = 1 THEN f.assigned ELSE f.assigned2 END AS assigned, 
                    CASE WHEN done = 1 THEN f.completed ELSE f.completed2 END AS completed, 
                    f.qid AS qid, f.description AS description, f.rpc_id, 
                    CASE WHEN (done =1 AND assigned_vid IS NOT NULL and assigned_vid2 IS NOT NULL) THEN '".T_("Double") . "' ELSE '".T_("Single")."' END AS vstatus
      FROM forms AS f
      WHERE f.qid = '$qid'
      AND done IN (1,3) ";

	if ($fid != "")
		$sql .= " AND f.fid = '$fid'";

	$forms = $db->GetAll($sql);

	if ($header)
	{
		$unv = "";
		if ($unverified) $unv = T_("unverified") . "_";

		header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header ("Content-Type: text/ascii");
		header ("Content-Disposition: attachment; filename={$unv}data_$qid.dat");
	}

	foreach ($forms as $form)
	{
		$sql = "SELECT bg.btid,f.val
			FROM boxes AS b
			JOIN boxgroupstype as bg ON (bg.bgid = b.bgid)
			JOIN pages as p ON (p.pid = b.pid)
			LEFT JOIN formboxverifychar AS f ON (f.vid = '{$form['vid']}' AND f.fid = '{$form['fid']}' AND f.bid = b.bid)
			WHERE p.qid = '$qid'
			AND bg.btid > 0
			ORDER BY bg.sortorder, b.bid";


		$sql = "(SELECT b.bid,b.bgid,g.btid,f.val,g.sortorder,b.value
		FROM boxes AS b
		JOIN boxgroupstype AS g ON (g.btid > 0 AND g.btid < 5 AND  b.bgid = g.bgid)
		JOIN pages AS p ON (p.qid = '$qid' AND p.pid = b.pid)
		LEFT JOIN formboxverifychar AS f ON (f.bid = b.bid AND f.vid='{$form['vid']}' AND f.fid = '{$form['fid']}'))
		UNION
		(select b.bid,b.bgid,g.btid,f.val,g.sortorder,b.value
		from boxes as b
		JOIN  boxgroupstype as g on (b.bgid = g.bgid and g.btid IN (5,6))
		JOIN pages as p on  (p.pid = b.pid and p.qid = '$qid')
		LEFT JOIN formboxverifytext as f on (f.bid = b.bid and f.vid = '{$form['vid']}' and f.fid = '{$form['fid']}'))
		order by sortorder asc,bid asc";


		$data =  $db->GetAll($sql);

		$bgid = "";
		$btid = "";
		$count = 1;
		$done = "";
		
		foreach($data as $val)
		{
			if ($bgid != $val['bgid'])
			{
				//print a blank space if none printed for single choice
				if ($btid == 1 && $done == 0)
					print str_pad(" ", max(strlen($desc[$bgid]['count']),$desc[$bgid]['width']), " ", STR_PAD_LEFT);

				$bgid = $val['bgid'];
				$count = 1;
				$done = 0;
			}

			$btid = $val['btid'];

			if ($val['btid'] == 1)
			{
				if ($val['val'] == 1)
				{
					if (strlen(trim($val['value'])) == 0)
						print str_pad($count, max(strlen($desc[$bgid]['count']),$desc[$bgid]['width']), " ", STR_PAD_LEFT); //pad to width
					else
						print str_pad(trim($val['value']), max(strlen($desc[$bgid]['count']),$desc[$bgid]['width']), " ", STR_PAD_LEFT); //pad to width

					$done = 1;
				}
			}
			else if ($val['btid'] == 6 || $val['btid'] == 5)
			{
				print substr(str_pad($val['val'],$desc[$bgid]['width']," ",STR_PAD_RIGHT),0,$desc[$bgid]['width']);
			}
			else
			{
				print str_pad((string)$val['val'],1," ",STR_PAD_LEFT);
			}

			$count++;
		}

		if ($btid == 1 && $done == 0)
			print str_pad(" ", max(strlen($desc[$bgid]['count']),$desc[$bgid]['width']), " ", STR_PAD_LEFT);


		if ($appendformid)
		{
			print str_pad($form['fid'], 10, " ", STR_PAD_LEFT);
      print str_pad((string)$form['rpc_id'], 10, " ", STR_PAD_LEFT);
			print str_pad($form['description'], 255, " ", STR_PAD_RIGHT);
			print str_pad($form['vstatus'], 255, " ", STR_PAD_RIGHT);
		}


		print "\r\n";

	}
}

/* Returns a new var dom element given info
*
*/
function variable_ddi($doc,$width,$varname,$vardescription,$startpos,$vartype,$cats = false)
{

	/*
	<var ID="$varname" name="$varname" dcml="0">
		<location StartPos="$startpos" width="6"/>
		<labl level="variable">ANZSCO of $column_from</labl>
		<catgry missing="N">
			<catValu>1</catValu>
			<labl level="category">Strongly disagree</labl>
		</catgry>
		<varFormat type="numeric">ASCII</varFormat>
	</var>

	*/

	$var = $doc->createElement("var");
		$var->setAttribute("ID", "$varname");
		$var->setAttribute("name", "$varname");
		$var->setAttribute("dcml", "0");

	$location = $doc->createElement("location");
		$location->setAttribute("StartPos", "$startpos");
		$location->setAttribute("width", "$width");

	$var->appendChild($location);
	
	$labl = $doc->createElement("labl", $vardescription);
	$labl->setAttribute("level", "variable");

	$var->appendChild($labl);

	if ($cats !== false)
	{
		foreach($cats as $cat)
		{
			$value = $cat['value'];
			$label = $cat['label'];

			$c = $doc->createElement("catgry");
			$c->setAttribute("missing","N");
			
			$v = $doc->createElement("catValu",$value);
			$c->appendChild($v);

			$l = $doc->createElement("labl",$label);
			$l->setAttribute("level","category");
			$c->appendChild($l);

			$var->appendChild($c);
		}
	}

	$varformat =  $doc->createElement("varFormat","ACSII");
		$varformat->setAttribute("type",$vartype);

	$var->appendChild($varformat);	

	return $var;
}


/**
 * Export the ICR knowledge base as an XML file
 *
 * @param int $kb The knowledge base id
 */ 
function export_icr($kb)
{
	global $db;

    $dom = new DOMDocument('1.0', 'UTF-8');

	$c = $dom->createElement("queXF");
	$dom->appendChild($c); 

	$q = $dom->createElement("kb");

	$tmp = $dom->createElement("id",$kb);
	$q->appendChild($tmp);
		
	$c->appendChild($q);
	
	//Export description
	$sql = "SELECT description
		FROM ocrkb
		WHERE kb = '$kb'";

	$rs = $db->GetRow($sql);
	
	$tmp = $dom->createElement("description",$rs['description']);
	$q->appendChild($tmp);

	//Export kb data
	$sql = "SELECT *
		FROM ocrkbdata
		WHERE kb = '$kb'";
	
	$rs = $db->GetAll($sql);

	foreach($rs as $r)
	{
		$o = $dom->createElement("ocrkbdata");

		foreach($r as $battr => $bval)
		{
			if ($battr != "kb")
			{
				$tmp = $dom->createElement($battr,$bval);
				$o->appendChild($tmp);
			}
		}
		$q->appendChild($o);
	}

    $dom->formatOutput = true;
	$ret = $dom->saveXML();	
	
	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Content-Type: text/xml");
	header ("Content-Disposition: attachment; filename=quexf_icr_$kb.xml");

	echo $ret;
}

/**
 * Export the banding layout as an XML file
 *
 * @param int $qid The quesitonnaire id
 */ 
function export_banding($qid)
{
	global $db;

    $dom = new DOMDocument('1.0', 'UTF-8');

	$c = $dom->createElement("queXF");
	$dom->appendChild($c); 

	$q = $dom->createElement("questionnaire");
	$tmp = $dom->createElement("id",$qid);
	$q->appendChild($tmp);
		
	$c->appendChild($q);
	
	//Export sections
	$sql = "SELECT sid,title,description
		FROM sections
		WHERE qid = '$qid'
		ORDER BY sid ASC";

	$rs = $db->GetAll($sql);
	
	foreach($rs as $r)
	{
		$s = $dom->createElement("section");

		$tmp = $dom->createElement("title",$r['title']);
		$s->appendChild($tmp);

		$tmp = $dom->createElement("label",$r['description']);
		$s->appendChild($tmp);

		$s->setAttribute("id",$r['sid']);
		$q->appendChild($s);
	}

	//Export pages
	$sql = "SELECT pid,pidentifierval as id,tlx,tly,trx,try,blx,bly,brx,bry,rotation,width,height,TL_VERT_TLX,TL_VERT_TLY,TL_VERT_BRX,TL_VERT_BRY,TL_HORI_TLX,TL_HORI_TLY,TL_HORI_BRX,TL_HORI_BRY,TR_VERT_TLX,TR_VERT_TLY,TR_VERT_BRX,TR_VERT_BRY,TR_HORI_TLX,TR_HORI_TLY,TR_HORI_BRX,TR_HORI_BRY,BL_VERT_TLX,BL_VERT_TLY,BL_VERT_BRX,BL_VERT_BRY,BL_HORI_TLX,BL_HORI_TLY,BL_HORI_BRX,BL_HORI_BRY,BR_VERT_TLX,BR_VERT_TLY,BR_VERT_BRX,BR_VERT_BRY,BR_HORI_TLX,BR_HORI_TLY,BR_HORI_BRX,BR_HORI_BRY,VERT_WIDTH,HORI_WIDTH
		FROM pages 
		WHERE qid = '$qid'
		ORDER BY pidentifierval ASC";
	
	$rs = $db->GetAll($sql);

	foreach($rs as $r)
	{
		$pid = $r['pid'];	

		$p = $dom->createElement("page");

		foreach($r as $pattr => $pval)
		{
			$tmp = $dom->createElement($pattr,$pval);
			$p->appendChild($tmp);
		}

		//Box groups
		$sql = "SELECT bgid as id, btid as type, width, varname, sortorder, label, sid as groupsection
			FROM boxgroupstype
			WHERE pid = '$pid'
			ORDER BY sortorder ASC";

		$rs2 = $db->GetAll($sql);

		foreach($rs2 as $r2)
		{
			$bgid = $r2['id'];

			$bg = $dom->createElement("boxgroup");

			foreach($r2 as $battr => $bval)
			{
				
				$tmp = $dom->createElement($battr,$bval);
				if ($battr == 'groupsection' && !empty($bval))
					$tmp->setAttribute("idref",$bval);
				$bg->appendChild($tmp);
			}

			//Boxes
			$sql = "SELECT bid as id, tlx,tly,brx,bry,value,label
				FROM boxes
				WHERE bgid = '$bgid'
				ORDER BY bid ASC";

			$rs3 = $db->GetAll($sql);

			foreach($rs3 as $r3)
			{
				$b = $dom->createElement("box");

				foreach($r3 as $battr => $bval)
				{
					$tmp = $dom->createElement($battr,$bval);
					$b->appendChild($tmp);
				}

				$bg->appendChild($b);
			}
			
			$p->appendChild($bg);
		}
		$q->appendChild($p);
	}
	
    $dom->formatOutput = true;
	$ret = $dom->saveXML();	
	
	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Content-Type: text/xml");
	header ("Content-Disposition: attachment; filename=quexf_$qid.xml");

	echo $ret;

}


/* Export the DDI file for this table with updates based on any new columns added
*
*
*/
function export_ddi($qid)
{
	global $db;

	//get the ddi file
    $dom = new DOMDocument('1.0', 'UTF-8');

	$c = $dom->createElement("codeBook");
	$dom->appendChild($c);


	$d = $dom->createElement("dataDscr");
	$c->appendChild($d);		//create dataDscr element


	//add section information
	$sql = "SELECT description, sid
		FROM sections
		WHERE qid = '$qid'";

	$sections = $db->GetAll($sql);

	foreach ($sections as $section)
	{
		$sid = $section['sid'];

		$sql = "SELECT varname
			FROM boxgroupstype
			WHERE sid = '$sid'
			ORDER BY sortorder ASC";

		$varnames = $db->GetAll($sql);

		$varstring = "";
		
		foreach ($varnames as $varname)
			$varstring .= $varname['varname'] . " ";

		$v = $dom->createElement("varGrp");
		$v->setAttribute("var", $varstring);
		
		$l = $dom->createElement("labl",$section['description']);
		$l->setAttribute("level", "VAR GROUP");

		$v->appendChild($l);
		$d->appendChild($v);		
	}

	$startpos = 1;


	//first get data desc

	$sql = "SELECT b.bgid, bg.btid, bg.varname, bg.label, count( b.bid ) as count,bg.width
		FROM boxes as b
		JOIN boxgroupstype as bg on (bg.bgid = b.bgid)
		JOIN pages as p on (p.pid = b.pid)
		WHERE p.qid = '$qid'
		AND bg.btid > 0
		GROUP BY b.bgid
		ORDER BY bg.sortorder";

	$desc = $db->GetAssoc($sql);


	foreach ($desc as $bgid => $row)
	{
		//length of var
		$length = $row['count'];
		$vartype = "number";
		if ($row['btid'] == 1) $length = max(strlen($row['count']),$row['width']);
		if ($row['btid'] == 3 || $row['btid'] == 6) $vartype = "character";
		if ($row['btid'] == 6 || $row['btid'] == 5) $length = $row['width'];


		$name = $row['varname'];
		$varlabel = $row['label'];
		if (strlen(trim($varlabel)) == 0) $varlabel = $name;

		if ($row['btid'] == 2) //Multiple choice
		{

			$length = 1;

			for ($i = 1; $i <= $row['count']; $i++)
			{
				$nam = $name . "_$i";
				$nvar = variable_ddi($dom,$length,$nam,$nam,$startpos,$vartype,array(array("value" => 1, "label" => "Selected")));
		
				$d->appendChild($nvar);
		
				$nvlocations = $nvar->getElementsByTagName("location");     
				foreach ($nvlocations as $nvlocation)
					$nvlocation->setAttribute("width", "$length");
		
				$startpos += $length;

			}

		}else
		{
			$cats = false;
			
			if ($row['btid'] == 1)
			{
				$sql = "SELECT value,label
					FROM boxes
					WHERE bgid = '$bgid'";
				
				$cats = $db->GetAll($sql);

				/*
				$cats = array();
				for ($i = 1; $i <= $row['count']; $i++)
					$cats[] = array("value" => $i, "label" => "");
				*/
			}

			$nvar = variable_ddi($dom,$length,$name,$varlabel,$startpos,$vartype,$cats);
	
			$d->appendChild($nvar);
	
			$nvlocations = $nvar->getElementsByTagName("location");     
			foreach ($nvlocations as $nvlocation)
				$nvlocation->setAttribute("width", "$length");

			$startpos += $length;
		}
	}


	$nvar = variable_ddi($dom,10,"formid","formid",$startpos,"number");
	$d->appendChild($nvar);
	$startpos += 10;
	$nvlocations = $nvar->getElementsByTagName("location");     
	foreach ($nvlocations as $nvlocation)
		$nvlocation->setAttribute("width", "10");


	$nvar = variable_ddi($dom,10,"rpc_id","rpc_id",$startpos,"number");
	$d->appendChild($nvar);
	$startpos += 10;
	$nvlocations = $nvar->getElementsByTagName("location");     
	foreach ($nvlocations as $nvlocation)
		$nvlocation->setAttribute("width", "10");

	$nvar = variable_ddi($dom,255,"filename","filename",$startpos,"number");
	$d->appendChild($nvar);
	$startpos += 255;
	$nvlocations = $nvar->getElementsByTagName("location");     
	foreach ($nvlocations as $nvlocation)
		$nvlocation->setAttribute("width", "255");

	$nvar = variable_ddi($dom,255,"vstatus","vstatus",$startpos,"number");
	$d->appendChild($nvar);
	$nvlocations = $nvar->getElementsByTagName("location");     
	foreach ($nvlocations as $nvlocation)
		$nvlocation->setAttribute("width", "255");


	//return a formatted version of the DDI file as as string

    $dom->formatOutput = true;
	$ret = $dom->saveXML();	
	
	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Content-Type: text/xml");
	header ("Content-Disposition: attachment; filename=ddi_$qid.xml");

	echo $ret;

}


/**
 * Escape a string to work properly with PSPP
 *
 * @param string $string The string to escape
 * @param int $length The maximum length of the string
 * @return string The escaped and cut string
 */
function pspp_escape($string,$length = 250)
{
	$string = strip_tags($string);
	$from = array("'", "\r\n", "\n");
	$to   = array("", "", "");
	return trim(substr(str_replace($from, $to, $string),0,$length));
}


/**
 * Export the data in PSPP form (may also work with SPSS)
 *
 * @param int qid The qid to export
 *
 */
function export_pspp($qid,$unverified = false)
{
	global $db;

	$unv = "";
	if ($unverified) $unv = T_("unverified") . "_";

	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Content-Type: text");
	header ("Content-Disposition: attachment; filename={$unv}data_$qid.sps");


	echo "DATA LIST FIXED /";

	$sql = "SELECT b.bgid, bg.btid, (CASE WHEN bg.varname = '' THEN CONCAT('Q_',b.bgid) ELSE bg.varname END) as varname, count( b.bid ) as count,bg.width,bg.label
		FROM boxes as b
		JOIN boxgroupstype as bg ON (bg.bgid = b.bgid)
		JOIN pages as p ON (p.pid = b.pid)
		WHERE p.qid = '$qid'
		AND bg.btid > 0
		GROUP BY b.bgid
		ORDER BY bg.sortorder";

	$cols = $db->GetAll($sql);

	$cc = count($cols);
	//PSPP variable name cannot start with a number and must be unique - check
	$vars = array();
	for ($i = 0; $i < $cc; $i++)
	{
		//start numeric
		if (is_numeric(substr($cols[$i]['varname'],0,1)))
			$cols[$i]['varname'] = "V" . $cols[$i]['varname'];

		//make unique
		$letter = "A";
		$checked = false;
		$added = false;
		while (!$checked)
		{
			if (isset($vars[$cols[$i]['varname']]))
			{
				if ($added)
				{
					$letter = chr(ord($letter) + 1);
					$cols[$i]['varname'] = substr($cols[$i]['varname'],0,-1) . $letter;
				}
				else
				{
					$cols[$i]['varname'] = $cols[$i]['varname'] . $letter;
					$added = true;
				}
			}
			else	
			{
				$checked = true;
				$vars[$cols[$i]['varname']] = $cols[$i]['varname'];
			}
		}
	}

	$startpos = 1;
	$width = 0;

	$colsc = 0;
	foreach ($cols as $col)
	{
		$varname = $col['varname'];
		$length = $col['count'];
		$vartype = " ";
		if ($col['btid'] == 1)
		{
			$length = max(strlen($col['count']),$col['width']);

			//check if any values are non-numeric
			$sql = "SELECT count(*) as c
				FROM `boxes` 
				WHERE `bgid` = '{$col['bgid']}'
				AND `value` NOT REGEXP '[0-9]+' 
				AND `value` != ''";

			$vt = $db->Getrow($sql);
		
			if (isset($vt['c']) && !empty($vt['c']))
			{
				$vartype = " (A) ";
				$cols[$colsc]['is_string'] = true;
			}
		}
		if ($col['btid'] == 3 || $col['btid'] == 6) $vartype = "(A) ";
		if ($col['btid'] == 6 || $col['btid'] == 5) $length = $col['width'];

		if ($col['btid'] == 2) //multiple choice
		{
			$length = 1;

			for ($i = 1; $i <= $col['count']; $i++)
			{
				$nam = $varname . "_$i";
	
				$startpos = $startpos + $width;

				$width = $length;
		
				$endpos = ($startpos + $width) - 1;

				echo "$nam $startpos-$endpos $vartype";
			}

		}
		else
		{
				$startpos = $startpos + $width;

				$width = $length;
		
				$endpos = ($startpos + $width) - 1;

				echo "$varname $startpos-$endpos $vartype";
		}
		$colsc++;
	}

	$startpos = $startpos + $width;
	$endpos = $startpos + 9;
	echo "formid $startpos-$endpos  ";

	$startpos = $startpos + 10;
	$endpos = $startpos + 9;
	echo "rpc_id $startpos-$endpos  ";

  $startpos = $startpos + 10;
	$endpos = $startpos + 254;
	echo "filename $startpos-$endpos (A)  ";

  $startpos = $startpos + 255;
	$endpos = $startpos + 254;
	echo "vstatus $startpos-$endpos (A)  ";



	echo " .\nVARIABLE LABELS ";

	$first = true;
	foreach ($cols as $col)
	{
		$vardescription = pspp_escape($col['label']);
		$varname = $col['varname'];
		
		if ($first)			
			$first = false;
		else
			echo "/";

		if ($col['btid'] == 2) //multiple choice
		{
			for ($i = 1; $i <= $col['count']; $i++)
			{
				$nam = $varname . "_$i";
				echo "$nam '$vardescription' ";
			}
		}
		else
		{
			echo "$varname '$vardescription' ";
		}
	}
	echo "/formid 'queXF Form ID' /rpc_id 'queXF RPC ID' /filename 'Original filename' /vstatus 'Verification status' .\n";

	echo "VALUE LABELS ";

	foreach ($cols as $col)
	{
		$varname = $col['varname'];
	
		if ($col['btid'] == 1)
		{
			$sql = "SELECT value,label
				FROM boxes
				WHERE bgid = '{$col['bgid']}'";

			$rs = $db->GetAll($sql);

			if (!empty($rs))
			{
				echo " /$varname";
				foreach ($rs as $r)
					if ($r['value'] != "")
					{
						if (!isset($col['is_string']))
							echo " {$r['value']} '";
						else
						{
							echo " '";
							//make label same width
							echo str_pad($r['value'], $col['width']," ", STR_PAD_LEFT);
							echo "' '"; 
						}
						echo pspp_escape($r['label']) . "'";
					}
      }
    }
      else	if ($col['btid'] == 2)
		  {
			$sql = "SELECT value,label
				FROM boxes
				WHERE bgid = '{$col['bgid']}'";

			$rs = $db->GetAll($sql);

      $i = 1;

			if (!empty($rs))
			{
        foreach ($rs as $r)
        {
          echo " /$varname" . "_" . $i;
					if ($r['value'] != "")
					{
						if (!isset($col['is_string']))
							echo " {$r['value']} '";
						else
						{
							echo " '";
							//make label same width
							echo str_pad($r['value'], $col['width']," ", STR_PAD_LEFT);
							echo "' '"; 
						}
						echo pspp_escape($r['label']) . "'";
          }
          $i++;
        }
      }
		}
	}

	echo " .\nBEGIN DATA.\n";

	outputdata($qid,"",false,true,$unverified);

	echo "END DATA.\n";
}




?>
