<?php

// Not running Composer for this demo app.
// require_once(dirname(__FILE__)."/../vendor/autoload.php");

spl_autoload_register(function ($className) {
	$libDir = dirname(__FILE__)."/..";

	$paths = [
		"models",
		"lib"
	];

	foreach ($paths as $p) {

		$file = $libDir . "/". $p ."/" . $className . ".php";
		if (is_readable($file)) {
			include($file);
			return;
		}
		$file = $libDir . "/". $p ."/" . strtolower($className) . ".php";
		if (is_readable($file)) {
			include($file);
			return;
		}
	}
});

// Make sure we know you did the thing
Request::log();
