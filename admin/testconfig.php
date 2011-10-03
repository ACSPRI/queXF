<?

include_once("../config.inc.php");
include_once("../db.inc.php");
include_once("../lang.inc.php");

?>
<html>
<head>
<title><? echo T_("Test configuration"); ?></title>
</head>
<body>
<?


global $db;

$fail = false;

$sql = "SELECT * 
	FROM worklog";

if (!($db->Execute($sql)))
{
	$fail = true;
	print "<p>" . T_("Could not connect to database. Make sure the database: ") . DB_NAME .  T_(" exists on ") . DB_HOST . T_(" and that ") . DB_USER . T_(" has privileges to it, otherwise modify config.inc.php to point to the correct database. Also make sure you have loaded the database structure from") . " <a href=\"../database/quexf.sql\">quexf.sql</a></p>";
}else
{
	print "<p>" . T_("Database connection succeeded") . "</p>";
}

if (isset($_SERVER['PHP_AUTH_USER']))
{
	print "<p>" . T_("User authentication has been set up. You are user: ") . $_SERVER['PHP_AUTH_USER'] . "</p>";
}
else
{
	$fail = true;
	print "<p>" . T_("Could not detect user authentication. Please set up web server based authentication. If using apache, see here: ") . "<a href='http://httpd.apache.org/docs/2.0/howto/auth.html'>" . T_("Apache authentication") . "</a></p>";
}

$post_max_size = ini_get('post_max_size');
if (substr($post_max_size,0,-1) < 10)
	$pms = T_("Recommended minimum") . ": 10M";
else
	$pms = T_("Passed");
$upload_max_filesize = ini_get('upload_max_filesize');
if (substr($upload_max_filesize,0,-1) < 10)
	$umf = T_("Recommended minimum") . ": 10M";
else
	$umf = T_("Passed");
$memory_limit = ini_get('memory_limit');
if (substr($memory_limit,0,-1) < 128)
	$ml = T_("Recommended minimum") . ": 128M";
else
	$ml = T_("Passed");

print "<p>" . T_("Configuration options from php.ini:") . "</p>";
print "<ul><li>post_max_size = $post_max_size   <b>$pms</b></li>
	<li>upload_max_filesize = $upload_max_filesize  <b>$umf</b></li>
	<li>memory_limit = $memory_limit  <b>$ml</b></li></ul>";

$gsbin = GS_BIN;
if($pos=stripos($gsbin," ")) $gsbin=substr($gsbin,0,$pos);
if (is_file($gsbin)) {
	$ver = exec(GS_BIN . " --version");
	if ($ver)
	{
		print "<p>" . T_("Found GhostScript version") . " $ver</p>";
	}
	else
	{
		print "<p>" . GS_BIN . T_(" exists but can not execute it. Please make sure you are pointing to the executable file, not just the directory of Ghostscript") . "</p>";
		$fail = true;
	}
} else {
	echo "<p>" . T_("Could not find GhostScript in path: ") . GS_BIN .  "</p><p>" . T_("Please modify config.inc.php, GS_BIN to point to the gs executable. Also please make sure you are pointing to the executable file, not just the directory of Ghostscript") . "</p>";
	$fail = true;
}

/*
if (OCR_ENABLED)
{
	
if (is_file(TESSERACT_BIN)) {
	print "<p>" . T_("Found Tesseract") . "</p>";
} else {
	echo "<p>"  . T_("Could not find Tesseract in path: ")  . TESSERACT_BIN .  "</p><p>" . T_("Please modify config.inc.php, TESSERACT_BIN to point to the tesseract executable or disable OCR by changing OCR_ENABLED to false") ."</p>";
		$fail = true;
}

if (is_file(CONVERT_BIN)) {
	print "<p>" .T_("Found ImageMagick") . "</p>";
} else
{
	echo "<p>" .T_("Could not find ImageMagick in path: ") . CONVERT_BIN .  "</p><p>" . T_("Please modify config.inc.php, CONVERT_BIN to point to the convert executable or disable OCR by changing OCR_ENABLED to false") . "</p>";
		$fail = true;
}
}
*/

if ($fail)
{
	print "<h1>" . T_("FAILED") . "</h1>";
}
else
{
	print "<h1>" . T_("Passed Configuration Test") . "</h1>";
}


?>
</body>
</html>
