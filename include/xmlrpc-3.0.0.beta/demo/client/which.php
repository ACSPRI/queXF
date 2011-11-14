<html>
<head><title>xmlrpc</title></head>
<body>
<h1>Which toolkit demo</h1>
<h2>Query server for toolkit information</h2>
<h3>The code demonstrates usage of the php_xmlrpc_decode function</h3>
<?php
	include("xmlrpc.inc");

	$f = new xmlrpcmsg('interopEchoTests.whichToolkit', array());
	$c = new xmlrpc_client("/server.php", "phpxmlrpc.sourceforge.net", 80);
	$r = $c->send($f);
	if(!$r->faultCode())
	{
		$v = php_xmlrpc_decode($r->value());
		print "<pre>";
		print "name: " . htmlspecialchars($v["toolkitName"]) . "\n";
		print "version: " . htmlspecialchars($v["toolkitVersion"]) . "\n";
		print "docs: " . htmlspecialchars($v["toolkitDocsUrl"]) . "\n";
		print "os: " . htmlspecialchars($v["toolkitOperatingSystem"]) . "\n";
		print "</pre>";
	}
	else
	{
		print "An error occurred: ";
		print "Code: " . htmlspecialchars($r->faultCode()) . " Reason: '" . htmlspecialchars($r->faultString()) . "'\n";
	}
?>
<hr/>
<em>$Id: which.php 2 2009-03-16 20:22:51Z ggiunta $</em>
</body>
</html>
