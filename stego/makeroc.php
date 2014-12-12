#! /usr/bin/php
<?php

$shortOpts = 'i:';
$shortOpts .= 'p:';
$shortOpts .= 't:';
$opts = getopt($shortOpts);

$empty = [];
$full = [];

for ($i = 0; $i < 30; ++$i) {
	if ($i & 1) {
		$p = floatval(exec('./steganalysis.php -i ' . $opts['i'] . $i . '.png -t' . $opts['t']));
		$empty[] = $p;
		echo $p . PHP_EOL;
	}
	else {
		$p = floatval(exec('./steganalysis.php -i ' . $opts['i'] . $i . '.png -t' . $opts['t'] . ' -p' . $opts['p']));
		$full[] = $p;
		echo $p . PHP_EOL;
	}
}

file_put_contents('/tmp/ololo', '');

for ($k = 0; $k <= 1; $k += 0.001) {
	$tpr = 0;
	$fpr = 0;
	foreach ($empty as $p) if ($p >= $k) ++$fpr;
	foreach ($full as $p) if ($p >= $k) ++$tpr;

	$tpr /= count($full);
	$fpr /= count($empty);

	file_put_contents('/tmp/ololo', $fpr . ' ' . $tpr . PHP_EOL, FILE_APPEND);
}

exec('gnuplot -e "filename=\'/tmp/ololo\'; outname=\'' . $opts['t'] . '.png\'" plot.plg');