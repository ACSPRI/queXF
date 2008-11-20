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
function outputdatacsv($qid,$fid = "")
{
	global $db;

	//first get data desc

	$sql = "SELECT bgid, btid, count( bid ) as count,width
		FROM boxesgroupstypes
		WHERE qid = '$qid'
		AND btid > 0
		GROUP BY bgid
		ORDER BY sortorder";

	$desc = $db->GetAssoc($sql);

	//get completed forms for this qid

	$sql = "SELECT w.vid AS vid, w.fid AS fid, w.assigned AS assigned, w.completed AS completed, f.qid AS qid, f.description AS description
		FROM `worklog` AS w
		LEFT JOIN forms AS f ON w.fid = f.fid
		WHERE f.qid = '$qid'";

	if ($fid != "")
		$sql .= " AND f.fid = '$fid'";

	$forms = $db->GetAll($sql);


	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Content-Type: text/ascii");
	header ("Content-Length: ");
	header ("Content-Disposition: attachment; filename=temp.csv");

	$sql = "SELECT varname,btid, count(bid) as count
		FROM boxesgroupstypes
		WHERE qid = '$qid'
		AND btid > 0
		GROUP BY bgid
		ORDER BY sortorder";

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
		$sql = "SELECT btid,val
		FROM `boxesgroupstypes` AS b
		LEFT JOIN formboxverifychar AS f ON ( f.vid = '{$form['vid']}'
		AND f.fid = '{$form['fid']}'
		AND f.bid = b.bid )
		WHERE b.qid = '$qid'
		AND b.btid >0
		ORDER BY b.sortorder, b.bid";


		$sql = "(select b.bid,b.bgid,g.btid,f.val,sortorder
		from boxes as b, boxgroupstype as g, pages as p, formboxverifychar as f
		where b.bgid = g.bgid
		and g.btid > 0
		and p.pid = b.pid
		and p.qid = '$qid'
		and f.bid = b.bid and f.vid = '{$form['vid']}' and f.fid = '{$form['fid']}')
		UNION
		(select b.bid,b.bgid,g.btid,f.val,sortorder
		from boxes as b
		JOIN  boxgroupstype as g on (b.bgid = g.bgid and g.btid = 6)
		JOIN pages as p on  (p.pid = b.pid and p.qid = '$qid')
		LEFT JOIN formboxverifytext as f on (f.bid = b.bid and f.vid = '{$form['vid']}' and f.fid = '{$form['fid']}'))
		UNION
		(select b.bid,b.bgid,g.btid,f.val,sortorder
		from boxes as b, boxgroupstype as g, pages as p, formboxverifytext as f
		where b.bgid = g.bgid
		and g.btid = 5
		and p.pid = b.pid
		and p.qid = '$qid'
		and f.bid = b.bid and f.vid = '0' and f.fid = '{$form['fid']}')
		order by sortorder asc,bid asc";


		$data =  $db->GetAll($sql);

		//print $sql;

		$bgid = $data[0]['bgid'];
		$btid = "";
		$count = 1;
		$done = "";

		$rr = array();

		$tmpstr = "";

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
							$rr[] = $count; //if single choice, val is the number of the box selected
						else
							$rr[] = ""; //blank if no val entered
					else
						$rr[] = $tmpstr;
	
					$tmpstr = "";
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
					$done = 1;
				if ($done != 1)
					$count++;
			}
			else if ($val['btid'] == 3 || $val['btid'] == 4)
				$tmpstr .= $val['val'];
			else if ($val['btid'] == 2)
				$rr[] = $val['val'];

			$prebtid = $val['btid'];
		}

		if ($prebtid == 1)
			if ($done == 1)
				$rr[] = $count; //if single choice, val is the number of the box selected
			else
				$rr[] = ""; //blank if no val entered
		else if ($prebtid == 3 || $prebtid == 4)
			$rr[] = $tmpstr;


		$rr[] = $form['fid']; //print str_pad($form['fid'], 10, " ", STR_PAD_LEFT);

		//print_r($rr);
		print csv($rr);
	}
}




/*
 * Fixed width data output */

