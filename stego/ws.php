<?php

namespace Steganalysis;

class WsAnalyzer implements iAnalyzer {
	private $image;

	public function analyseRate(Image &$image) {
		$this->image = $image;

		$p = 0;

		for ($y = 2; $y < $image->count() - 2; ++$y) {
			for ($x = 2; $x < $image[$y]->count() - 2; ++$x) {
				$i = ($y - 2) * ($image[$y]->count() - 4) + $x - 2;
				$p += $this->w($i) * ($image[$y][$x] - ($image[$y][$x] ^ 1)) * ($image[$y][$x] - $this->pred($y, $x));
			}
		}
		$p *= 2;

		return min(max(0, $p), 1);
	}

	private function pred($y, $x) {
		$c = 0;
		$c += ($this->image[$y][$x - 1] + $this->image[$y][$x + 1]) * 16;
		$c += ($this->image[$y - 1][$x - 1] + $this->image[$y - 1][$x + 1] + $this->image[$y + 1][$x - 1] + $this->image[$y + 1][$x + 1]) * 8;
		$c += ($this->image[$y - 1][$x] + $this->image[$y + 1][$x]) * 4;
		$c += ($this->image[$y][$x - 2] + $this->image[$y][$x + 2]) * 2;
		$c += $this->image[$y - 2][$x - 2] + $this->image[$y - 2][$x] + $this->image[$y - 2][$x + 2] + $this->image[$y + 2][$x - 2] + $this->image[$y + 2][$x] + $this->image[$y + 2][$x + 2];
		$c /= 82;

		return min(max(0, $c), 0xFF);
	}

	private function conf($i) {
		return $this->image->getCapacity() - 1;
	}

	private function w($i) {
		return 1 / (1 + $this->conf($i));
	}
}