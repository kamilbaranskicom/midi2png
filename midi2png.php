<?php
/*

midi2png 2021 rewrite

author: kamilbaranski (http://kamilbaranski.com/)
license: freeware

let's write it once more with Midi class
(uses Motniemtin\Midi package, original from https://valentin.dasdeck.com/midi/)

*/

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>MIDI 2 png</title>
</head>

<body>
	<pre>
<?php


require_once('vendor/autoload.php');
use Motniemtin\Midi\Midi;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$file = isset($_GET['file']) ? $_GET['file'] : 'margaret_ifiaintgotyou.mid';
$fps = isset($_GET['fps']) ? $_GET['fps'] : 30;

if (isset($file)) {
	extractNoteOnsForMuzykuj($file, $fps);
};


/*
 * prints "$frame $noteName [$velocity]\n" for each [Note On] on 1st track.
 * written for muzykuj.com
 */
function extractNoteOnsForMuzykuj($file, $fps = 30) {
	$midi = new Midi();
	$midi->importMid($file);
	$settings = getSettingsFromMidi($midi);
	$settings['fps'] = $fps;
	$noteList = array_map(
		function ($noteName) {
			return strtr($noteName, array('s' => '#'));
		},
		$midi->getNoteList()
	);  																								// the original array is Cs4, but we like C#4

	foreach ($midi->tracks[1] as $fullMessage) {														// we're interested only in first track
		if (preg_match('/([0-9]+) (On) ch=([0-9]+) n=([0-9]+) v=([0-9]+)/', $fullMessage, $array)) {	// we're interested in NoteOn only
			list($fullMessage, $timestamp, $message, $channel, $noteNumber, $velocity) = $array;
			if ($velocity !== '0') {																	// some systems use [NoteOn with velocity=0] as [NoteOff].
				echo getFrameFromTimestamp($timestamp, $settings) . " $noteList[$noteNumber] [$velocity]\n";
			}
		}
	}
};

function getSettingsFromMidi($midi) {
	$settings['tempo'] = $midi->tempo;
	$settings['tempoBpm'] = 60000000 / $settings['tempo'];
	$settings['timebase'] = $midi->timebase;
	// echo 'tempoBpm = ' . $tempoBpm . "\n";
	// echo 'timebase = ' . $timebase . "\n";		// often 480
	return $settings;
}

function getFrameFromTimestamp($timestamp, $settings) {
	$seconds = ($timestamp / $settings['timebase']) * ($settings['tempo'] / 1000000);
	// $timestamp=$timestamp.' = '.number_format($seconds, 16);
	$frame = round($seconds * $settings['fps']);
	return $frame;
};






// *********************************
// below is a draft; rewrite it!
// *********************************
// 
function midi2png($file) {
	$midi = new Midi();
	$midi->importMid($file);
	$noteList = $midi->getNoteList();

	$tt = 0;		// 0: absolute times; 1: delta times

	$tempoBpm = 60000000 / $midi->tempo;
	echo 'tempoBpm = ' . $tempoBpm . "\n";

	foreach ($midi->tracks[1] as $fullMessage) {		// we're interested only in first track
		$words = explode(' ', $fullMessage);
		$message = $words[1];
		if (($message === 'On') || ($message === 'Off') || (($message === 'Par') && ($words[3] === 'c=64'))) {		// we need only note on, note off and sustain.
			list($timestamp, $message, $channel, $n, $v) = explode(' ', $fullMessage);
			$regexp = '/([0-9]+) ([A-Za-z]+) ch=([0-9]+) [c|n]=([0-9]+) v=([0-9]+)/';
			preg_match($regexp, $fullMessage, $array);

			list($fullMessage, $timestamp, $message, $channel, $n, $v) = $array;
			if (($message === 'On') and ($v !== '0')) {			// because some systems use [NoteOn with velocity=0] as [NoteOff].
				$noteName = $noteList[$n];
				echo $timestamp . ' ' . $noteName . ' [' . $v . ']' . "\n";
			}
			//echo $fullMessage . "\n";
		}
	}

	//print_r($txt);
};

?>
</pre>
</body>

</html>