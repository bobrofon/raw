<?php

namespace Steganalysis;

class RsAnalyzer implements iAnalyzer {
	private $mask1 = ['0', '1', '1', '0'];
	private $mask_1 = ['0', '_1', '_1', '0'];

	public function analyseRate(Image &$image) {
		$stat = $this->calcStat($image);
		$image->flipAll();
		$fstat = $this->calcStat($image);

		$k = count($this->mask1);
		$n = $image->getCapacity();

		$dm = ($stat['R+'] - $stat['S+']) * $k / $n;
		$dm_ = ($fstat['R+'] - $fstat['S+']) * $k / $n;
		$dm1 = ($stat['R-'] - $stat['S-']) * $k / $n;
		$dm_1 = ($fstat['R-'] - $fstat['S-']) * $k / $n;

		$a = 2 * ($dm + $dm_);
		$b = $dm_1 - $dm1 - $dm - 3 * $dm_;
		$c = $dm_ - $dm_1;

		$z = solveEq2($a, $b, $c);

		$p = $z / ($z - 0.5);

		return max(0, min(1, $p));
	}

	private function flip1(&$x) {
		$x ^= 1;
	}

	private function flip_1(&$x) {
		++$x;
		$this->flip1($x);
		--$x;
		$x = ($x + 0xFF) % 0xFF;
	}

	private function flip0(&$x) {

	}

	private function discr(array &$u) {
		$sum = 0;
		for ($i = 1; $i < count($u); ++$i) {
			$sum += abs($u[$i] - $u[$i - 1]);
		}
		return $sum;
	}

	private function getGroup(Image &$image, $y, $x) {
		return [$image[$y][$x], $image[$y][$x + 1], $image[$y + 1][$x], $image[$y + 1][$x + 1]];
	}

	private function mapMask(array $group, array &$mask) {
		for ($i = 0; $i < count($mask); ++$i) {
			if ($mask[$i] === '_1') {
				$this->flip_1($group[$i]);
			}
			elseif ($mask[$i] === '1') {
				$this->flip1($group[$i]);
			}
			else {
				$this->flip0($group[$i]);
			}
		}
		return $group;
	}

	private function calcStat(Image &$image) {
		$stat['R+'] = 0;
		$stat['S+'] = 0;
		$stat['R-'] = 0;
		$stat['S-'] = 0;

		for ($y = 0; $y < $image->count() - 1; ++$y) {
			for ($x = 0; $x < $image[$y]->count() - 1; ++$x) {
				$g = $this->getGroup($image, $y, $x);
				$g1 = $this->mapMask($g, $this->mask1);
				$g_1 = $this->mapMask($g, $this->mask_1);

				$d = $this->discr($g);
				$d1 = $this->discr($g1);
				$d_1 = $this->discr($g_1);

				if ($d < $d1) {
					++$stat['R+'];
				}
				elseif ($d > $d1) {
					++$stat['S+'];
				}

				if ($d < $d_1) {
					++$stat['R-'];
				}
				elseif ($d > $d_1) {
					++$stat['S-'];
				}
			}
		}
		return $stat;
	}
}