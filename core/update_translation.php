#!/usr/bin/php -q
<?php

if (empty ($argv[1]) || empty ($argv[2]) || empty($argv[3])) {
	echo "\n\nUsage: update_translation.php <APP_PATH> <TRANSLATION_FILE.csv> <LANG>\n\n";
	exit;
}

$appPath = $argv[1];
$file = $argv[2];
$lang = $argv[3];

define('APP_LEVEL', 'COMMAND');

require __DIR__."/bootstrap.php";

$token = new \Token($lang);

$fp = fopen($file, 'r');

echo "Begin: \n";

while ($t = fgetcsv($fp)) {
	if (!is_numeric($t[0])) {
		continue;
	}
	echo $t[1];
	if (!isset ($t[3])) {
		echo ": Error: {$t[2]}\n";
		continue;
	}
	$token->update($t[1], $lang, $t[3]);
	echo "                      \r";
}

