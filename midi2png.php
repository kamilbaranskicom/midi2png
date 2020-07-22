<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1); 

require_once('vendor/autoload.php');
use Motniemtin\Midi\Midi;

/*

midi2png 2020 rewrite

author: kamilbaranski (http://kamilbaranski.com/)
license: freeware

let's write it once more with Midi class
(uses Motniemtin\Midi package, original from https://valentin.dasdeck.com/midi/)

*/

?><!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>MIDI 2 png</title>
</head>

<body>
	<pre>
	
<?php

error_reporting(E_ALL);

$file = 'margaret_ifiaintgotyou.mid';

if (isset($file)) {

	$midi = new Midi();
	$midi->importMid($file);

	$tt = 0;		// 0: absolute times; 1: delta times

	$tempoBpm = 60000000 / $midi->tempo;
	echo 'tempoBpm = ' . $tempoBpm . "\n";

	foreach ($midi->tracks[1] as $fullMessage) {		// we're interested only in first track
		$words = explode(' ', $fullMessage);
		$message = $words[1];
		if (($message === 'On') || ($message === 'Off') || (($message === 'Par') && ($words[3] === 'c=64'))) {		// we need only note on, note off and sustain.
			list($timestamp, $message, $channel, $n, $v) = explode(' ', $fullMessage);
			echo $fullMessage . "\n";
		}
	}

	//print_r($txt);
};

?>

	</pre>
</body>

</html>