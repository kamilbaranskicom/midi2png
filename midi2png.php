<?php

/*

midi2png 2020 rewrite

author: kamilbaranski (http://kamilbaranski.com/)
license: freeware

let's write it once more with midiClass (https://valentin.dasdeck.com/midi/)

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

// $file=$_GET['file'];
$file = 'margaret_ifiaintgotyou.mid';

if (isset($file)) {
	require(__DIR__ . '/midiClass/classes/midi.class.php');

	$midi = new Midi();
	$midi->importMid($file);

	$tt = 0;		// 0: absolute times; 1: delta times

	$tempoBpm = 60000000 / $midi->tempo;
	echo 'tempoBpm = ' . $tempoBpm . "\n";

	foreach ($midi->tracks[1] as $fullMessage) {		// we're interested only in first track
		list($timestamp, $message, $channel, $n, $v) = explode(' ', $fullMessage);
		if (($message === 'On') || ($message === 'Off') || (($message === 'Par') && ($n === 'c=64'))) {		// we need only note on, note off and sustain.
			echo $fullMessage . "\n";
		}
	}



	//print_r($txt);


};
?>

	</pre>
</body>

</html>