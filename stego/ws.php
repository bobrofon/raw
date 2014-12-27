<?php

	namespace Steganalysis;

	class WsAnalyzer implements iAnalyzer {
		private $image;
		private $filter;
		private $w;

		private function calcW() {
			$s = [];

			for ($y = 2; $y < $this->image->count() - 2; ++$y) {
				for ($x = 2; $x < $this->image[$y]->count() - 2; ++$x) {
					$sx = (
						$this->image[$y][$x - 1] +
						$this->image[$y][$x + 1] +
						$this->image[$y][$x - 2] +
						$this->image[$y][$x + 2] +
						$this->image[$y - 1][$x] +
						$this->image[$y - 1][$x - 1] +
						$this->image[$y - 1][$x + 1] +
						$this->image[$y + 1][$x] +
						$this->image[$y + 1][$x - 1] +
						$this->image[$y + 1][$x + 1] +
						$this->image[$y - 2][$x - 2] +
						$this->image[$y - 2][$x] +
						$this->image[$y - 2][$x + 2] +
						$this->image[$y + 2][$x - 2] +
						$this->image[$y + 2][$x] +
						$this->image[$y + 2][$x + 2]
					) / 16;

					$t =
						($this->image[$y][$x - 1] - $sx) * ($this->image[$y][$x - 1] - $sx) +
						($this->image[$y][$x + 1] - $sx) * ($this->image[$y][$x + 1] - $sx) +
						($this->image[$y][$x - 2] - $sx) * ($this->image[$y][$x - 2] - $sx) +
						($this->image[$y][$x + 2] - $sx) * ($this->image[$y][$x + 2] - $sx) +
						($this->image[$y - 1][$x] - $sx) * ($this->image[$y - 1][$x] - $sx) +
						($this->image[$y - 1][$x - 1] - $sx) * ($this->image[$y - 1][$x - 1] - $sx) +
						($this->image[$y - 1][$x + 1] - $sx) * ($this->image[$y - 1][$x + 1] - $sx) +
						($this->image[$y + 1][$x] - $sx) * ($this->image[$y + 1][$x] - $sx) +
						($this->image[$y + 1][$x - 1] - $sx) * ($this->image[$y + 1][$x - 1] - $sx) +
						($this->image[$y + 1][$x + 1] - $sx) * ($this->image[$y + 1][$x + 1] - $sx) +
						($this->image[$y - 2][$x - 2] - $sx) * ($this->image[$y - 2][$x - 2] - $sx) +
						($this->image[$y - 2][$x] - $sx) * ($this->image[$y - 2][$x] - $sx) +
						($this->image[$y - 2][$x + 2] - $sx) * ($this->image[$y - 2][$x + 2] - $sx) +
						($this->image[$y + 2][$x - 2] - $sx) * ($this->image[$y + 2][$x - 2] - $sx) +
						($this->image[$y + 2][$x] - $sx) * ($this->image[$y + 2][$x] - $sx) +
						($this->image[$y + 2][$x + 2] - $sx) * ($this->image[$y + 2][$x + 2] - $sx);
					$t = $t / 16;
					$s[] = $t;
					unset($sx);
					unset($t);
				}
			}
			foreach ($s as $w) {
				$this->w[] = 1 / (1 + $w);
			}
			$t = array_sum($this->w);
			foreach($this->w as &$w) {
				$w /= $t;
			}
		}

		private function calcFilter($sy = null, $sx = null, $fy = null, $fx = null) {
			if (is_null($sy)) $sy = 2;
			if (is_null($sx)) $sx = 2;
			if (is_null($fy)) $fy = $this->image->count() - 3;
			if (is_null($fx)) $fx = $this->image[0]->count() - 3;

			$keys = str_split('abcde');

			foreach(str_split('abcde?') as $c1) foreach(str_split('abcde?') as $c2) $p[$c1.$c2] = 0;

			for ($y = $sy; $y <= $fy; ++$y) {
				for ($x = $sx; $x <= $fx; ++$x) {
					$lp['a'] = floatval($this->image[$y][$x - 1] + $this->image[$y][$x + 1]);
					$lp['b'] = floatval($this->image[$y - 1][$x - 1] + $this->image[$y - 1][$x + 1] + $this->image[$y + 1][$x - 1] + $this->image[$y + 1][$x + 1]);
					$lp['c'] = floatval($this->image[$y - 1][$x] + $this->image[$y + 1][$x]);
					$lp['d'] = floatval($this->image[$y][$x - 2] + $this->image[$y][$x + 2]);
					$lp['e'] = floatval($this->image[$y - 2][$x - 2] + $this->image[$y - 2][$x] + $this->image[$y - 2][$x + 2] + $this->image[$y + 2][$x - 2] + $this->image[$y + 2][$x] + $this->image[$y + 2][$x + 2]);
					$lp['?'] = floatval(-$this->image[$y][$x]);

					foreach(str_split('abcde?') as $c1) foreach(str_split('abcde?') as $c2) $p[$c1.$c2] += $lp[$c1] * $lp[$c2];
					unset($lp);
				}
			}
			foreach(str_split('abcde') as $c) {
				foreach(str_split('abcde?') as $t) $dp[$t] = 0;
				foreach($p as $k => $v) {
					if ($c == $k[0]) {
						$dp[$k[1]] += $v;
					}
					elseif ($c == $k[1]) {
						$dp[$k[0]] += $v;
					}
				}
				$dp[$c] *= 2;
				$np = [];
				foreach(str_split('abcde') as $n) $np[] = $dp[$n];
				$np[] = -$dp['?'];
				$gp[] = $np;
			}

			$ans = array_fill(0, count($keys), 0);
			$this->gauss($gp, $ans);
			$this->filter = [];
			for ($i = 0; $i < count($keys); ++$i) {
				$this->filter[$keys[$i]] = $ans[$i];
			}
		}

		public function analyseRate(Image &$image) {
			$this->image = $image;

			$this->calcFilter();
			$this->calcW();

			$p = 0;

			for ($y = 2; $y < $image->count() - 2; ++$y) {
				for ($x = 2; $x < $image[$y]->count() - 2; ++$x) {
					$i = ($y - 2) * ($image[$y]->count() - 4) + $x - 2;
					$p += $this->w[$i] * ($image[$y][$x] - ($image[$y][$x] ^ 1)) * ($image[$y][$x] - $this->pred($y, $x));
				}
			}
			$p *= 2;

			return min(max(0, $p), 1);
		}

		private function pred($y, $x) {
			$c = 0;
			$c += ($this->image[$y][$x - 1] + $this->image[$y][$x + 1]) * $this->filter['a'];
			$c += ($this->image[$y - 1][$x - 1] + $this->image[$y - 1][$x + 1] + $this->image[$y + 1][$x - 1] + $this->image[$y + 1][$x + 1]) * $this->filter['b'];
			$c += ($this->image[$y - 1][$x] + $this->image[$y + 1][$x]) * $this->filter['c'];
			$c += ($this->image[$y][$x - 2] + $this->image[$y][$x + 2]) * $this->filter['d'];
			$c += ($this->image[$y - 2][$x - 2] + $this->image[$y - 2][$x] + $this->image[$y - 2][$x + 2] + $this->image[$y + 2][$x - 2] + $this->image[$y + 2][$x] + $this->image[$y + 2][$x + 2]) * $this->filter['e'];

			return min(max(0, $c), 0xFF);
		}

		private function conf($i) {
			return (($this->image->count() - 4) * ($this->image[0]->count() - 4)) - 1;
		}

		private function w($i) {
			return 1 / (1 + $this->conf($i));
		}

		private function gauss(array &$a, array &$ans) {
			$n = count($a);
			$m = count($a[0]) - 1;
			$EPS = 1e-9;

			$where = array_fill(0, $m, -1);
			for ($col=0, $row=0; $col<$m && $row<$n; ++$col) {
				$sel = $row;
				for ($i=$row; $i<$n; ++$i)
					if (abs ($a[$i][$col]) > abs ($a[$sel][$col]))
						$sel = $i;
				if (abs ($a[$sel][$col]) < $EPS)
					continue;
				for ($i=$col; $i<=$m; ++$i)
					list ($a[$sel][$i], $a[$row][$i]) = [$a[$row][$i], $a[$sel][$i]];
				$where[$col] = $row;

				for ($i=0; $i<$n; ++$i)
					if ($i != $row) {
						$c = $a[$i][$col] / $a[$row][$col];
						for ($j=$col; $j<=$m; ++$j)
							$a[$i][$j] -= $a[$row][$j] * $c;
				}
				++$row;
			}

			$ans = array_fill(0, $m, 0);
			for ($i=0; $i<$m; ++$i)
				if ($where[$i] != -1)
					$ans[$i] = $a[$where[$i]][$m] / $a[$where[$i]][$i];
			for ($i=0; $i<$n; ++$i) {
				$sum = 0;
				for ($j=0; $j<$m; ++$j)
					$sum += $ans[$j] * $a[$i][$j];
				if (abs ($sum - $a[$i][$m]) > $EPS)
					return 0;
			}

			for ($i=0; $i<$m; ++$i)
				if ($where[$i] == -1)
					return INF;
			return 1;
		}
	}