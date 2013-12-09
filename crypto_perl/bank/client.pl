#! /bin/env perl

use IO::Socket;
use strict;
use bigint;
use Rsa;

sub main {
	my ($id, $sgn, $r, $d, $n);

	open(pub, "<", "pub.key");
	$d = <pub>; $d = Math::BigInt->new($d);
	$n = <pub>; $n = Math::BigInt->new($n);

	do {
		$r = Math::BigInt->new(int(rand(1000000)));
	} while (Rsa::gcd($r, $n, $id, $sgn) != 1);
	my $mr = $id;
	while ($mr < 0) {
		$mr += $n;
	}
	$mr = $mr % $n;

	$id = Math::BigInt->new(int(rand(1000000)));
	$id *= 0xFFFFFFFF;
	$id += 0xDEADBEEF;

	my $m = ($id * Rsa::pow($r, $d, $n)) % $n;

	my $host = "127.0.0.1";
	my $port = 9999;

	my $sock = IO::Socket::INET->new(
		PeerAddr  => $host,
		PeerPort  => $port,
		Proto => 'tcp');
	die "Could not create socket: $!\n" unless $sock;
	$sock->print("client $m\n");
	$sgn = <$sock>; $sgn = Math::BigInt->new($sgn);
	$sgn = ($sgn * $mr) % $n; 
	print("$id $sgn\n");
}

main();
