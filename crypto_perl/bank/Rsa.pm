package Rsa;
use strict;
use bigint;

our @EXPORT = qw( enRSA deRSA gcd pow );

sub gcd {
	my ($a, $b) = ($_[0], $_[1]);
	if ($a == 0) {
		$_[2] = 0;
		$_[3] = 1;
		return $b;
	}
	my ($x1, $y1);
	my $d = gcd($b % $a, $a, $x1, $y1);
	$_[2] = $y1 - ($b / $a) * $x1;
	$_[3] = $x1;
	return $d;
}
sub generateKeys {
	my ($p, $q) = @_;
	my $n = $p * $q;
	my $phi = ($p - 1) * ($q - 1);
	my $c;
	my $d;
	my $k;
	for ($c = $phi - 1; $c > 0 && (gcd($c, $phi, $d, $k) > 1 || ($c * $c) % $phi == 1); --$c) {}
	if ($d <= 0) {
		$k = ((1 - $d + $phi - 1) / $phi);
		$d += $k * $phi;
    }
	return ($c, $d, $n);
}
sub pow {
	my ($a, $b, $m) = @_;
	my $res = 1;
	while ($b > 0) {
		if ($b % 2 == 1) {
			$res = ($res * $a) % $m;
        }
		$a = ($a * $a) % $m;
		$b /= 2;
    }
	return $res;
}
sub enRSA {
	my ($msg, $c, $m) = @_;
	return pow($msg, $c, $m);
}
sub deRSA {
	my ($msg, $d, $m) = @_;
	return pow($msg, $d, $m);
}
sub Crc32 {
	open(in, "<", $_[0]);
	binmode in;
	my $buf;
	while (<in>) {
		$buf .= $_;
    }
	close(in);
    my @crc_table;
    my $crc;
    my ($i, $j);
	for ($i = 0; $i < 256; $i++)
	{
		$crc = $i;
		for ($j = 0; $j < 8; $j++) {
			$crc = $crc & 1 ? ($crc >> 1) ^ 0xEDB88320 : $crc >> 1;
		}
		$crc_table[$i] = $crc;
	};
	$crc = 0xFFFFFFFF;
	$i = 0;
	my $leng = (-s $_[0]);
	while ($leng--) {
	   $crc = $crc_table[($crc ^ vec($buf, $i++, 8)) & 0xFF] ^ ($crc >> 8);
    }
	return ($crc ^ 0xFFFFFFFF);
}
