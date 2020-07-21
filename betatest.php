<html><head>
<style type="text/css">
BODY, TABLE {
	background:#d0d0d0;
	font-size:8pt;
}
.czarny {
	background:black;
	padding-bottom:40px;
	color:white;
	border:0px;
}
.bialy {
	background:white;
	padding-top:40px;
	color:black;
}
INPUT {
	margin:0px 0px auto;
}
TD {
	margin:auto;
	padding-top:4px;
	padding-bottom:0px;
	text-align:center;
	border:1px solid black;
}

</style>
</head><body>

<?php
	$dzwieki=array('C','C#','D','D#','E','F','F#','G','G#','A','A#','B');		// potrzebne do zwrocnazwe($nr) i zwrocnazwezoktawa($nr)
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

	echo '<form action="index.php" method="post" target="ramka">';
	echo '<table cellspacing=1><tr>';
	for($i=21;$i<=108;$i++) {
		$n=informacjeonutcemidi($i);
		if ($n['czarny']) {		// czarny; wy¿ej
			echo '<td class="czarny">'."\n";
		} else {
			echo '<td class="bialy">'."\n";
		};
		echo '<input type="checkbox" name="nutki'.$i.'" /><br />';
		echo $n['dzwiek'].'</td>';
	
	};

?>

</tr>
<tr><td colspan=3>-1</td>
<td colspan=12>0</td>
<td colspan=12>1</td>
<td colspan=12>2</td>
<td colspan=12>3</td>
<td colspan=12>4</td>
<td colspan=12>5</td>
<td colspan=12>6</td>
<td colspan=1>7</td>
</tr>
<tr><td colspan=88 style="border:none;"><input type="reset" /><input type="submit" name="nutki" style="width:90%;padding:20px;margin:20px;" value="RYSUJ" /></td></tr>
<tr><td colspan=88 style="border:none">

<iframe name="ramka" style="width:1300;height:170;background-color:white;" />
</td></tr></table>
</form>

</body></html>