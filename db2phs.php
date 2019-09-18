<?php
/*
- mancano le S
- ampezze al posto delle mag di stazione
- una riga per stazione
- la versione non come pparametro ma la best version
*/

// parameter
$debug			= $argv[1];
$outputformat   = $argv[2];
$start          = $argv[3];
$end            = $argv[4];
$ver            = $argv[5];
$latmin         = $argv[6];
$latmax         = $argv[7];
$lonmin         = $argv[8];
$lonmax         = $argv[9];

echo "START\t$start\nEND\t$end\nVER\t$ver\nLATMIN\t$latmin\nLATMAX\t$latmax\nLONMIN\t$lonmin\nLONMAX\t$lonmax\n\n";
echo "\n\n";
echo "*******************************************************************************\n";
echo "* condizioni di corretta estrazione\n";   
echo "*     1 per una localizzazione esiste una e una sola P una e una sola S per stazione \n";
echo "*******************************************************************************\n";
echo "\n\n";

// Open connection
try
{
	$db = new PDO('mysql:host=hdbbck-rm.int.ingv.it;dbname=seisev', adsreader, adsreader);
//	$db = new PDO('mysql:host=localhost;dbname=seisev', adsreader, adsreader);
}
catch (PDOException $e)
{
    echo 'Error: ' . $e->getMessage();
    exit();
}

$id_fk_hypocenter = 1;

$hidsql = " SELECT fk_hypocenter, fk_event, ot, lat, lon, depth, mag, mag_type, region, err_h, err_z, azim_gap, rms, min_distance, nph, nph_s".
          " FROM hypocenter_h2 ".
          " WHERE ot BETWEEN '$start' AND '$end' ".
          " AND ( lat BETWEEN $latmin AND $latmax AND lon BETWEEN $lonmin AND $lonmax) ".
          " AND version = $ver ".
          " ORDER BY ot";
$psql =  "SELECT ".
		  "LEFT(s.sta,4) sta4, ".
		  "IF(LENGTH(s.sta)<5, '', RIGHT(s.sta,1)) sta5, ".
		  "CONCAT(IF(ISNULL(p.emersio),' ',p.emersio), p.phase_code) phase_code, ".
		  "IF(ISNULL(p.firstmotion), '', p.firstmotion) firstmotion, " .
		  "p.weight w, " .
		  "RIGHT(s.cha,1) cha, " .
		  "CONCAT(RIGHT(YEAR(p.arrival_time),2), IF(LENGTH(MONTH(p.arrival_time))=1,'0',''), MONTH(p.arrival_time), IF(LENGTH(DAY(p.arrival_time))=1,'0',''), DAY(p.arrival_time), IF(HOUR(p.arrival_time)<10, '0',''), HOUR(p.arrival_time), IF(MINUTE(p.arrival_time)<10,'0',''), MINUTE(p.arrival_time))  ptime, " .
		  "CONCAT(IF(LENGTH(SECOND(p.arrival_time))=1,'0',''), SECOND(p.arrival_time), '.', IF(LENGTH(ROUND(p.usec/10000, 0))=1,'0',''), ROUND(p.usec/10000, 0))  psec, " .
          "IF(fn_diff_datetime(a.arrival_time, a.usec, CONCAT(DATE(p.arrival_time),' ',HOUR(p.arrival_time),':',MINUTE(p.arrival_time)), 0)<100, ROUND(fn_diff_datetime(a.arrival_time, a.usec, CONCAT(DATE(p.arrival_time),' ',HOUR(p.arrival_time),':',MINUTE(p.arrival_time)), 0),2), ROUND(fn_diff_datetime(a.arrival_time, a.usec, p.arrival_time, 0),1)) scent, ".
		  "a.phase_code scode, ".
		  "a.weight sw, ".
		  "IF(ISNULL(d.dur) OR d.dur=0, '', d.dur) dur, ".
		  "s.cha cha3, ".
		  "s.net, ".
		  "IF(s.loc='--','',s.loc) loc ".
		" FROM hypocenter_h2 h ".
		"    JOIN hyp_lnk_pha l ON l.fk_hypocenter = h.fk_hypocenter ".
		"    JOIN phase p       ON p.id = l.fk_phase AND p.phase_code='P' ". 
		"    JOIN scnl s        ON s.id = p.fk_scnl ".
		"    LEFT JOIN (SELECT s.sta, p.arrival_time, p.usec, p.weight, p.phase_code ".
		            "   FROM hyp_lnk_pha l ".
		            "    JOIN phase p       ON p.id = l.fk_phase AND p.phase_code='S' ". 
		            "    JOIN scnl s        ON s.id = p.fk_scnl ".
		            "  WHERE l.fk_hypocenter = ? ) a ON a.sta = s.sta ".
		"    LEFT JOIN st_dur_mag d  ON h.fk_hypocenter = d.fk_hypocenter AND p.fk_scnl = d.fk_scnl ".
		" WHERE h.fk_hypocenter = ? ".
		"GROUP BY s.id ".		
		"ORDER BY p.arrival_time";

