<?php

namespace Steganalysis;


class Image extends \ArrayObject {
	private $image;
	private $h;
	private $w;
	private $messageLength = 0;

	public function __construct(&$imagePath) {
		parent::__construct();

		$imageBlob = file_get_contents($imagePath) or die("Can't open file '$imagePath' content" . PHP_EOL);
		$image = imagecreatefromstring($imageBlob) or die("Can't decode image from '$imagePath'" . PHP_EOL);
		unset($imageBlob);

		$this->h = imagesy($image);
		$this->w = imagesx($image);

		$this->image = new \SplFixedArray($this->h);
		for ($y = 0; $y < $this->h; ++$y) {
			$this->image[$y] = new \SplFixedArray($this->w);
			for ($x = 0; $x < $this->w; ++$x) {
				$this->image[$y][$x] = imagecolorat($image, $x, $y);
			}
		}
		imagedestroy($image);
	}

	public function offsetGet($i) {
		return $this->image[$i];
	}

	public function count() {
		return $this->h;
	}

	public function getCapacity() {
		return $this->h * $this->w;
	}

	public function getRate() {
		return $this->messageLength / $this->getCapacity();
	}

	public function flipAll() {
		for ($y = 0; $y < $this->h; ++$y) {
			for ($x = 0; $x < $this->w; ++$x) {
				$this->image[$y][$x] ^= 1;
			}
		}

		$this->messageLength = $this->getCapacity() - $this->messageLength;
	}

	public function addRate($p) {
		$this->messageLength = intval($this->getCapacity() * $p / 100);

		$positions = new \SplFixedArray($this->getCapacity());
		for ($i = 0; $i < $positions->getSize(); ++$i) {
			$positions[$i] = $i;
		}

		for ($i = 0; $i < $this->messageLength; ++$i) {
			$k = rand(0, $positions->getSize() - 1);
			$pos = $positions[$k];
			$positions[$k] = $positions[$positions->getSize() - 1];
			$positions->setSize($positions->getSize() - 1);

			$y = intval($pos / $this->w);
			$x = $pos % $this->w;

			$this->image[$y][$x] ^= rand(0, 1);
		}
	}
} 