function outputdata($qid,$fid = "")
{
	global $db;

	//first get data desc

	$sql = "SELECT bgid, btid, count( bid ) as count,width
		FROM boxesgroupstypes
		WHERE qid = '$qid'
		AND btid > 0
		GROUP BY bgid
		ORDER BY sortorder";

	$desc = $db->GetAssoc($sql);

	//get completed forms for this qid

	$sql = "SELECT w.vid AS vid, w.fid AS fid, w.assigned AS assigned, w.completed AS completed, f.qid AS qid, f.description AS description
		FROM `worklog` AS w
		LEFT JOIN forms AS f ON w.fid = f.fid
		WHERE f.qid = '$qid'";

	if ($fid != "")
		$sql .= " AND f.fid = '$fid'";

	$forms = $db->GetAll($sql);


	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Content-Type: text/ascii");
	header ("Content-Length: ");
	header ("Content-Disposition: attachment; filename=temp.dat");


	foreach ($forms as $form)
	{
		$sql = "SELECT btid,val
		FROM `boxesgroupstypes` AS b
		LEFT JOIN formboxverifychar AS f ON ( f.vid = '{$form['vid']}'
		AND f.fid = '{$form['fid']}'
		AND f.bid = b.bid )
		WHERE b.qid = '$qid'
		AND b.btid >0
		ORDER BY b.sortorder, b.bid";


		$sql = "(select b.bid,b.bgid,g.btid,f.val,sortorder
		from boxes as b, boxgroupstype as g, pages as p, formboxverifychar as f
		where b.bgid = g.bgid
		and g.btid > 0
		and p.pid = b.pid
		and p.qid = '$qid'
		and f.bid = b.bid and f.vid = '{$form['vid']}' and f.fid = '{$form['fid']}')
		UNION
		(select b.bid,b.bgid,g.btid,f.val,sortorder
		from boxes as b
		JOIN  boxgroupstype as g on (b.bgid = g.bgid and g.btid = 6)
		JOIN pages as p on  (p.pid = b.pid and p.qid = '$qid')
		LEFT JOIN formboxverifytext as f on (f.bid = b.bid and f.vid = '{$form['vid']}' and f.fid = '{$form['fid']}'))
		UNION
		(select b.bid,b.bgid,g.btid,f.val,sortorder
		from boxes as b, boxgroupstype as g, pages as p, formboxverifytext as f
		where b.bgid = g.bgid
		and g.btid = 5
		and p.pid = b.pid
		and p.qid = '$qid'
		and f.bid = b.bid and f.vid = '0' and f.fid = '{$form['fid']}')
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
					print str_pad($count, strlen($desc[$bgid]['count']), " ", STR_PAD_LEFT); //pad to width
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


		print str_pad($form['fid'], 10, " ", STR_PAD_LEFT);
		//print str_pad($form['suspense_file'], 30, " ", STR_PAD_RIGHT);
		print "\r\n";

	}
}

/* Returns a new var dom element given info
*
*/
function variable_ddi($doc,$width,$varname,$vardescription,$startpos,$vartype)
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

	$varformat =  $doc->create_element("varFormat");
		$varformat->set_attribute("type",$vartype);
		$varformat->set_content("ASCII");

	$var->append_child($varformat);	

	return $var;
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

	$startpos = 1;


	//first get data desc

	$sql = "SELECT bgid, btid, varname, count( bid ) as count,width
		FROM boxesgroupstypes
		WHERE qid = '$qid'
		AND btid > 0
		GROUP BY bgid
		ORDER BY sortorder";

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

		if ($row['btid'] == 2)
		{

			$length = 1;

			for ($i = 1; $i <= $row['count']; $i++)
			{
				$nam = $name . "_$i";
				$nvar = variable_ddi($dom,$length,$nam,$nam,$startpos,$vartype);
		
				$d->append_child($nvar);
		
				$nvlocations = $nvar->get_elements_by_tagname("location");     
				foreach ($nvlocations as $nvlocation)
					$nvlocation->set_attribute("width", "$length");
		
				$startpos += $length;

			}

		}else
		{

			$nvar = variable_ddi($dom,$length,$name,$name,$startpos,$vartype);
	
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
	header ("Content-Disposition: attachment; filename=ddi_temp.xml");

	echo $ret;

}



?>
