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


include_once(dirname(__FILE__).'/../config.inc.php');
include_once(dirname(__FILE__).'/../db.inc.php');

if (version_compare(PHP_VERSION,'5','>='))
 include_once('domxml-php4-to-php5.php');

set_time_limit(600);


function csv($fields = array(), $delimiter = ',', $enclosure = '"')
{
    $str = '';
    $escape_char = '\\';
    foreach ($fields as $value)
    {
      if (strpos($value, $delimiter) !== false ||
          strpos($value, $enclosure) !== false ||
          strpos($value, "\n") !== false ||
          strpos($value, "\r") !== false ||
          strpos($value, "\t") !== false ||
          strpos($value, ' ') !== false)
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



/*
 * CSV data output */
function outputdatacsv($qid,$fid = "",$labels = false,$unverified = false)
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
		$sql = "SELECT 0 AS vid, f.fid as fid, f.qid as qid, f.description as description
			FROM forms as f
			WHERE f.qid = '$qid'"; 
	else
		$sql = "SELECT w.vid AS vid, w.fid AS fid, w.assigned AS assigned, w.completed AS completed, f.qid AS qid, f.description AS description
			FROM `worklog` AS w
			LEFT JOIN forms AS f ON w.fid = f.fid
			WHERE f.qid = '$qid'";

	if ($fid != "")
		$sql .= " AND f.fid = '$fid'";

	$forms = $db->GetAll($sql);

	$unv = "";
	if ($unverified) $unv = T_("unverified") . "_";

	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Content-Type: text/ascii");
	header ("Content-Length: ");
	header ("Content-Disposition: attachment; filename={$unv}data_$qid.csv");

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
	foreach($varnames as $vn)
	{
		if ($vn['btid'] == 2)
		{
			for ($i = 1; $i <= $vn['count']; $i++)
				$rv[] = $vn['varname'] . "_$i";
		}
		else
			$rv[] = $vn['varname'];
	}

	$rv[] = "formid";

	//print the header row
	print csv($rv);

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


		$sql = "(select b.bid,b.bgid,g.btid,f.val,sortorder,b.value,b.label
		from boxes as b, boxgroupstype as g, pages as p, formboxverifychar as f
		where b.bgid = g.bgid
		and g.btid > 0
		and g.btid < 5
		and p.pid = b.pid
		and p.qid = '$qid'
		and f.bid = b.bid and f.vid = '{$form['vid']}' and f.fid = '{$form['fid']}')
		UNION
		(select b.bid,b.bgid,g.btid,f.val,sortorder,b.value,b.label
		from boxes as b
		JOIN  boxgroupstype as g on (b.bgid = g.bgid and g.btid = 6)
		JOIN pages as p on  (p.pid = b.pid and p.qid = '$qid')
		LEFT JOIN formboxverifytext as f on (f.bid = b.bid and f.vid = '{$form['vid']}' and f.fid = '{$form['fid']}'))
		UNION
		(select b.bid,b.bgid,g.btid,f.val,sortorder,b.value,b.label
		from boxes as b
		JOIN  boxgroupstype as g on (b.bgid = g.bgid and g.btid = 5)
		JOIN pages as p on  (p.pid = b.pid and p.qid = '$qid')
		LEFT JOIN formboxverifytext as f on (f.bid = b.bid and f.vid = 0 and f.fid = '{$form['fid']}'))
		order by sortorder asc,bid asc";


		$data =  $db->GetAll($sql);

		$bgid = $data[0]['bgid'];
		$btid = "";
		$count = 1;
		$done = "";

		$rr = array();

		$tmpstr = "";
		$labelval = "";
		$valueval = "";

		$data[] = array('btid' => 0,  'bgid' => 0, 'val' => "");

		//print_r($data);

		foreach($data as $val)
		{
			$btid = $val['btid'];

			if ($bgid != $val['bgid']) //we have changed box groups
			{
				if ($prebtid ==	1 || $prebtid == 3 || $prebtid == 4)
				{
					//multiple boxes -> down to one variable
					if ($prebtid == 1)
						if ($done == 1)
							if ($labels)
								$rr[] = $labelval;
							else
							{
								if (empty($valueval))
									$rr[] = $count; //if single choice, val is the number of the box selected
								else
									$rr[] = $valueval;
							}
						else
							$rr[] = ""; //blank if no val entered
					else
						$rr[] = $tmpstr;
	
					$tmpstr = "";
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
				$tmpstr .= $val['val'];
			else if ($val['btid'] == 2)
			{
				if ($labels)
					$rr[] = $val['label'];
				else
				{
					if (empty($val['value']))
						$rr[] = $val['val'];
					else
						$rr[] = $val['value'];
				}
			}

			$prebtid = $val['btid'];
		}

		$rr[] = $form['fid']; //print str_pad($form['fid'], 10, " ", STR_PAD_LEFT);

		//print_r($rr);
		print csv($rr);
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
		$sql = "SELECT 0 AS vid, f.fid as fid, f.qid as qid, f.description as description
			FROM forms as f
			WHERE f.qid = '$qid'"; 
	else
		$sql = "SELECT w.vid AS vid, w.fid AS fid, w.assigned AS assigned, w.completed AS completed, f.qid AS qid, f.description AS description
			FROM `worklog` AS w
			LEFT JOIN forms AS f ON w.fid = f.fid
			WHERE f.qid = '$qid'";

	if ($fid != "")
		$sql .= " AND f.fid = '$fid'";

	$forms = $db->GetAll($sql);

	if ($header)
	{
		$unv = "";
		if ($unverified) $unv = T_("unverified") . "_";

		header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header ("Content-Type: text/ascii");
		header ("Content-Length: ");
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
		JOIN  boxgroupstype as g on (b.bgid = g.bgid and g.btid = 6)
		JOIN pages as p on  (p.pid = b.pid and p.qid = '$qid')
		LEFT JOIN formboxverifytext as f on (f.bid = b.bid and f.vid = '{$form['vid']}' and f.fid = '{$form['fid']}'))
		UNION
		(select b.bid,b.bgid,g.btid,f.val,g.sortorder,b.value
		from boxes as b
		JOIN  boxgroupstype as g on (b.bgid = g.bgid and g.btid = 5)
		JOIN pages as p on  (p.pid = b.pid and p.qid = '$qid')
		LEFT JOIN formboxverifytext as f on (f.bid = b.bid and f.vid = 0 and f.fid = '{$form['fid']}'))
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
					print str_pad(" ", strlen($desc[$bgid]['count']), " ", STR_PAD_LEFT);

				$bgid = $val['bgid'];
				$count = 1;
				$done = 0;
			}

			$btid = $val['btid'];

			if ($val['btid'] == 1)
			{
				if ($val['val'] == 1)
				{
					if (empty($val['value']))
						print str_pad($count, strlen($desc[$bgid]['count']), " ", STR_PAD_LEFT); //pad to width
					else
						print str_pad($val['value'], strlen($desc[$bgid]['count']), " ", STR_PAD_LEFT); //pad to width

					$done = 1;
				}
			}
			else if ($val['btid'] == 6 || $val['btid'] == 5)
			{
				print substr(str_pad($val['val'],$desc[$bgid]['width']," ",STR_PAD_RIGHT),0,$desc[$bgid]['width']);
			}
			else
			{
				print str_pad($val['val'],1," ",STR_PAD_LEFT);
			}

			$count++;
		}

		if ($btid == 1 && $done == 0)
			print str_pad(" ", strlen($desc[$bgid]['count']), " ", STR_PAD_LEFT);


		if ($appendformid) 
			print str_pad($form['fid'], 10, " ", STR_PAD_LEFT);


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

	$var = $doc->create_element("var");
		$var->set_attribute("ID", "$varname");
		$var->set_attribute("name", "$varname");
		$var->set_attribute("dcml", "0");

	$location = $doc->create_element("location");
		$location->set_attribute("StartPos", "$startpos");
		$location->set_attribute("width", "$width");

	$var->append_child($location);
	
	$labl = $doc->create_element("labl");
		$labl->set_attribute("level", "variable");
		$labl->set_content("$vardescription");

	$var->append_child($labl);

	if ($cats !== false)
	{
		foreach($cats as $cat)
		{
			$value = $cat['value'];
			$label = $cat['label'];

			$c = $doc->create_element("catgry");
			$c->set_attribute("missing","N");
			
			$v = $doc->create_element("catValu");
			$v->set_content($value);
			$c->append_child($v);

			$l = $doc->create_element("labl");
			$l->set_attribute("level","category");
			$l->set_content($label);
			$c->append_child($l);

			$var->append_child($c);
		}
	}

	$varformat =  $doc->create_element("varFormat");
		$varformat->set_attribute("type",$vartype);
		$varformat->set_content("ASCII");

	$var->append_child($varformat);	

	return $var;
}