$asql =  "SELECT ".
		"LEFT(s.sta,4) sta4, ".
		"IF(LENGTH(s.sta)<5, '', RIGHT(s.sta,1)) sta5, ".
		"' A' phase_code, ".
		"'' firstmotion, " .
		"'' w, " .
		"RIGHT(s.cha,1) cha, " .
		  "CONCAT(RIGHT(YEAR(a.time1),2), IF(LENGTH(MONTH(a.time1))=1,'0',''), MONTH(a.time1), IF(LENGTH(DAY(a.time1))=1,'0',''), DAY(a.time1), IF(HOUR(a.time1)<10, '0',''), HOUR(a.time1), IF(MINUTE(a.time1)<10,'0',''), MINUTE(a.time1)) ptime, " .
		  "CONCAT(IF(LENGTH(SECOND(a.time1))=1,'0',''), SECOND(a.time1), '.', IF(LENGTH(ROUND(a.usec1/10000, 0))=1,'0',''), ROUND(a.usec1/10000, 0)) psec, " .
		  "'' scent, " .
		  "'' scode, ".
		  "'' sw, ".
		  "IF(ABS(a.amp1-a.amp2)>=1000, '', IF(ABS(a.amp1-a.amp2)>=10,ROUND(ABS(a.amp1-a.amp2),0), IF(ABS(a.amp1-a.amp2)>=1, ROUND(ABS(a.amp1-a.amp2),1), RIGHT(ROUND(ABS(a.amp1-a.amp2),2),3)))) amp, ".
		  "IF(ISNULL(a.id), '', CONCAT('', ROUND(ABS(a.amp1-a.amp2),5))) ampfull, ".
		  "'' dur, ".
		  "IF(ISNULL(m.mag), '', CONCAT('AMP_MAG=',m.mag)) amp_mag, ".
		  "s.cha cha3, ".
		  "s.net, ".
		  "IF(s.loc='--','',s.loc) loc ".
		"FROM hypocenter_h2 h ".
		"    JOIN st_amp_mag m  ON m.fk_hypocenter = h.fk_hypocenter ".
		"    JOIN amplitude a   ON a.id = m.fk_amplitude ".
		"    JOIN scnl s        ON s.id = a.fk_scnl ".
		"WHERE h.fk_hypocenter = ? ".
		"GROUP BY s.id ".
		"ORDER BY a.time1";

if ($debug==1) {
	echo "\n--------------------------\n";
    echo $hidsql;
    echo "\n-----------------------------\n";
}
try {
	$stmt = $db->query($hidsql);
	$hlists = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e)
{
    echo 'Error: ' . $e->getMessage();
    exit();
}

