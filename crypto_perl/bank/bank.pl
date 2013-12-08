#! /bin/env perl

use Math::BigInt;
use IO::Select;
use IO::Socket;
use strict;
use Rsa;

sub main {
	my ($c, $m, $d);
	open(sec, "<", "sec.key");
	$c = <sec>; $c = Math::BigInt->new($c);
	$m = <sec>; $m = Math::BigInt->new($m);
	open(pub, "<", "pub.key");
	$d = <pub>; $d = Math::BigInt->new($d);
	
	my $bank_cache = 0;
	my $client_cache = 0;

	my %store;

	my $port = 9999;
	my $server = IO::Socket::INET->new (Proto => 'tcp', LocalPort => $port, Listen => SOMAXCONN, Reuse => 1);
	(! $server) && die "Could not setup server - $!\n";
	$server->autoflush(1);
	
	while (my $client = $server->accept()) {
		my $message = $client->getline;
		my ($user, $id, $sgn) = split(/ /, $message);
		$id = Math::BigInt->new($id);
		$sgn = Math::BigInt->new($sgn);
		print("get message from $user(", $user eq 'shop'? $bank_cache:$client_cache,"\$): $id\n");
		if ($user eq "client") {
			if ( defined $store{$id} ) {
				printf("id already in use\n");
				$client->print("id already in use\n");
				$client->close();
				next;
			}
			my $sign = Rsa::enRSA($id, $c, $m);
			$client->print("$sign\n");
			$client_cache -= 1;
			print("$client_cache\$\n");
		} else {
			if (defined $store{$id}) {
				printf("id already in use\n");
				$client->print("id already in use\n");
				$client->close();
				next;
			}
			my $deSign = Rsa::deRSA($sgn, $d, $m);
			if ($deSign != $id) {
				$client->print("fail\n");
				$client->close();
				next;
			}
			$store{$id} = 1;
			$client->print("ok\n");
			$bank_cache += 1;
			printf("$bank_cache\$\n");
		}		
		$client->close();
	}
}

main();