/**
 * Export the banding layout as an XML file
 *
 * @param int $qid The quesitonnaire id
 */ 
function export_banding($qid)
{
	global $db;

	$dom = domxml_new_doc("1.0");

	$c = $dom->create_element("queXF");
	$dom->append_child($c); 

	$q = $dom->create_element("questionnaire");
	$tmp = $dom->create_element("id");
	$tmp->set_content($qid);
	$q->append_child($tmp);
		
	$c->append_child($q);
	
	//Export sections
	$sql = "SELECT sid,title,description
		FROM sections
		WHERE qid = '$qid'
		ORDER BY sid ASC";

	$rs = $db->GetAll($sql);
	
	foreach($rs as $r)
	{
		$s = $dom->create_element("section");

		$tmp = $dom->create_element("title");
		$tmp->set_content($r['title']);
		$s->append_child($tmp);

		$tmp = $dom->create_element("label");
		$tmp->set_content($r['description']);
		$s->append_child($tmp);

		$s->set_attribute("id",$r['sid']);
		$q->append_child($s);
	}

	//Export pages
	$sql = "SELECT pid,pidentifierval as id,tlx,tly,trx,try,blx,bly,brx,bry,rotation
		FROM pages 
		WHERE qid = '$qid'
		ORDER BY pidentifierval ASC";
	
	$rs = $db->GetAll($sql);

	foreach($rs as $r)
	{
		$pid = $r['pid'];	

		$p = $dom->create_element("page");

		foreach($r as $pattr => $pval)
		{
			$tmp = $dom->create_element($pattr);
			$tmp->set_content($pval);
			$p->append_child($tmp);
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

			$bg = $dom->create_element("boxgroup");

			foreach($r2 as $battr => $bval)
			{
				
				$tmp = $dom->create_element($battr);
				if ($battr == 'groupsection' && !empty($bval))
					$tmp->set_attribute("idref",$bval);
				else
					$tmp->set_content($bval);
				$bg->append_child($tmp);
			}

			//Boxes
			$sql = "SELECT bid as id, tlx,tly,brx,bry,value,label
				FROM boxes
				WHERE bgid = '$bgid'
				ORDER BY bid ASC";

			$rs3 = $db->GetAll($sql);

			foreach($rs3 as $r3)
			{
				$b = $dom->create_element("box");

				foreach($r3 as $battr => $bval)
				{
					$tmp = $dom->create_element($battr);
					$tmp->set_content($bval);
					$b->append_child($tmp);
				}

				$bg->append_child($b);
			}
			
			$p->append_child($bg);
		}
		$q->append_child($p);
	}
	
	$ret = $dom->dump_mem(true);	
	
	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Content-Type: text/xml");
	header ("Content-Length: " . strlen($ret));
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
	$dom = domxml_new_doc("1.0");  //create new file


	$c = $dom->create_element("codeBook");
	$dom->append_child($c);


	$d = $dom->create_element("dataDscr");
	$c->append_child($d);		//create dataDscr element


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

		$v = $dom->create_element("varGrp");
		$v->set_attribute("var", $varstring);
		
		$l = $dom->create_element("labl");
		$l->set_attribute("level", "VAR GROUP");
		$l->set_content($section['description']);

		$v->append_child($l);
		$d->append_child($v);		
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
		if ($row['btid'] == 1) $length = strlen($row['count']);
		if ($row['btid'] == 3 || $row['btid'] == 6) $vartype = "character";
		if ($row['btid'] == 6 || $row['btid'] == 5) $length = $row['width'];


		$name = $row['varname'];
		$varlabel = $row['label'];
		if (empty($varlabel)) $varlabel = $name;

		if ($row['btid'] == 2) //Multiple choice
		{

			$length = 1;

			for ($i = 1; $i <= $row['count']; $i++)
			{
				$nam = $name . "_$i";
				$nvar = variable_ddi($dom,$length,$nam,$nam,$startpos,$vartype,array(array("value" => 1, "label" => "Selected")));
		
				$d->append_child($nvar);
		
				$nvlocations = $nvar->get_elements_by_tagname("location");     
				foreach ($nvlocations as $nvlocation)
					$nvlocation->set_attribute("width", "$length");
		
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
	
			$d->append_child($nvar);
	
			$nvlocations = $nvar->get_elements_by_tagname("location");     
			foreach ($nvlocations as $nvlocation)
				$nvlocation->set_attribute("width", "$length");

			$startpos += $length;
		}
	}


	$nvar = variable_ddi($dom,10,"formid","formid",$startpos,"number");

	$d->append_child($nvar);

	$nvlocations = $nvar->get_elements_by_tagname("location");     
	foreach ($nvlocations as $nvlocation)
		$nvlocation->set_attribute("width", "10");


	//return a formatted version of the DDI file as as string

	$ret = $dom->dump_mem(true);	
	
	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Content-Type: text/xml");
	header ("Content-Length: " . strlen($ret));
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
	$from = array("'", "\r\n", "\n");
	$to   = array("", "", "");
	return substr(str_replace($from, $to, $string),0,$length);
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

	$startpos = 1;
	$width = 0;

	foreach ($cols as $col)
	{
		$varname = $col['varname'];
		$length = $col['count'];
		$vartype = " ";
		if ($col['btid'] == 1) $length = strlen($col['count']);
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
	}

	$startpos = $startpos + $width;
	$endpos = $startpos + 9;
	echo "formid $startpos-$endpos  ";

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
	echo "/formid 'queXF Form ID' .\n";

	echo "VALUE LABELS ";

	foreach ($cols as $col)
	{
		$varname = $col['varname'];
	
		if ($col['btid'] == 1 || $col['btid'] == 2)
		{
			$sql = "SELECT value,label
				FROM boxes
				WHERE bgid = '{$col['bgid']}'";

			$rs = $db->GetAll($sql);

			if (!empty($rs))
			{
				echo " /$varname";
				foreach ($rs as $r)
					if (!empty($r['value']))
						echo " {$r['value']} '" . pspp_escape($r['label']) . "'";
			}
		}
	}

	echo " .\nBEGIN DATA.\n";

	outputdata($qid,"",false,true,$unverified);

	echo "END DATA.\n";
}




?>
