<?php

namespace Steganalysis;


interface iAnalyzer {
	public function analyseRate(Image &$image);
}

function solveEq2($a, $b, $c) {
	if ($a == 0) {
		if ($b == 0) {
			if ($c == 0) {
				return 1;
			}
			else {
				return 0;
			}
		}
		else {
			return -$c / $b;
		}
	}
	else {
		$d = $b * $b - 4 * $a * $c;

		if ($d < 0) {
			return 1;
		}
		elseif ($d == 0) {
			return -$b / (2 * $a);
		}
		else {
			$x1 = (-$b - sqrt($d)) / (2 * $a);
			$x2 = (-$b + sqrt($d)) / (2 * $a);

			return min($x1, $x2);
		}
	}
}