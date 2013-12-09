#! /bin/env perl
#use strict;
use bigint;

use Math::BigInt::Random;
use Math::Primality;

my $P = 35742549198872617291353508656626642567;
my $Q = 359334085968622831041960188598043661065388726959079837;
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

sub generatePrime {
	for (my $i = Math::BigInt::Random::random_bigint(min => '4294967296', max => '18446744073709551616');
		 ;
		$i = Math::BigInt::Random::random_bigint(min => '4294967296', max => '18446744073709551616')) {
		if (Math::Primality::is_prime($i)) {
			return $i;
		}
	}
}

sub main {
	my ($mode) = @ARGV;
	
	if ($mode eq "g") {
		my ($mode, $p, $q) = @ARGV;
		if (not defined $p) { $p = generatePrime(); }
		if (not defined $q) { $q = generatePrime(); }

		my ($c, $d, $m) = generateKeys($p, $q);
		open(pub, ">", "pub.key");
		open(sec, ">", "sec.key");
		print pub $d."\n".$m."\n";
		print sec $c."\n".$m."\n";
		return;
    }
	if ($mode eq "s") {
		my ($mode, $file) = @ARGV;
		my ($c, $m);
		open(sec, "<", "sec.key");
		$c = <sec>; $c = Math::BigInt->new($c);
		$m = <sec>; $m = Math::BigInt->new($m);
		my $chSum = Crc32($file);
		open(sign_file, ">", "$file.sign");
		print sign_file enRSA($chSum, $c, $m)."\n";
		return;
    }
	if ($mode eq "t") {
		my ($mode, $file) = @ARGV;
		my ($d, $m);
		open(pub, "<", "pub.key");
		$d = <pub>; $d = Math::BigInt->new($d);
		$m = <pub>; $m = Math::BigInt->new($m);
		my $chSum = Crc32($file);
		open(sign_file, "<", "$file.sign");
		my $sign = <sign_file>; $sign = Math::BigInt->new($sign);
		my $testSum = deRSA($sign, $d, $m);
		print "Sum: ".$chSum."\nFrom sign: ".$testSum."\n";
		if ($testSum == $chSum) { print "OK\n"; } else { print "FAILE\n"; }
		return;
    }
	print "Use: $0 [options]\noptions:\ng [prime 1] [prime 2] - generate public and private key\ns input_file - sign file\nt inputfile - test sign\n";
}
main();
