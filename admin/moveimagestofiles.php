<?php

/*	Copyright Australian Consortium for Social and Political Research Incorporated (ACSPRI) 2014
 *	Written by Adam Zammit - adam.zammit@acspri.org.au
 *	For ACSPRI: http://www.acspri.org.au/
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


include_once("../config.inc.php");
include_once("../db.inc.php");
include("../functions/functions.database.php");

if ($argc != 2 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

Move images from the database to files

  Usage:
    <?php echo $argv[0]; ?> qid

    Where qid is the id of the questionnaire you wish to move images 
    out of the database. Note that the database will be updated to
    reflect that images have been moved.

<?php
} else {
  //Move images from database to files
  //

  if (is_writable(IMAGES_DIRECTORY) && !empty($argv[1]))
  {
    $qid = intval($argv[1]);

    $sql = "SELECT description
            FROM questionnaires
            WHERE qid = $qid";

    $d = $db->GetOne($sql);

    if (!empty($d))
    {
      print "Moving images for $d\n";
      
      $sql = "SELECT fp.fpid, fp.fid, fp.pid
              FROM `forms` as f, `formpages` as fp
              WHERE f.qid = $qid
              AND fp.fid = f.fid
              AND fp.filename = ''";

      $rs = $db->GetAll($sql);
  
      $ic = 0;

      foreach($rs as $r)
      {
	$sql = "SELECT image
		FROM formpages
		WHERE fpid = {$r['fpid']}";

	$im = $db->GetOne($sql);

        //move image to file then remove from database and set filename
        $filename = $r['fid'] . "-" . $r['pid'] . ".png";
        $image = imagecreatefromstring($im);
        if ($image !== FALSE)
        {
          if (imagepng($image,IMAGES_DIRECTORY . $filename))
          {
            $sql = "UPDATE formpages
                    SET image = '', filename = '$filename'
                    WHERE fpid = '{$r['fpid']}'";

            $db->Execute($sql);
            
            print "Moved fid:{$r['fid']} pid:{$r['pid']} to $filename\n";

            $ic++;
          }
          else
            print "Cannot write to $filename - skipping\n";
        }
        else
           print "Cannot read fid:{$r['fid']} pid:{$r['pid']} from database - skipping\n";
      }

      print "Moved $ic images to " . IMAGES_DIRECTORY . "\n";
    }
    else
      print "Questionnaire does not exist\n";
  }
  else
    print "No qid provided or " . IMAGES_DIRECTORY . " not writeable\n";
}

