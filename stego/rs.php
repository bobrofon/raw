<?php

namespace Steganalysis;

class RsAnalyzer implements iAnalyzer {
	public function analyseRate(Image &$image) {
		return 0.25;
	}
}