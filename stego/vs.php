<?php

namespace Steganalysis;

class VsAnalyzer implements iAnalyzer {
	public function analyseRate(Image &$image) {
		return 0.75;
	}
}