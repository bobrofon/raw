#! /bin/env perl

use IO::Socket;
use strict;
use bigint;
use Rsa;

sub main {
	my ($id, $sgn) = @ARGV;
	die "Use: shop id sign" if (not defined $id or not defined $sgn);
	$id = Math::BigInt->new($id);
	$sgn = Math::BigInt->new($sgn);

	my $host = "127.0.0.1";
	my $port = 9999;

	my $sock = IO::Socket::INET->new(
		PeerAddr  => $host,
		PeerPort  => $port,
		Proto => 'tcp');
	die "Could not create socket: $!\n" unless $sock;
	$sock->print("shop $id $sgn\n");
	print (<$sock>);
}

main();
