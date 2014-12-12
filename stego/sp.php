<?php

namespace Steganalysis;

class SpAnalyzer implements iAnalyzer {
	public function analyseRate(Image &$image) {
		list($c, $o, $e) = $this->calcStat($image);

		for ($i = -2; $i <= 2; ++$i) {
			$p2 = $c[$i] - $c[$i + 1];
			$p1 = 2 * ($e[2 * $i + 2] + $o[2 * $i + 2] - 2 * $e[2 * $i + 1] + 2 * $o[2 * $i + 1] - $e[2 * $i] - $o[2 * $i]);
			$p0 = 4 * ($e[2 * $i + 1] - $o[2 * $i + 1]);

			$roots[] = solveEq2($p2, $p1, $p0);
		}

		$p = max($roots);

		return max(min(1, $p), 0);
	}

	private function getPairs(Image &$image, $y, $x) {
		$pairs = [];
		if ($x + 1 < $image[$y]->count()) $pairs[] = [$image[$y][$x], $image[$y][$x + 1]];
		if ($x - 1 >= 0) $pairs[] = [$image[$y][$x], $image[$y][$x - 1]];
		if ($y + 1 < $image->count()) $pairs[] = [$image[$y][$x], $image[$y + 1][$x]];
		if ($y - 1 >= 0) $pairs[] = [$image[$y][$x], $image[$y - 1][$x]];

		return $pairs;
	}

	private function calcStat(Image &$image) {
		$c = array_fill_keys(range(-127, 127), 0);
		$o = array_fill_keys(range(-255, 254), 0);
		$e = array_fill_keys(range(-254, 255), 0);

		for ($y = 0; $y < $image->count(); ++$y) {
			for ($x = 0; $x < $image[$y]->count(); ++$x) {
				$pairs = $this->getPairs($image, $y, $x);
				foreach ($pairs as list($x1, $x2)) {
					$i = intval($x2 / 2) - intval($x1 / 2);
					++$c[$i];
					$i = $x2 - $x1;
					if ($x1 % 2 == 0) {
						++$e[$i];
					}
					else {
						++$o[$i];
					}
				}
			}
		}
		return [$c, $o, $e];
	}
}