// while($hlist = $liliststmt->fetch()) {
foreach ($hlists as $hlist) {
    $id_fk_hypocenter   = $hlist["fk_hypocenter"];
    $eid                = $hlist["fk_event"];
    $ot                 = $hlist["ot"];
    $lat                = $hlist["lat"];
    $lon                = $hlist["lon"];
    $depth              = $hlist["depth"];
    $mag                = $hlist["mag"];
    $mag_type           = $hlist["mag_type"];
    $region             = $hlist["region"];
	$errh                = $hlist["err_h"];
	$errz                = $hlist["err_z"];
	$gap                = $hlist["azim_gap"];
	$rms                = $hlist["rms"];
	$min_distance       = $hlist["min_distance"];
	$nph                = $hlist["nph"];
	$nph_s              = $hlist["nph_s"];

    if ($debug==1) {
        echo "\n--------------------------\n";
        echo $psql;
        echo "\n-----------------------------\n";
    }

	$stmt = $db->prepare($psql);
	$stmt->bindValue(1, $id_fk_hypocenter, PDO::PARAM_INT);
	$stmt->bindValue(2, $id_fk_hypocenter, PDO::PARAM_INT);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // output data of each row
//   echo "\n12345678_1_2345678_2_2345678_3_2345678_4_2345678_5_2345678_6_2345678_7_2345678_8_23456789\n";
   foreach ($results as $row) {
        switch ($outputformat) {
            case 0:
                echo str_pad($row["sta4"],4) .
                    str_pad($row["phase_code"],1) .
                    str_pad($row["firstmotion"],1) .
                    str_pad($row["w"],1) .
                    str_pad($row["cha"],1) .
                    $row["ptime"] .
                    $row["psec"] .
                    "       " .
                    str_pad($row["scent"],5) .
                    str_pad($row["scode"],2) .
                    " " . // da manuale 1X
                    str_pad($row["sw"],1) .
                    "    " . // datasource code, A1 3X
					//str_pad($row["amp"],3) .
					str_pad("",3) .
                    "   ". // str_pad($row["per"],3) il periodo è sempre -1
                    "                     ". // 21 blanc
                    str_pad($row["dur"],4) .
                    "  ".
                    str_pad($row["sta5"],1) .
                    str_pad($row["cha3"],3) .
                    str_pad($row["net"],2) .
                    str_pad($row["loc"],2) .
                    "\n";
                break;
            case 1:
                echo str_pad($row["sta4"],4) .
                    str_pad($row["phase_code"],1) .
                    str_pad($row["firstmotion"],1) .
                    str_pad($row["w"],1) .
                    str_pad($row["cha"],1) .
                    $row["ptime"] .
                    $row["psec"] .
                    "       " .
                    str_pad($row["scent"],5) .
                    str_pad($row["scode"],2) .
                    " " . // da manuale 1X
                    str_pad($row["sw"],1) .
                    "    " . // datasource code, A1 3X
                    str_pad($row["amp"],3) .
                    "   ". // str_pad($row["per"],3) il periodo è sempre -1
                    "                     ". // 21 blanc
                    str_pad($row["dur"],4) .
                    "  ".
                    str_pad($row["sta5"],1) .
                    str_pad($row["cha3"],3) .
                    str_pad($row["net"],2) .
                    str_pad($row["loc"],2) .
                    "#".       // col 86 additional fields
                    str_pad($row["ampfull"],20) .
                    str_pad($row["amp_mag"],20) .
                    "\n";
                break;
            default:
                echo "ERROR";
        }
    } // foreach ph

	if ($debug==1) {
        echo "\n--------------------------\n";
        echo $asql;
        echo "\n-----------------------------\n";
    }

	$stmt = $db->prepare($asql);
	$stmt->bindValue(1, $id_fk_hypocenter, PDO::PARAM_INT);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
//	echo "\n-----------------------------\n";

    // output data of each row AMPLITUDE
    foreach ($results as $row) {
        switch ($outputformat) {
            case 0:
                echo str_pad($row["sta4"],4) .
                    str_pad($row["phase_code"],1) .
                    str_pad($row["firstmotion"],1) .
                    str_pad($row["w"],1) .
                    str_pad($row["cha"],1) .
                    $row["ptime"] .
                    $row["psec"] .
                    "       " .
                    str_pad($row["scent"],5) .
                    str_pad($row["scode"],2) .
                    " " . // da manuale 1X
                    str_pad($row["sw"],1) .
                    "    " . // datasource code, A1 3X
					str_pad("999",3) .
                    "   ". // str_pad($row["per"],3) il periodo è sempre -1
                    "                     ". // 21 blanc
                    str_pad($row["dur"],4) .
                    "  ".
                    str_pad($row["sta5"],1) .
                    str_pad($row["cha3"],3) .
                    str_pad($row["net"],2) .
                    str_pad($row["loc"],2) .
                    "#".       // col 86 additional fields
                    str_pad($row["ampfull"],20) .
                    "\n";
                break;
            case 1:
                echo str_pad($row["sta4"],4) .
                    str_pad($row["phase_code"],1) .
                    str_pad($row["firstmotion"],1) .
                    str_pad($row["w"],1) .
                    str_pad($row["cha"],1) .
                    $row["ptime"] .
                    $row["psec"] .
                    "       " .
                    str_pad($row["scent"],5) .
                    str_pad($row["scode"],2) .
                    " " . // da manuale 1X
                    str_pad($row["sw"],1) .
                    "    " . // datasource code, A1 3X
                    str_pad($row["amp"],3) .
                    "   ". // str_pad($row["per"],3) il periodo è sempre -1
                    "                     ". // 21 blanc
                    str_pad($row["dur"],4) .
                    "  ".
                    str_pad($row["sta5"],1) .
                    str_pad($row["cha3"],3) .
                    str_pad($row["net"],2) .
                    str_pad($row["loc"],2) .
                    "#".       // col 86 additional fields
                    str_pad($row["ampfull"],20) .
                    str_pad($row["amp_mag"],20) .
                    "\n";
                break;
            default:
                echo "ERROR";
        }
    } // foreach amp

	echo "                 10     #ide=$eid hid=$id_fk_hypocenter ot=$ot lat=$lat lon=$lon dep=$depth mag=$mag mag_type=$mag_type errh=$errh errz=$errz gap=$gap rms=$rms min_dist=$min_distance nph=$nph nph_s=$nph_s reg=$region \n"; // per hypoinvrse
}
// Close connection
$pdo = null;

?>
