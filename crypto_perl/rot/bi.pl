#! /bin/env perl

use bigint;

($infile, $outfile, $mode, $blockSize, $shift, $mod) = @ARGV;

if (not defined $infile or not defined $outfile or not defined $mode or not ($mode eq "encode" or $mode eq "decode")) {
	die("Usage: infile  outfile {encode|decode} [ block_size shift mod ]\n");
}
if (not defined $blockSize) {
	$blockSize = 8;
}
if (not defined $shift) {
	$shift = 13;
}
if (not defined $mod) {
	$mod = 1 << $blockSize;
}

open(in, "<", $infile);
open(out, ">", $outfile);
binmode in;
binmode out;

while (<in>) {
	$content .= $_;
}

$blockCount = ((-s $infile) * 8 + $blockSize - 1) / $blockSize;

for ($i = 0; $i < $blockCount; ++$i) {
	$block = vec($content, $i, $blockSize);
	if ($mode eq "encode") {
		$block = ($block + $shift) % $mod;
	} else {
		$block = ($block - $shift) % $mod;
	}
	vec($content, $i, $blockSize) = $block;
}
print out $content;
