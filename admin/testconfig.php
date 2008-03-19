<html>
<head>
<title>Test Installation</title>
</head>
<body>
<?

include("../config.inc.php");

global $db;

$fail = false;

$sql = "SELECT * 
	FROM worklog";

if (!($db->Execute($sql)))
{
	$fail = true;
	print "<p>Could not connect to database. Make sure the database: " . DB_NAME .  " exists on " . DB_HOST . " and that " . DB_USER . " has privileges to it, otherwise modify config.inc.php to point to the correct database. Also make sure you have loaded the database structure from <a href=\"../database/quexf.sql\">quexf.sql</a></p>";
}else
{
	print "<p>Database connection succeeded</p>";
}

if (is_file(GS_BIN)) {
	$ver = exec(GS_BIN . " --version");
	if ($ver)
	{
		print "<p>Found GhostScript version $ver</p>";
	}
	else
	{
		print "<p>" . GS_BIN . " exists but can not execute it. Please make sure you are pointing to the executable file, not just the directory of Ghostscript</p>";
		$fail = true;
	}
} else {
	echo "<p>Could not find GhostScript in path: " . GS_BIN .  "</p><p>Please modify config.inc.php, GS_BIN to point to the gs executable. Also please make sure you are pointing to the executable file, not just the directory of Ghostscript</p>";
	$fail = true;
}

if (OCR_ENABLED)
{
	
if (is_file(TESSERACT_BIN)) {
	print "<p>Found Tesseract</p>";
} else {
	echo "<p>Could not find Tesseract in path: " . TESSERACT_BIN .  "</p><p>Please modify config.inc.php, TESSERACT_BIN to point to the tesseract executable or disable OCR by changing OCR_ENABLED to false</p>";
		$fail = true;
}

if (is_file(CONVERT_BIN)) {
	print "<p>Found ImageMagick</p>";
} else
{
	echo "<p>Could not find ImageMagick in path: " . CONVERT_BIN .  "</p><p>Please modify config.inc.php, CONVERT_BIN to point to the convert executable or disable OCR by changing OCR_ENABLED to false</p>";
		$fail = true;
}
}

if ($fail)
{
	print "<h1>FAILED</h1>";
}
else
{
	print "<h1>Passed Configuration Test</h1>";
}


?>
</body>
</html>

