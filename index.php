<?php
/*

midi2png

author: kamilbaranski [http://kamilbaranski.com]
license: freeware

- dirty old version written in ~2014
- chord recognition assumes the lowest note is root which is very bad :)
- images looks OK, for example - https://www.youtube.com/watch?v=G-UN8dNdubI

todo:
- rewrite whole

*/


	// debug variables ----------------------------------------------------------------------------------------------------
	$version='0.1b';
	date_default_timezone_set('Europe/Warsaw');
	$date=date('d M Y, H:m:s',filemtime('index.php'));
	$readme='Thanks for testing! Send any ideas and thoughts to kamilbaranski@gmail.com. Algorithm version '.$version.' ('.$date.'). For now, it always assumes the lowest note as root ;)';
	$debugtext=false;
	$showpictures=true;
	$reportvlv=false;
	$debugpodajakord=true;
	$chord=true;
	$dontflush=false;
	// --------------------------------------------------------------------------------------------------------------------
	$plik='margaret_ifiaintgotyou.mid';
	$pngDirectory='png/';
	
	$szerokoscbialego=24;
	$wysokoscbialego=100;
	$offsetx=15;
	$offsety=15;
	
	$wysokoscczarnego=60;
	$szerokoscczarnego=$szerokoscbialego/3*2;
	$coilesaczarne=$szerokoscbialego;
	//$pcx=12;
	$pcx=$szerokoscbialego-($szerokoscczarnego/2);
	$pcy=0;
	$uciacbialy=2;
	$uciacczarny=2;
	
	$wielkoscczcionkigora=8;
	$malaczcionka=7;

	$dzwieki=array('C','C#','D','D#','E','F','F#','G','G#','A','A#','B');		// potrzebne do zwrocnazwe($nr) i zwrocnazwezoktawa($nr)

	$przerobbialy=array(
												1 => 1,			// c
												2 => 3,			// d
												3 => 5,			// e
												4 => 6,			// f
												5 => 8,			// g
												6 => 10,		// a
												7 => 12);		// b
	$przerobczarny=array(
												1 => 2,			// c#
												2 => 4,			// d#
												3 => null,
												4 => 7,			// f#
												5 => 9,			// g#
												6 => 11,		// a#
												7 => null);

	$pressed=array();
	$sustain=false;
		
	$tracknr=0;
	$iloscklatek=30;				//fps w obrazkach

	if ($chord) {
		$wyjsciowyobrazek=imagecreate(1280,151);
	} else {
		$wyjsciowyobrazek=imagecreate(1280,131);
	};
	$white=imagecolorallocate($wyjsciowyobrazek,255,255,255);
	$presw=imagecolorallocate($wyjsciowyobrazek,10,220,85);
	$presb=imagecolorallocate($wyjsciowyobrazek,10,220,85);
	$pressusw=imagecolorallocate($wyjsciowyobrazek,150,255,180);
	$pressusb=imagecolorallocate($wyjsciowyobrazek,5,110,45);
	$black=imagecolorallocate($wyjsciowyobrazek,0,0,0);
	$silver=imagecolorallocate($wyjsciowyobrazek,140,140,140);
	
	$metatajpy=array(
		00 => 'Sequence Number',
		01 => 'Text Event',
		02 => 'Copyright Notice',
		03 => 'Sequence/Track Name',
		04 => 'Instrument Name',
		05 => 'Lyric',
		06 => 'Marker',
		07 => 'Cue Point',
		32 => 'MIDI Channel Prefix',
		47 => 'End of Track',
		81 => 'Set Tempo (in microseconds per MIDI quarter-note)',
		84 => 'SMPTE Offset',
		88 => 'Time Signature',
		89 => 'Key Signature',
		127 => 'Sequencer Specific Meta-Event'
	);
	//$debugtext=false;
	
	for($a=0;$a<52;$a++) {					// $a to białe klawisze. a=0 to A-1, a=23 to C3, a=51 to C7
	  narysujbialyklawisz($wyjsciowyobrazek,$a,false,zwrocnumermidi($a,false));
	 };
	 
	 for($a=0;$a<51;$a++) {
	  narysujczarnyklawisz($wyjsciowyobrazek,$a,false,zwrocnumermidi($a,true));
	 }

	 $a=imagettfbbox(8,0,'arial.ttf',$readme);
	 imagettftext($wyjsciowyobrazek,8,0,1280-$a[2]-5,151-$a[3]-3,$black,'arial.ttf',$readme);
	//$debugtext=true;
	 
	//testujpodajakord();
	 
	 
	if (isset($_POST['nutki'])) {
		for($i=0;$i<=108;$i++) {
			if (isset($_POST['nutki'.$i])) {
				$pressed[$i]=$_POST['nutki'.$i];
			} else {
			
			};
		};
		/* echo 'PR: '.join(array_keys($pressed),' ');
		echo podajakord($pressed); */
		ob_start();
		narysuj(0,true);
	} else {
		rozczytajplik($plik);
	};
	
	
	
	function narysujbialyklawisz($i,$nr,$pressed=false,$dopisz=false) {
		global $white,$black,$presw,$pressusw,$silver;
		global $szerokoscbialego,$wysokoscbialego,$offsetx,$offsety,$uciacbialy;
		global $wielkoscczcionkigora,$malaczcionka;
		global $debugtext;
		
		$nrmidi=zwrocnumermidi($nr,false);
	//report('$i='.$i);
		
		$nazwy=array('C','D','E','F','G','A','B');
		$oktawa=-1;
		//$nr=$nr+5;
		for($n=$nr+5;$n>6;$n=$n-7) { $oktawa++; };
		$nazwa=$nazwy[$n];
		if (($n==6) and ($oktawa<6)) { $mniejszy=-1; } else { $mniejszy=0; };
		
		$x=$offsetx+($nr*$szerokoscbialego);
		$y=$offsety;
		
		$points=array(
			$x+0,$y+0,																														// 7
			$x+0,$y+$wysokoscbialego-$uciacbialy,																	// 1a
			$x+0+$uciacbialy,$y+$wysokoscbialego,																	// 1b
			$x+$szerokoscbialego+$mniejszy-$uciacbialy,$y+$wysokoscbialego,				// 3a
			$x+$szerokoscbialego+$mniejszy,$y+$wysokoscbialego-$uciacbialy,				// 3b
			$x+$szerokoscbialego+$mniejszy,$y+0																		// 9
		);
	
		if ($pressed) {
			if ($pressed==2) {
			$kolor=$pressusw;
			$kolortekstu=$silver;
			} else {
			$kolor=$presw;
			$kolortekstu=$black;
			};
			$rozmiary=imagettfbbox($wielkoscczcionkigora,0,'arial.ttf',$nazwa);
			$szerokosc=$rozmiary[2];
			imagettftext($i,$wielkoscczcionkigora,0,$x+$szerokoscbialego/2-$szerokosc/2,$y-3,$kolortekstu,'arial.ttf',$nazwa);
		} else {
			$kolor=$white;
		};
		
		$ilpunktow=count($points)/2;
		imagefilledpolygon($i, $points, $ilpunktow, $kolor);
		imagepolygon($i, $points, $ilpunktow, $black);
		if ($debugtext) {
			imagettftext($i,$malaczcionka,0,$x+2,$y+$wysokoscbialego-2,$black,'arial.ttf',zwrocnazwezoktawa($nrmidi)); // $nazwa.$oktawa);
			imagettftext($i,$malaczcionka,0,$x+2,$y+$wysokoscbialego-2-12,$black,'arial.ttf',$nr);
			if ($dopisz) {
				imagettftext($i,$malaczcionka,0,$x+2,$y+$wysokoscbialego-2-24,$black,'arial.ttf',$dopisz);
			};
		} else {
			if ($nazwa=='C') {
				imagettftext($i,$malaczcionka,0,$x+2,$y+$wysokoscbialego-1,$black,'arial.ttf',$nazwa.$oktawa);
			};
		};
	};
	function narysujczarnyklawisz($i,$nr,$pressed=false,$dopisz=false) {
		global $white,$black,$presb,$presw,$pressusb,$silver;
		global $szerokoscczarnego,$wysokoscczarnego,$pcx,$pcy,$offsetx,$offsety;
		global $uciacczarny;
		global $coilesaczarne;
		global $wielkoscczcionkigora,$malaczcionka;
		global $debugtext;
		
		$nrmidi=zwrocnumermidi($nr,true);
		
		$nazwy=array('C#','D#',false,'F#','G#','A#',false);
		$czof=array(-1,1,false,-1,0,1,false);
		$oktawa=-1;
		//$nr=$nr+5;
		for($n=$nr+5;$n>6;$n=$n-7) { $oktawa++; };
		$nazwa=$nazwy[$n];
		$czof=$czof[$n]*($szerokoscczarnego/8);
		//$czof=0;
		
		if ($nazwa) {
			$x=$offsetx+($nr*$coilesaczarne);
			$y=$offsety;
		
			$points=array(
				$x+0+$pcx+$czof,$y+0+$pcy,																												// 7
				$x+0+$pcx+$czof,$y+$wysokoscczarnego+$pcy-$uciacczarny,														// 1a
				$x+0+$pcx+$czof+$uciacczarny,$y+$wysokoscczarnego+$pcy,														// 1b
				$x+$szerokoscczarnego+$pcx+$czof-$uciacczarny,$y+$wysokoscczarnego+$pcy,					// 3a
				$x+$szerokoscczarnego+$pcx+$czof,$y+$wysokoscczarnego+$pcy-$uciacczarny,					// 3b
				$x+$szerokoscczarnego+$pcx+$czof,$y+0+$pcy																				// 9
			);
		
			if ($pressed) {
				if ($pressed==2) {
					$kolor=$pressusb;
					//report('$dopisz='.$dopisz);
					$kolortekstu=$silver;
				} else {
					$kolor=$presb;
					$kolortekstu=$black;
				};
				$rozmiary=imagettfbbox($wielkoscczcionkigora,0,'arial.ttf',$nazwa);
				$szerokosc=$rozmiary[2];
				imagettftext($i,$wielkoscczcionkigora,0,$x+$pcx+$szerokoscczarnego/2-$szerokosc/2+1,$y-3,$kolortekstu,'arial.ttf',$nazwa);
			} else {
				$kolor=$black;
			};
	
			$ilpunktow=count($points)/2;
			imagefilledpolygon($i, $points, $ilpunktow, $kolor);
			imagepolygon($i, $points, $ilpunktow, $black);
			if ($debugtext) {
				imagettftext($i,$malaczcionka,0,$x+16+$czof,$y+$wysokoscczarnego-3,$white,'arial.ttf',zwrocnazwezoktawa($nrmidi));	// $nazwa.$oktawa);
				imagettftext($i,$malaczcionka,0,$x+17+$czof,$y+$wysokoscczarnego-3-25,$white,'arial.ttf',$nr);
				if ($dopisz) {
					imagettftext($i,$malaczcionka,0,$x+16+$czof,$y+$wysokoscczarnego-3-45,$white,'arial.ttf',$dopisz);
				};
			};
		};
	};
	function report($txt,$level=0,$small=false) {	
		global $dontflush;
		if ($small) { $smallst='<small>'; $smallen='</small>'; } else { $smallst=''; $smallen=''; };
		$level=str_repeat(' ',$level);
		echo '* '.$level.$smallst.$txt.$smallen.'<br />';
		if (!$dontflush) {
			ob_flush();
		};
	};
	function unp($type,$var) {		// unpacks $var variable with $type format, and returns $result[$type] (assumes one type at a time)
		$tmp=unpack($type,$var);
		return $tmp[substr($type,1)];
	};
	function vlv($txt) {				// returns variable bit value (as in SMF) and length to cut from txt
		global $reportvlv;
		$vlv=0;
		$bajt='';
		$bint=0;
		$a=0;
		$exit=false;
		do {
		$bajt=substr($txt,$a,1);
		$bint=unp('Cint',$bajt);
		//report('$a='.$a.'. BINT: '.$bint);
		if ($bint>127) {									// ostatni bit ustawiony (będzie więcej bajtów)
			$bint=$bint-128;								// więc usuwamy
			$newvlv=$vlv*128+$bint*128;						// nowy vlv
			
			// $newvlv=$vlv*127+$bint;
			if ($reportvlv) report('Found byte '.($bint+128).'. It isnt the last byte, so were changing VLV from '.$vlv.' to '.$newvlv.'.',2,true);
			$vlv=$newvlv;
			$exit=false;
		} else { 											// ostatni bit nieustawiony -> koniec
			$vlv=$vlv+$bint;
			if ($reportvlv) report('Found byte '.$bint.'. It is the last byte, so were breaking loop; VLV='.$vlv.'.',2,true);
			$exit=true;
			//break(1);
		};
		$a++;
		} while ($exit!=true);
		$length=$a;
		//if ($reportvlv) report('Finished VLV routine with VALUE='.$vlv.' and LENGTH='.$length.' bytes.',2);
		return array(
					'value' => $vlv,
					'length' => $length
					);
	};
	function analysechunk($chunk) {
		global $reportvlv,$division,$tempo,$pressed,$sustain,$iloscklatek,$metatajpy;
		$tick=0;
		$pressedkeys=0;
		report('Starting analysis of '.strlen($chunk).' bytes long chunk',2);
		report('TEMPO='.$tempo.'; DIVISION='.$division.'; ');
		$bylyzmianywnutkach=true;
		$seconds=0;
		$klatka=0;
		
		for($a=0;$a<strlen($chunk);$a) {
			set_time_limit(5);
			$vlv=vlv(substr($chunk,$a));
			$delta=$vlv['value'];
			$a=$a+$vlv['length'];
		
			//report('$delta='.$delta.';');
			// if ($delta>0) {					// zaraz przesuniemy się w czasie utworu
			$nowaklatka=round($seconds*$iloscklatek);
			if ($klatka!=$nowaklatka) {
				if ($bylyzmianywnutkach) {		// były jakieś zmiany do rysunku? (czyli eventy note on/off/sustain)
					//report('BYLY ZMIANY, więc rysujemy '.$klatka);
					$bylyzmianywnutkach=false;
					if (narysuj($klatka)===false) {			// (poprzednia $klatka, nowa poniżej się podstawia)
						return false;
					};
					$klatka=$nowaklatka;
				};
			};
			
			$tick=$tick+$delta;
			$ticks=str_pad($tick,8,'.',STR_PAD_LEFT);
			$seconds=($tick/$division)*($tempo/1000000);
			$ticks=$ticks.' = '.str_pad($seconds,16,'0',STR_PAD_RIGHT);
			//report('SEC'.$seconds);
		
			$eventtype=unp('Cint',substr($chunk,$a,1));
			if ($eventtype==255) {																	// Meta Event
				// meta_event = 0xFF + <meta_type> + <v_length> + <event_data_bytes>
				$a++;
				$metatype=unp('Cint',substr($chunk,$a,1));
				if ($reportvlv) {
					report($ticks.' : Found META event type '.$metatype.' ('.$metatajpy[$metatype].')...',3);
				};
				$a++;
				$vlv=vlv(substr($chunk,$a));
				$metalength=$vlv['value'];
				$a=$a+$vlv['length'];
				$value=substr($chunk,$a,$metalength);
				if ($reportvlv) {
					report('   ...size '.$metalength.' bytes and value='.substr($chunk,$a,$metalength));
				} else {
					report($ticks.' : Found META event type '.$metatype.' ('.$metatajpy[$metatype].') with value '.$value);
				};
				if ($metatype==81) {
					$tempo=unp('Nlong',chr(0).$value);		// w standardzie tu są 3 bajty, więc dodamy sobie czwarty i zrobimy unp
					report('	Found TEMPO = '.$tempo.' microseconds per MIDI quarter-note (that is '.(60000000/$tempo).' bpm)');
				};
				$a=$a+$metalength;
			} else if (($eventtype==240) or ($eventtype==247)) {										// System Exclusive Event
				// sysex_event = 0xF0 + <data_bytes> 0xF7 or sysex_event = 0xF7 + <data_bytes> 0xF7
				report($ticks.' : Found SYSEX event! Not tested!',3);
				do {
				$a++;
				} while (substr($chunk,$a,1)!=chr(247));			// <-- sprawdzić kiedyś, czy to jest dobrze, bo wg innej dokumentacji nie jest! (vlv needed?)
				
			} else {																					// MIDI event
				// czy nowy status?
				if ($eventtype>127) {			// zmieniamy status, więc zwiększamy $a (bo data byte będzie dalej!)
					$a++;
				} else {						// ten sam status co ostatnio, więc nie zmieniamy $a!! (bo będzie tu pierwszy data byte!)
					$eventtype=$lasteventtype;
				};
				if (($eventtype>=128) and ($eventtype<=143)) {										// note off (binary 1000nnnn)
					$note=unp('Cint',substr($chunk,$a,1));
					$a++;
					$vel=unp('Cint',substr($chunk,$a,1));
					$a++;
					$bylyzmianywnutkach=true;
					$pressedkeys--;
					report($ticks.' : NOTE OFF '.$note.' ('.zwrocnazwezoktawa($note).') @ vel='.$vel);
					if ($sustain) {
						$pressed[$note]=2;
					} else {
						unset($pressed[$note]);
					};
				
				} else if (($eventtype>=144) and ($eventtype<=159)) {								// note on (binary 1001nnnn)
					$note=unp('Cint',substr($chunk,$a,1));
					$a++;
					$vel=unp('Cint',substr($chunk,$a,1));
					$a++;
					$bylyzmianywnutkach=true;
					if ($vel>0) {
						$pressedkeys++;
						report($ticks.' : NOTE ON '.$note.' ('.zwrocnazwezoktawa($note).') @ vel='.$vel);
						$pressed[$note]=1;
					} else {
						$pressedkeys--;
						report($ticks.' : NOTE OFF '.$note.' ('.zwrocnazwezoktawa($note).') @ vel='.$vel);
						if ($sustain) {
							$pressed[$note]=2;
						} else {
							unset($pressed[$note]);
						};
					};
				} else if (($eventtype>=176) and ($eventtype<=191)) {									// control change
					$controllernr=unp('Cint',substr($chunk,$a,1));
					$a++;		// controller nr
					$controllervalue=unp('Cint',substr($chunk,$a,1));
					$a++;		// value
					if ($controllernr==64) {																// sustain
					if ($controllervalue>0) {						// naciśnięcie sustain
						report($ticks.' : SUSTAIN ON @ val='.$controllervalue);
						$sustain=true;
					} else {										// puszczenie sustain
						report($ticks.' : SUSTAIN OFF @ val='.$controllervalue);
						$sustain=false;
						foreach($pressed as $arkey => $arval) {
							if ($arval==2) {
								unset($pressed[$arkey]);
								$bylyzmianywnutkach=true;
							};
						};
					};
					};
				} else {
				report('Other event ('.$eventtype.'); not tested yet.');
				if (($eventtype>=160) and ($eventtype<=175)) {										// polyphonic key pressure
					$a++;		// key
					$a++;		// pressure
				} else if (($eventtype>=192) and ($eventtype<=207))	{								// program change
					$a++;		// program nr
				} else if (($eventtype>=208) and ($eventtype<=223))	{								// channel pressure (aftertouch)
					$a++;		// pressure
				} else if (($eventtype>=224) and ($eventtype<=239))	{								// pitch wheel change
					$a++;		// value*1
					$a++;		// value*128
				} else if ($eventtype>=240) {						// system common messages/system real-time messages (nie powinny wystąpić)
					$a++;
				};
				};
				// narysuj($seconds);
				$lasteventtype=$eventtype;
			};
		};
	
		if ($bylyzmianywnutkach) {		// były jakieś zmiany do rysunku na końcu?
			report('BYLY ZMIANY, więc rysujemy '.$seconds);
			$bylyzmianywnutkach=false;
			narysuj($seconds*iloscklatek);			// (poprzednie $seconds, nowe jest jeszcze nieobliczone)
		};
	  return true;
	};  
	function createcolor($pic,$c1,$c2,$c3) {
		//get color from palette
		$color = imagecolorexact($pic, $c1, $c2, $c3);
		if($color==-1) {
			//color does not exist...
			//test if we have used up palette
			if(imagecolorstotal($pic)>=255) {
				//palette used up; pick closest assigned color
				$color = imagecolorclosest($pic, $c1, $c2, $c3);
			} else {
				//palette NOT used up; assign new color
				$color = imagecolorallocate($pic, $c1, $c2, $c3);
			}
		}
		return $color;
	}
	function narysuj($klatka,$wyslijnastdout=false) {
		global $wyjsciowyobrazek,$pressed,$iloscklatek,$white,$black,$presw,$presb,$pressusw,$pressusb,$silver;
		global $przerobbialy,$przerobczarny;
		global $ODNOSNIKI;
		global $debugtext,$chord;
		global $dontflush;
		global $pngDirectory;
	  //echo '  '.$sec.' : ';
	  //$nazwapliku=round($sec*$iloscklatek).'.png';
		if ($wyslijnastdout) {
			$nazwapliku=NULL;
			ob_clean();
			ob_start(); //null, 0, PHP_OUTPUT_HANDLER_CLEANABLE);
			$dontflush=true;
			header('Content-Type: image/png;');
		} else {
			$nazwapliku=$pngDirectory.$klatka.'.png';
		};
	  //echo $nazwapliku;
	  
	  // imagedestroy($i);
	  
		$i = _clone_img_resource($wyjsciowyobrazek);
		$white=imagecolorallocate($i,255,255,255);
		$presw=imagecolorallocate($i,10,220,85);
		$presb=imagecolorallocate($i,10,220,85);
		$pressusw=imagecolorallocate($i,150,255,180);
		$pressusb=imagecolorallocate($i,5,110,45);
		$black=imagecolorallocate($i,0,0,0);
		$silver=imagecolorallocate($i,140,140,140);

	  $pr='';
		ksort($pressed);
	  foreach($pressed as $nr => $key) { $pr=$pr.$nr.' '; };
	  if ($debugtext) {
			//imagettftext($i,10,0,3,12,$black,'arial.ttf','fn='.round($sec*$iloscklatek).' ** PR='.$pr);
			imagettftext($i,10,0,3,12,$black,'arial.ttf','fn='.$klatka.' ** PR='.$pr);
	  };
		//echo 'fn='.round($sec*$iloscklatek).' ** PR='.$pr.'<br />';
		echo '#'.$klatka.' ** PR='.$pr.'<br />';

		narysuj_te_ktore_trzeba($i);
		
/*		for($a=0;$a<52;$a++) {					// $a to białe klawisze. a=0 to A-1, a=23 to C3, a=51 to C7
			$oktawa=1;
			for($t=$a+6;$t>7;$t=$t-7) {
				$oktawa++;
			};
			$tenklawisz=$oktawa*12+$przerobbialy[$t]-1;
		
			if (isset($pressed[$tenklawisz])) {
				narysujbialyklawisz($i,$a,$pressed[$tenklawisz],$tenklawisz);
			} else {
				narysujbialyklawisz($i,$a,false,$tenklawisz);
			};
	  };

	  for($a=0;$a<51;$a++) {
			$oktawa=1;
			for($t=$a+6;$t>7;$t=$t-7) {
				$oktawa++;
			};
			$tenklawisz=$oktawa*12+$przerobczarny[$t]-1;
			if (isset($pressed[$tenklawisz])) {
				narysujczarnyklawisz($i,$a,$pressed[$tenklawisz],$tenklawisz);
			} else {
				narysujczarnyklawisz($i,$a,false,$tenklawisz);
			};
	  }
	*/
	
		if ($chord) {
			$akord=podajakord($pressed);
			imagettftext($i,15,0,10,140,$black,'arial.ttf',$akord);
			report('^--- AKORD = '.$akord,70);
		};
	
		if ($wyslijnastdout) {
		  ob_end_clean();
		};
	
	  imagepng($i,$nazwapliku,0,PNG_NO_FILTER);
	  //report('narysowałem '.$nazwapliku);
	  $ODNOSNIKI=$ODNOSNIKI.'<br /><img src="'.$nazwapliku.'" style="margin-bottom:10px;" />';
	  // report('<img src="'.$nazwapliku.'" />');
	  //if ($nazwapliku=='2752.png') { return false; };				// margaret: 406, 710, 2752
		imagedestroy($i);
	};
	
	function zwrocnazwe($nr) {
	  $tmp=informacjeonutcemidi($nr);
		return $tmp['dzwiek'];
	};
	function zwrocnazwezoktawa($nr) {
	  $tmp=informacjeonutcemidi($nr);
		return $tmp['dzwiek'].$tmp['oktawa'];
	};
  function czarny($nr) {
	  $tmp=informacjeonutcemidi($nr);
		return $tmp['czarny'];
	};
	function bialy($nr) {
	  $tmp=informacjeonutcemidi($nr);
		return $tmp['bialy'];
	};
	function nkdk($nr) {
	  $tmp=informacjeonutcemidi($nr);
		return $tmp['nkdk'];
	};
  function informacjeonutcemidi($nr) {
		global $dzwieki;
		$oktawa=-2;
		for($i=$nr;$i>11;$i=$i-12) {
			$oktawa=$oktawa+1;
		};
		if (($i==1)||($i==3)||($i==6)||($i==8)||($i==10)) {
				$czarny=true;
				$bialy=false;
				$numerklawiszadanegokoloru=array_search($i,array(1,3,0,6,8,10,0))+($oktawa+1)*7-5;
		} else {
				$bialy=true;
				$czarny=false;
				
				$numerklawiszadanegokoloru=array_search($i,array(0,2,4,5,7,9,11))+($oktawa+1)*7-5;
		};
    return array(
					'oktawa' => $oktawa,
					'dzwiek' => $dzwieki[$i],
					'czarny' => $czarny,
					'bialy' => $bialy,
					'nkdk' => $numerklawiszadanegokoloru);
	};
	function zwrocnumermidi($nrklaw,$czarny) {
		global $przerobbialy,$przerobczarny;
		$oktawa=1;
		if (!$czarny) {					// biały
		  for($i=$nrklaw+5;$i>6;$i=$i-7) {
			  $oktawa=$oktawa+1;
			};
			$numermidi=$oktawa*12+$przerobbialy[$i+1]-1;
			//report('bialy nrklaw='.$nrklaw.' ('.$numermidi.')');
		} else {								// czarny
		  for($i=$nrklaw+5;$i>6;$i=$i-7) {
			  $oktawa=$oktawa+1;
			};
			if ($przerobczarny[$i+1]) {
			  $numermidi=$oktawa*12+$przerobczarny[$i+1]-1;
			  //report('czarny nrklaw='.$nrklaw.' ('.$numermidi.')');
			} else {
			  //report('czarny nrklaw='.$nrklaw.' (NIE DA SIĘ)');
				return false;
			};
		};
		return $numermidi;
	};
	
	
	function rozczytajplik($plik) {
		global $showpictures,$division,$sustain,$pressed,$tracknr,$iloscklatek;
		if (!file_exists($plik)) {
			report('File doesn'."'".'t exist. Try our <a href="betatest.php">betatest</a>!');
			return false;
		};
		
		echo '<pre>';
		if ($showpictures) {
			report('<a href="#odnosniki">keyboard pictures below</a>');
		};
		
		$m=file_get_contents($plik);
		// header: "MThd" + <header_length> + <format> + <n> + <division>
		$header=substr($m,0,4);
		if ($header=='MThd') {
			report('Header is OK (MThd)');
		} else {
			report('Header is wrong: "'.$header.'"');
		};
		
		$headersize=unp('Nlong',substr($m,4,4));
		if ($headersize==6) {
			report('Headersize is OK (6)');
		} else {
			report('Headersize is wrong: "'.$headersize.'"');
		};
	
		$format=unp('nint',substr($m,8,2));
		report('Format is: '.$format);
		
		$trackchunkscount=unp('nint',substr($m,10,2));
		report('There will be '.$trackchunkscount.' track chunks in this file.');
		
		$division=unp('nint',substr($m,12,2));												// TICKS PER QUARTER NOTE!
		report('Division is: '.$division.'. (Negative division is unsupported yet.)');
		report('----------------------------');
	
		for($n=14;$n<strlen($m);$n) {
			$chunkname=substr($m,$n,4);
			report('Chunk '.$chunkname.' found!');
			$length=unp('Nlong',substr($m,$n+4,4));
			report('It is '.$length.' bytes long.');
			$chunk=substr($m,$n+4+4,$length);
			if ($chunkname=='MTrk') {
				$tracknr++;
				report('Starting analysis of chunk MTrk nr '.$tracknr.'...');
				if (analysechunk($chunk)===false) {
					report('Breaking analysis.');
				  break;
				};
				report('Finishing analysis of chunk.');
				report('----------------------------');
			} else {
				report('Omitting chunk.');
			};
			$n=$n+4+4+$length;
		};
		report('Reached end of file! Well done!');
		
		if ($showpictures) {
			report('Here follows pictures');
			echo '<div style="background-color:black;color:white;padding:20px;text-align:center;">';
			echo '<div style="width:1280px;margin:auto;" />';
			echo '<a id="odnosniki"></a>(1280px)';
			echo '<hr style="width:1280px;color:yellow" />';
		
			$arr=scandir('.');
			//var_dump($arr);
			$druga=array();
			
			for($i=0;$i<count($arr);$i++) {
				//echo $i.' <br />';
				if ((substr($arr[$i],-4)=='.png') or (substr($arr[$i],-4)=='.PNG')) {
					array_push($druga,intval(substr($arr[$i],0,-4)));
				};
			};
			sort($druga);
			//var_dump($druga);
			unset($arr);
			$arr=$druga;
		
			foreach ($arr as $imgname) {
				echo '<br /><div style="text-align:left;margin:0;padding:0;">'.$imgname.'.png</div>';
				echo '<img src="'.$imgname.'.png" style="margin-bottom:0px;" />';
			};
			
			// echo $ODNOSNIKI;
			echo '</div>';
			echo '</div>';
		};
	};
	function _clone_img_resource($img) {
	
		//Get width from image.
		$w = imagesx($img);
		//Get height from image.
		$h = imagesy($img);
		//Get the transparent color from a 256 palette image.
		$trans = imagecolortransparent($img);
	
		//If this is a true color image...
		if (imageistruecolor($img)) {
	
			$clone = imagecreatetruecolor($w, $h);
			imagealphablending($clone, false);
			imagesavealpha($clone, true);
		}
		//If this is a 256 color palette image...
		else {
	
			$clone = imagecreate($w, $h);
	
			//If the image has transparency...
			if($trans >= 0) {
	
				$rgb = imagecolorsforindex($img, $trans);
	
				imagesavealpha($clone, true);
				$trans_index = imagecolorallocatealpha($clone, $rgb['red'], $rgb['green'], $rgb['blue'], $rgb['alpha']);
				imagefill($clone, 0, 0, $trans_index);
			}
		}
	
		//Create the Clone!!
		imagecopy($clone, $img, 0, 0, 0, 0, $w, $h);
	
		return $clone;
	};
	
	
	function narysuj_te_ktore_trzeba($i) {
	  global $pressed;
		// $pressed[$key] ==
		//										1 - normalnie,
		//										2 - sustain,
		//										0 lub unset - w ogóle,
		//										-1 - nie ma (ale jest sąsiad, więc narysuj!)

		//var_dump($pressed);
	  $ktore=array();
	  foreach($pressed as $klawisz => $stan) {
		  $nazwa=zwrocnazwe($klawisz);
			array_push($ktore,$klawisz);
			switch($nazwa) {
				case 'C'; case 'D'; case 'F'; case 'G'; case 'A';
				  if ($klawisz<108) {
						array_push($ktore,$klawisz+1);
					};
			};
			
			switch($nazwa) {
				case 'D'; case 'E'; case 'G'; case 'A'; case 'B';
				  if ($klawisz>21) {
						array_push($ktore,$klawisz-1);
					};
			};

		};
		array_unique($ktore);
		sort($ktore);
		//echo '<blockquote style="border:1px solid #808080;background-color:#e0e0e0;">';
		//var_dump($ktore);
		//echo '</blockquote>';
		foreach($ktore as $nrmidi) {
			if(bialy($nrmidi)) {
				narysujbialyklawisz($i,nkdk($nrmidi),$pressed[$nrmidi]);
			};
		};
		foreach($ktore as $nrmidi) {
		  if(czarny($nrmidi)) {
				narysujczarnyklawisz($i,nkdk($nrmidi),isset($pressed[$nrmidi])?$pressed[$nrmidi]:false);
			};
		};
		
	};

  function testujpodajakord() {
		global $pressed;
		//$pressed[24]=true;			// C0
		//$pressed[36]=true;			// C1
		//$pressed[45]=true;			// A0
		//$pressed[48]=true;			// C1
		//$pressed[52]=true;			// E2
		//$pressed[51]=true;			// Eb2
		//$pressed[55]=true;			// G2
		//$pressed[56]=true;			// G#2
		$start=55;
		$pressed[$start]=true;
		report('GLOBAL ROOT: '.zwrocnazwezoktawa($start));
		for($i=$start+1;$i<100;$i++) {
			$pressed[$i]=true;
			report('SKŁADNIK: '.zwrocnazwezoktawa($i));
			echo '<h1>'.podajakord($pressed).'</h1>';
			$pressed[$i]=false;
		};

	};
	function podajakord($pressed) {
		global $dzwieki, $debugpodajakord;
		
		$root=-1;
		for($i=0;$i<=108;$i++) {
			if((isset($pressed[$i]))&&($pressed[$i])) {				// assuming root ;) (v1)
				$root=$i;
				break;
			};
		};
		if ($root==-1) { report('no root'); return false; };
	
	
		//report('ROOT='.$root.'; flooring ;)');
		$rootoctave=floor($root/12)-2;
		$rootkey=$root%12;
		if ($debugpodajakord) {
			report('assuming ROOT='.$root.', key='.$dzwieki[$rootkey].' ('.$rootkey.'), octave='.$rootoctave);
		};
	
	$juzdobrze=false;
	$start=$root+1;
	do {
		// szukamy pierwszego składnika od dołu innego niż pryma
		$pierwszyskladnik=-1;
		for($i=$start;$i<=108;$i++) {
			if (((isset($pressed[$i]))&&($pressed[$i])) and (($i-$rootkey)%12!=0)) {		// składnik inny niż pryma
																															//*************************************************************************
				$pierwszyskladnik=$i;																	// DOPISAĆ: jeśli w całej oktawie pierwszegoskładnika jest tylko kwinta, to szukajmy dalej...
				break;																								// ******************************************************************************************
			};
		};
		if (($i<108) && (($pierwszyskladnik%12-$rootkey==7) or ($pierwszyskladnik%12-$rootkey==-5))) {// uwaga, jest to kwinta! sprawdzamy, czy może tylko ona jest w tej oktawie
			report('Uwaga, mamy kwintę!');
			for ($i=$pierwszyskladnik+1;$i<=min(108,$pierwszyskladnik+4);$i++) {
				if (((isset($pressed[$i]))&&($pressed[$i])) and (($i-$rootkey)%12!=0)) {
					report('Ale mamy też inny składnik, więc jest luz.');
					$juzdobrze=true;
					$oldpressed=(isset($pressed[$pierwszyskladnik+12]))?$pressed[$pierwszyskladnik+12]:0;
					$pressed[$pierwszyskladnik+12]=true;				// tymczasowe rozwiązanie, bo $pressed[] to global!
																											// trzeba tu zwrócić uwagę, czy nie jesteśmy poza klawiaturą?
					break;
				};
			};
			if (!$juzdobrze) {
				report('Ooo! Teraz była tylko KWINTA!');							// no dobra, tylko to robi źle w margaret w 2128.png: "Dm9sus4(11)" zamiast "Dm11" lub "Dm9(11)"
				report('PIERWSZYSKLADNIK: '.$pierwszyskladnik.'; ROOTKEY: '.$rootkey);
				$start=$i;
			};
		} else {
			$juzdobrze=true;
		};

	} while ($juzdobrze!=true);
		if ($pierwszyskladnik==-1) { report('no pierwszyskladnik'); return false; };
		
		$odjac=floor(($pierwszyskladnik-$rootkey)/12)*12+$rootkey;
		/*if ($rootkey) {																		//
			$odjac=$odjac-(12-$rootkey);											//
		};*/																								//
		report('Pierwszy składnik: '.$i.'. A więc odejmujemy '.$odjac.'.');
		
		$swokt=array();											// składniki w oktawie. żeby je policzyć chociażby.
	/*	for($i=0;$i<=11;$i++) {							// (init)
			$swokt[$i]=false;
		};
	*/
		
		$s=array();					// $s = SKLADNIKI...     0=root (np. c), do 21= trzynastka (np. a); na razie używamy na oko
		for($i=$odjac;$i<=108;$i++) {
			if (isset($pressed[$i])&&($pressed[$i])) {
				$s[$i-$odjac]=true;
				$ostatniskladnik=$i-$odjac;
				$swokt[($i-$odjac)%12]=true;
			} else {
				$s[$i-$odjac]=false;
			};
		};
	
		$swokt[0]=true;
		if ($debugpodajakord) {
			//var_dump($swokt);
		};
		
		if (count($swokt)<3) {
			if ($debugpodajakord) { report('Mniej niż 3 składniki. Wychodzimy.'); };
			return false;
		} else {
			ksort($swokt);
			if ($debugpodajakord) {
				report('Mamy '.count($swokt).' składników ('.join(array_keys($swokt),', ').')! Fajnie, idziemy dalej!');
				$dddcalosc='';
				foreach(array_keys($swokt) as $dddzw) {
					$dddcalosc.=zwrocnazwe($dddzw+$rootkey).' ';
				};
				report('Czyli: '.$dddcalosc);
			};
		};
		
		if ($s[29]) { $s[17]=true; $swokt[5]=true; };									// 11 się przydaje do np. Cm11
		if ($s[20]) { $s[8]=true; $swokt[8]=true; $s[20]=false; };		// 13b to jednak 5# ;)
		if ($s[22]) { $s[10]=true; $swokt[10]=true; $s[22]=false; };	// 7 zawsze będzie 7, a nie 7+okt. ;)
		if ($s[16] and !$s[3]) { $s[4]=true; $swokt[4]=true; $s[16]=false; };		// a tercja durowa to tercja durowa PRAWIE zawsze, prawda?
		if ((!$s[4]) and (($s[15]) or ($s[27]))) { $s[3]=true; $s[15]=false; $s[27]=false; };			// jeśli nie ma durowej tercji, to jest moll, nie 9#
		if ($s[26]) { $s[14]=true; $swokt[2]=true; $s[26]=false; };		// 9 woła gdzieś z oddali
	/*	for($i=count($s);$i>=max($ostatniskladnik,21+1);$i--) {				// na razie składniki > 21 nas nie interesują
			unset($s[$i]);
		};
	*/
		
		if ($debugpodajakord) {
			//var_dump($s);
		};
		
		//global $add2,$minor,$major,$fourth,$fourthsharp,$fifthflat,$fifth,$fifthsharp,$sixthflat,$sixth,$seventh,$majorseventh,$ninthflat,$ninth,$ninthsharp,$eleven,$elevensharp,$thirteenth;
		
		$add2         = false;
		$minor        = false;
		$major        = false;
		$fourth       = false;
		$fourthsharp  = false;
		$fifthflat    = false;
		$fifth        = false;
		$fifthsharp   = false;
		$sixthflat    = false;
		$sixth        = false;
		$seventh      = false;
		$majorseventh = false;
		$ninthflat    = false;
		$ninth        = false;
		$ninthsharp   = false;
		$elevenflat   = false;
		$eleven       = false;
		$elevensharp  = false;
		$thirteenth   = false;
		
		
		// $s[0] nie ma znaczenia i obecnie przyjmuje różną, nieistotną wartość.
		if ($s[1]) {						// db
			$s[1]=false;
			$s[13]=true;
			$ninthflat=true;
		};
		if ($s[2]) {						// d
			if ($s[10]) {					// (bb)
				$s[2]=false;
				$s[14]=true;				// 9
			} else {
				$add2=true;
			};
		};
		if ($s[4] or (!$s[3] && ($s[16] or ($s[28] && !$s[15])))) {						// e; tylko jeśli nie ma eb oktawę niżej (hint: "c eb g bb d e")
			$major=true;
		};
		if (($s[3]) or ($s[15])) {						// eb
			if($major) {
				$s[3]=false;
				$s[15]=true;				// 9#
			} else {
				$minor=true;
				$s[15]=false;
			};
		};
		if ($s[5]) {						// f
			$fourth=true;
		};
		if ($s[7]) {						// g
			$fifth=true;
			if ($s[6]) {					// f#
				if ($s[5]) {
					$s[6]=false;
					$s[18]=true;			// 11#
				} else {
					$fourthsharp=true;
				};
			};
		} else {
			if ($s[6]) {
				$fifthflat=true;
			};
		};
		if ($s[8]) {						// g#
			if ($major) {
				$fifthsharp=true;
			} else {
				$sixthflat=true;
			};
		};
		if ($s[10]) {						// bb
			$seventh=true;
		};
		if ($s[9]) {						// a
			if ($seventh and ($ninth || $major)) {					// $ninth||$major, bo akord typu "c g a bb c eb f g", to powinien być Cm7(11)add6, a nie Cm13...
				$s[9]=false;
				$s[21]=true;				// 13
			} else {
				$sixth=true;
			};
		};
		if ($s[11] || $s[23]) {						// b
			$majorseventh=true;
		};
		if ($s[13]) {						// db
			$ninthflat=true;
		};
		if ($s[14]) {						// d
			$ninth=true;
		};
		if ($s[15]) {						// eb
			$ninthsharp=true;
		};
		if ($s[16] && $minor) { // e, jeśli oktawę niżej było eb
			$elevenflat=true;
		
		};
		if ($s[17]) {						// f
			if ($major or $minor) {
				$eleven=true;
			} else {
				$fourth=true;
			};
		};
		if ($s[18]) {						// f#
			$elevensharp=true;
		};
		if ($s[21]) {						// a
			$thirteenth=true;
		};
		
		if (($ninth) && (!$seventh) && (!$majorseventh) && (!$elevensharp) && (!$thirteenth)) {
			$ninth=false;
			$add2=true;
			$s[14]=false;
			$s[2]=true;
		};

/*
		if (($minor) && ($sixthflat))						// tu sprawdzić, czy gdyby był to akord durowy (sixthflat=>pryma durowego),
																						// to byłby to najprostszy akord, ewent. maj7/add2/maj9
																						// jeżeli tak, wstawić tu taki akord na tercji.
																						// przykład: "b f# g a d" (Bm7(b6)) => "GMaj9/B"
		};
		if (($fourth) && ($seven) && ($ninth))
																						// tu sprawdzić, czy gdyby był to akord durowy ($seven=>pryma durowego),
																						// to byłby to najprostszy akord, (?)
																						// jeżeli tak, chyba wstawić tu taki akord na sekundzie.
																						// przykład: "c bb d f" ("C9sus4") -> "Bb/C" ?
																						// ?? CZY MOŻE LEPIEJ NIE?		
		};
		if (!$fifth && !$fifthsharp && !$fifthflat && $major && $sixth) {
																						// sprawdzić resztę składników i spróbować akord 6m/3
																						// przykład: "c e a" -> "Am/C"
		};
		if (!$fifth && !$fifthsharp && !$fifthflat && $minor && $sixthflat) {
																						// sprawdzić resztę składników i spróbować akord 6b/3b
																						// przykład: "c eb ab" -> "Ab/C"
		};
*/

		if (!$debugpodajakord) {
			report('add2         <b>'.$add2         .'</b>');
			report('minor        <b>'.$minor        .'</b>');
			report('major        <b>'.$major        .'</b>');
			report('fourth       <b>'.$fourth       .'</b>');
			report('fourthsharp  <b>'.$fourthsharp  .'</b>');
			report('fifthflat    <b>'.$fifthflat    .'</b>');
			report('fifth        <b>'.$fifth        .'</b>');
			report('fifthsharp   <b>'.$fifthsharp   .'</b>');
			report('sixthflat    <b>'.$sixthflat    .'</b>');
			report('sixth        <b>'.$sixth        .'</b>');
			report('seventh      <b>'.$seventh      .'</b>');
			report('majorseventh <b>'.$majorseventh .'</b>');
			report('ninthflat    <b>'.$ninthflat    .'</b>');
			report('ninth        <b>'.$ninth        .'</b>');
			report('ninthsharp   <b>'.$ninthsharp   .'</b>');
			report('elevenflat   <b>'.$elevenflat   .'</b>');
			report('eleven       <b>'.$eleven       .'</b>');
			report('elevensharp  <b>'.$elevensharp  .'</b>');
			report('thirteenth   <b>'.$thirteenth   .'</b>');
		};
		
		$chord=$dzwieki[$rootkey];
		
		if ($minor) {
			$chord.='m';
		};
		if ($thirteenth) {
			if ($fourth) {
				$chord.='sus4/';
			};
			if ($seventh) { 
				$chord.='13';
			} else {
				$chord.='MA13';
			};
			if ($elevenflat) { $chord.='(b11)'; };
			if ($elevensharp) { $chord.='(#11)'; };
			if ($ninthflat) { $chord.='(b9)'; };
			if ($ninthsharp) { $chord.='(#9)'; };
			if ($fifthflat) { $chord.='(b5)'; };
			if ($fifthsharp) { $chord.='(#5)'; };
		} else if ($elevensharp) {
			if ($majorseventh) {
			
			} else {
				if ($majorseventh) {
					$chord.='Maj7';
					if ($ninth) { $chord.='/9'; };
				} else if ($ninth) {
					$chord.='9';
				};
				if ($ninthflat) { $chord.='(b9)'; };
				if ($ninthsharp) { $chord.='(#9)'; };
				if ($eleven) { $chord.='(11)'; };
				$chord.='(#11)';					// hm. tylko? załóżmy: c e g bb d f#
				if ($elevenflat) { $chord.='(b11)'; };
			};
/*		} else if ($eleven) {
			if ($majorseventh) {
			
			} else {
				if ($majorseventh) {
					$chord.='Maj7';
					if ($ninth) { $chord.='/9'; };
				} else if ($ninth) {
					$chord.='9';
				};
				if ($sixthflat) { $chord.='(b5)'; };
				if ($seventh) { $chord.='7'; };
				if ($ninthflat) { $chord.='(b9)'; };
				if ($ninthsharp) { $chord.='(#9)'; };
				$chord.='(11)';					// hm. tylko? załóżmy: c eb g bb d f
			};
*/
		} else if (($ninth) || ($ninthflat) || ($ninthsharp)) {
				if ($majorseventh) {																						// DOROBIĆ: Cm9(Maj7) = np. "c eb g bb b d"
						if ($ninth) {
							$chord.='Maj9';
						} else {
							$chord.='Maj7';
						};
				} else if ($ninth) {
					$chord.='9';
					if ($fourth) {
						$chord.='sus4';
					} else {
						if ((!$major) && (!$minor)) {
							$chord.='(no3)';
						};
					};
				};
				if ($fifthflat) { $chord.='(b5)'; };
				if ($fifthsharp) { $chord.='(#5)'; };
				if ($ninthflat) { $chord.='(b9)'; };
				if ($ninthsharp) { $chord.='(#9)'; };
				if ($sixth) { $chord.='/6'; };
				if ($eleven) { $chord.='(11)'; };
				if ($elevenflat) { $chord.='(b11)'; };
		} else if ($seventh) {
			$chord.='7';
			if ($fourth) {
				if ((!$major) && (!$minor)) {
					$chord.='sus4';
				} else {
					$chord.='add4';
				};
			} else {
			  if ((!$major) and (!$minor)) {
				  $chord.='(no3)';
				};
			};
			if ($fifthflat) { $chord.='(b5)'; };
			if ($fifthsharp) { $chord.='(#5)'; };
			if ($sixthflat) { $chord.='(b6)'; };
			if ($eleven) { $chord.='(11)'; };
			if ($elevenflat) { $chord.='(b11)'; };
			if ($sixth) { $chord.='add6'; };
			if ($fourthsharp) { $chord.='(#4)'; };
			// alteracje inne?
		} else if ($majorseventh) {
			if ($sixth) { $chord.='6'; };
			$chord.='Maj7';
			if ($fifthflat) { $chord.='(b5)'; };
			if ($fifthsharp) { $chord.='(#5)'; };
			if ($eleven) { $chord.='(11)'; };
			if ($eleven) { $chord.='(11)'; };
			if ($elevenflat) { $chord.='(b11)'; };
			if ($fourthsharp) { $chord.='(#4)'; };
			if ((!$major) and (!$minor)) { $chord.='(no3)'; };
			if ($add2) { $chord.='add2'; };
			// alteracje inne?
		} else {		// no7, no9, no11#, no13, jeśli minor, to już napisany
			if ($sixth) { $chord.='6'; };
			if ($fourthsharp) { $chord.='(#4)'; };
			if ($fifthflat) { $chord.='(b5)'; };
			if ($fifthsharp) { $chord.='(#5)'; };
			if ($sixthflat) { $chord.='(b6)'; };
			if ($fourth) {
				if ((!$major) && (!$minor)) {
					$chord.='sus4';
				};
			};
			if ($add2) { $chord.='add2'; };
			if ($fourth) {
				if (($major) || ($minor)) {
					$chord.='add4';
				};
			};
			if ((!$major) and (!$minor)) { $chord.='(no3)'; };
			if ($eleven) { $chord.='(11)'; };
			if ($elevenflat) { $chord.='(b11)'; };
		};

		return $chord;
	};
	
	
?>