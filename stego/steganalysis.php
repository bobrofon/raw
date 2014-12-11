#! /usr/bin/php

<?php

include_once('image.php');
include_once('analyzer.php');
include_once('rs.php');
include_once('sp.php');
include_once('vs.php');

class App {
	private $imagePath = '';
	private $p = 0;
	private $analyzer;

	public function __construct() {
		$this->parseOpts();
	}

	private function getUsage() {
		$message = 'Use: ' . $GLOBALS['argv'][0] . ' -i path [-p percent] [-t {rs|sp|vs}] [-h]' . PHP_EOL;
		$message .= '-i - image path' . PHP_EOL;
		$message .= '-p - embedding_rate. Integer value from 0 to 100 (%) [default=0]' . PHP_EOL;
		$message .= '-t - analysis type [default=rs]' . PHP_EOL;
		$message .= '-h - print this message and exit' . PHP_EOL;

		return $message;
	}

	private function  parseOpts() {
		$shortOpts = 'i:';
		$shortOpts .= 'p::';
		$shortOpts .= 't::';
		$opts = getopt($shortOpts) or die($this->getUsage());

		isset($opts['i']) and !empty($opts['i']) or die($this->getUsage());
		$this->imagePath = $opts['i'];

		if (isset($opts['p'])) {
			ctype_digit(strval($opts['p'])) and $opts['p'] >= 0 and $opts['p'] <= 100 or die($this->getUsage());
			$this->p = $opts['p'];
		}

		if (isset($opts['t'])) {
			if ($opts['t'] === 'rs') {
				$this->analyzer = new \Steganalysis\RsAnalyzer();
			}
			elseif ($opts['t'] === 'sp') {
				$this->analyzer = new \Steganalysis\SpAnalyzer();
			}
			elseif ($opts['t'] === 'vs') {
				$this->analyzer = new \Steganalysis\VsAnalyzer();
			}
			else {
				die($this->getUsage());
			}
		}
		else {
			$this->analyzer = new \Steganalysis\RsAnalyzer();
		}

	}

	public function run() {
		$image = new Steganalysis\Image($this->imagePath);
		$image->addRate($this->p);

		$p = $this->analyzer->analyseRate($image);

		echo round($p, 8) . PHP_EOL;
	}
}

$app = new App();
$app->run();
