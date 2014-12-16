#! /usr/bin/php
<?php

$shortOpts = 'i:';
$shortOpts .= 't:';
$opts = getopt($shortOpts);

$parr = array_fill_keys(range(0, 100), 0);

for ($i = 0; $i < 100; ++$i) {
	for ($k = 0; $k <= 100; $k += 5) {
		$p = floatval(exec('./steganalysis.php -i ' . $opts['i'] . $i . '.png -t' . $opts['t'] . ' -p' . $k));
		echo $p . PHP_EOL;
		$parr[$k] += $p;
	}
}

$tmpfile = '/tmp/' . $opts['t'] . '.tmp';

file_put_contents($tmpfile, '');

for ($k = 0; $k <= 100; $k += 5) {
	file_put_contents($tmpfile, $k . ' ' . $parr[$k] . PHP_EOL, FILE_APPEND);
}

exec('gnuplot -e "filename=\'' . $tmpfile . '\'; outname=\'' . $opts['t'] . '_p.png\'" p_plot.plg');
