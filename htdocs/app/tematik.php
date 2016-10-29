<?php
$DEBUG = true;

include('config.php');

# Turn off the warning reporting
if(!$DEBUG) {
	error_reporting(E_ERROR | E_PARSE);
}

session_start();
if (!isset($_SESSION['logged'])) {
      $_SESSION['logged'] = 0;
}

if ($_SESSION['logged'] == 0){
    print "<results>";
    print "<html><![CDATA[";
    print 'Anda harus <a href="/app/login.php">login</a> dulu<br/>';
    print "]]></html></results>";
    exit;
}

$colors = array('0 0 0', '255 0 0','0 255 0', '0 0 255',
               '255 255 0','255 0 255', '0 255 255',
               '125 0 0','0 125 0', '0 0 125',
               '125 125 0','125 0 125', '0 125 125',
               '125 255 0','125 0 255', '0 125 255',
               '255 125 0','255 0 125', '0 255 125'
               );
function getColor(){
  return dechex(rand(0x000000, 0xFFFFFF));
}

$projection = $CONFIGURATION['projection'];
if(array_key_exists('projection', $_REQUEST) and isset($_REQUEST['projection'])) {
	$projection = $_REQUEST['projection'];
}

$tempDirectory = $CONFIGURATION['temp'];

$LATLONG_PROJ = ms_newprojectionobj('epsg:4326');


$thn_pajak_sppt = $_REQUEST['thn_pajak_sppt'];

if ($DEBUG){error_log("Tahun Pajak SPPT: $thn_pajak_sppt");}

# This is the layer where shapes are selected from
$selectLayer = $_REQUEST['select_layer'];

# Load the mapbook
$mapbook = getMapbook();

$class_temp = "CLASS
                  NAME '[class_nm]'
                  EXPRESSION [class_exp]
                  STYLE
                    SIZE 1
                    COLOR [class_col]
                    OUTLINECOLOR 0 0 0
                    MAXSCALEDENOM 15000
                    OPACITY [opacity]
                  END
                  
                  LABEL
                    TYPE TRUETYPE
                    FONT vera_sans
                    SIZE 8
                    ANTIALIAS TRUE
                    COLOR 0 0 0
                    OUTLINECOLOR 0 255 0
                    BUFFER 4
                    MINFEATURESIZE auto
                    PARTIALS FALSE
                    POSITION cc
                  END            
              END
              ";
        
# If we have a query layer from which to pull shapes, we'll do that.
if(isset($selectLayer) and $selectLayer != null and $selectLayer != '') {
	$queryMap = $_REQUEST['mapfile'];//getMapfile($mapbook, $selectLayer);
	$layer = $_REQUEST['select_layer'];//array_reverse(explode('/', $selectLayer));
	#$layer = $layer[0];
  if ($DEBUG){
    #error_log('MapFile: '.$queryMap);
    error_log("Layer: $layer");
  }
  $classitem = "CLASSITEM jenis";
  $labelitem = "LABELITEM 'no_urut'";
  $arr_class['opacity'] = 20;
  if ($layer=="piutang"){
    $sql = "geom FROM (SELECT a.*, coalesce(b.status_pembayaran_sppt,'3')  jenis 
                 FROM sig.sig_bumi a
                      LEFT JOIN (SELECT kd_propinsi, kd_dati2, kd_kecamatan, kd_kelurahan,
                                        kd_blok, no_urut, kd_jns_op, 
                                        status_pembayaran_sppt
                                 FROM pbb.sppt 
                                 WHERE thn_pajak_sppt='$thn_pajak_sppt'
                                 ) as b
                           on a.kd_propinsi=b.kd_propinsi
                              and a.kd_dati2=b.kd_dati2
                              and a.kd_kecamatan=b.kd_kecamatan
                              and a.kd_kelurahan=b.kd_kelurahan
                              and a.kd_blok=b.kd_blok
                              and a.no_urut=b.no_urut
                              and a.kd_jns_op=b.kd_jns_op
                              ) as sigs using unique gid using srid=4326";
    #$classitem = "CLASSITEM jenis";
    $arr_class_nm = array("Piutang", "Lunas", "Batal", "No-Data");  
    for ($i=0;$i<4;$i++){
        $arr_class['class_nm'] = $arr_class_nm[$i];
        $arr_class['class_exp'] = "'$i'";
        $arr_class['class_col'] = $colors[$i+1];
        $class .= processTemplate($class_temp,$arr_class);
    }
  }elseif ($layer=="kls_bumi"){
    $sql = "geom FROM (SELECT a.*, coalesce(b.kd_kls_tanah,'100')  jenis 
                       FROM sig.sig_bumi a
                       INNER JOIN pbb.sppt b
                          on      a.kd_propinsi=b.kd_propinsi
                              and a.kd_dati2=b.kd_dati2
                              and a.kd_kecamatan=b.kd_kecamatan
                              and a.kd_kelurahan=b.kd_kelurahan
                              and a.kd_blok=b.kd_blok
                              and a.no_urut=b.no_urut
                              and a.kd_jns_op=b.kd_jns_op
                        WHERE b.kd_kls_tanah <> 'XXX' and b.thn_pajak_sppt='$thn_pajak_sppt'
                       ) as sigs using unique gid using srid=4326";
    $classitem = "";
    $labelitem = "LABELITEM 'no_urut'";
    $class = "";
    $arrZnt = getArrZNT();
    for ($i=0;$i<count($arrZnt);$i++){
        $arr_class['class_nm'] = $arrZnt[$i][0];
        $arr_class['class_exp'] = "([jenis] = ".$arrZnt[$i][0].")";
        $arr_class['class_col'] = $arrZnt[$i][1];
        $arr_class['opacity'] = 100;
        $class .= processTemplate($class_temp,$arr_class);
    }
    
  }elseif($layer=="kls_bng"){
    $sql = "geom FROM (SELECT a.*, coalesce(b.kd_kls_bng,'XXX')  jenis 
                       FROM sig.sig_bumi a
                          INNER JOIN pbb.sppt b 
                              on  a.kd_propinsi=b.kd_propinsi
                              and a.kd_dati2=b.kd_dati2
                              and a.kd_kecamatan=b.kd_kecamatan
                              and a.kd_kelurahan=b.kd_kelurahan
                              and a.kd_blok=b.kd_blok
                              and a.no_urut=b.no_urut
                              and a.kd_jns_op=b.kd_jns_op
                       WHERE kd_kls_bng<>'XXX' and thn_pajak_sppt='$thn_pajak_sppt'
                              ) as sigs using unique gid using srid=4326";
    #$classitem = "jenis";
    $classitem = "";
    #$labelitem = "LABELITEM 'znt'";
    $class = "";
    $arrZnt = getArrZNT();
    for ($i=0;$i<count($arrZnt);$i++){
        $arr_class['class_nm'] = $arrZnt[$i][0];
        $arr_class['class_exp'] = "([jenis] = ".$arrZnt[$i][0].")";
        $arr_class['class_col'] = $arrZnt[$i][1];
        $arr_class['opacity'] = 40;
        $class .= processTemplate($class_temp,$arr_class);
    }
  }elseif($layer=="ketetapan"){
    $sql = "geom FROM (SELECT a.*, CASE 
                                        WHEN b.pbb_yg_harus_dibayar_sppt BETWEEN 0 AND 100000 THEN '1'
                                        WHEN b.pbb_yg_harus_dibayar_sppt BETWEEN 100001 AND 500000 THEN '2'
                                        WHEN b.pbb_yg_harus_dibayar_sppt BETWEEN 500001 AND 2000000 THEN '3'
                                        WHEN b.pbb_yg_harus_dibayar_sppt BETWEEN 2000001 AND 5000000 THEN '4'
                                        ELSE '5'
                                    END AS jenis
                       FROM sig.sig_bumi a
                       INNER JOIN pbb.sppt b
                           on a.kd_propinsi=b.kd_propinsi
                              and a.kd_dati2=b.kd_dati2
                              and a.kd_kecamatan=b.kd_kecamatan
                              and a.kd_kelurahan=b.kd_kelurahan
                              and a.kd_blok=b.kd_blok
                              and a.no_urut=b.no_urut
                              and a.kd_jns_op=b.kd_jns_op
                       WHERE b.thn_pajak_sppt = '$thn_pajak_sppt'
                 ) as sigs using unique gid using srid=4326";
    #$classitem = "jenis";
    $class = "";
    $arr_class_nm = array("", "Buku I", "Buku II", "Buku III", "Buku IV", "Buku V");  
    for ($i=1;$i<6;$i++){
        $arr_class['class_nm'] = $arr_class_nm[$i];
        $arr_class['class_exp'] = "'$i'";
        $arr_class['class_col'] = $colors[$i];
        $class .= processTemplate($class_temp,$arr_class);
    }
  }elseif($layer=="znt"){
    $sql = "geom FROM (SELECT a.*, coalesce(c.nir,0)  jenis, b.kd_znt znt
                       FROM sig.sig_bumi a
                            INNER JOIN pbb.dat_op_bumi b
                                on a.kd_propinsi            = b.kd_propinsi
                                        and a.kd_dati2      = b.kd_dati2
                                        and a.kd_kecamatan  = b.kd_kecamatan
                                        and a.kd_kelurahan  = b.kd_kelurahan
                                        and a.kd_blok       = b.kd_blok
                                        and a.no_urut       = b.no_urut
                                        and a.kd_jns_op     = b.kd_jns_op
                      INNER JOIN pbb.dat_nir c
                            ON b.kd_znt = c.kd_znt
                      WHERE c.thn_nir_znt = '$thn_pajak_sppt'
                      ) as sigs using unique gid using srid=4326";
    $classitem = "";
    $labelitem = "LABELITEM 'znt'";
    $class = "";
    $arrZnt = getArrZNT();
    for ($i=0;$i<count($arrZnt);$i++){
        $arr_class['class_nm'] = $arrZnt[$i][0];
        $arr_class['class_exp'] = "([jenis] ".$arrZnt[$i][2].")";
        $arr_class['class_col'] = $arrZnt[$i][1];
        $arr_class['opacity'] = 40;
        $class .= processTemplate($class_temp,$arr_class);
    }
  }elseif ($layer=="jns_bumi"){
    $sql = "geom FROM (SELECT a.*, coalesce(b.jns_bumi,'9')  jenis
                 FROM sig.sig_bumi a
                      INNER JOIN pbb.dat_op_bumi b
                           on a.kd_propinsi=b.kd_propinsi
                              and a.kd_dati2=b.kd_dati2
                              and a.kd_kecamatan=b.kd_kecamatan
                              and a.kd_kelurahan=b.kd_kelurahan
                              and a.kd_blok=b.kd_blok
                              and a.no_urut=b.no_urut
                              and a.kd_jns_op=b.kd_jns_op
                              ) as sigs using unique gid using srid=4326";
    #$classitem = "jenis";
    $class = "";
    $arr_class_nm = array("", "Tanah & Bng", "Kavling", "Tanah Kosong", "Fasum", "Lain2");  
    for ($i=1;$i<6;$i++){
        $arr_class['class_nm'] = $arr_class_nm[$i];
        $arr_class['class_exp'] = "'$i'";
        $arr_class['class_col'] = $colors[$i];
        $class .= processTemplate($class_temp,$arr_class);
    }
  }elseif ($layer=="jns_bng"){
    $sql = "geom FROM (SELECT a.*, coalesce(b.kd_jpb,'XX')  jenis 
                 FROM sig.sig_bumi a
                      INNER JOIN pbb.dat_op_bangunan b
                           on a.kd_propinsi=b.kd_propinsi
                              and a.kd_dati2=b.kd_dati2
                              and a.kd_kecamatan=b.kd_kecamatan
                              and a.kd_kelurahan=b.kd_kelurahan
                              and a.kd_blok=b.kd_blok
                              and a.no_urut=b.no_urut
                              and a.kd_jns_op=b.kd_jns_op
                              ) as sigs using unique gid using srid=4326";
    #$classitem = "jenis";
    $class = "";
    for ($i=1;$i<17;$i++){
        $arr_class['class_nm'] = "JPB ".substr("0$i",-2);
        $arr_class['class_exp'] = "'".substr("0$i",-2)."'";
        $arr_class['class_col'] = $colors[$i];
        $class .= processTemplate($class_temp,$arr_class);
    }
  }elseif ($layer=="individu"){
     $sql = "geom FROM (SELECT a.*, CASE WHEN c.nilai_individu IS NOT NULL THEN '1'
                                         ELSE '0' END jenis 
                 FROM sig.sig_bumi a
                      INNER JOIN pbb.dat_op_bangunan b
                           on     a.kd_propinsi=b.kd_propinsi
                              and a.kd_dati2=b.kd_dati2
                              and a.kd_kecamatan=b.kd_kecamatan
                              and a.kd_kelurahan=b.kd_kelurahan
                              and a.kd_blok=b.kd_blok
                              and a.no_urut=b.no_urut
                              and a.kd_jns_op=b.kd_jns_op

                      INNER JOIN pbb.dat_nilai_individu c
                           on     c.kd_propinsi=b.kd_propinsi
                              and c.kd_dati2=b.kd_dati2
                              and c.kd_kecamatan=b.kd_kecamatan
                              and c.kd_kelurahan=b.kd_kelurahan
                              and c.kd_blok=b.kd_blok
                              and c.no_urut=b.no_urut
                              and c.kd_jns_op=b.kd_jns_op
                              and c.no_bng=b.no_bng
                              ) as sigs using unique gid using srid=4326";
   #$classitem = "jenis";
   $class = "";
   $arr_class['class_nm'] = 'Individu';
   $arr_class['class_exp'] = "'$i'";
   $arr_class['class_col'] = $colors[1];
   $class .= processTemplate($class_temp,$arr_class);
   
  }
}
// $results = '';
$dict = array();
$dict['LAYER_TYPE'] = 'POLYGON';
$dict['DATA'] = $sql;
$dict['CLASSITEM'] = $classitem;
$dict['LABELITEM'] = $labelitem;
$dict['CLASS'] = $class;

$dict['PROJECTION'] = 'epsg:4326'; #$CONFIGURATION['projection'];

$uniqueId = 'tema_'.getmypid().time();

# Form the mapfile.
$mapfile = implode('', file($CONFIGURATION['root'].$queryMap));

$mapfile = processTemplate($mapfile, $dict);
$mapfile = str_replace('/root/',$CONFIGURATION['root'],$mapfile);
#$mapfileOut = fopen($tempDirectory.'/'.$uniqueId.'.map', 'w+');
$fileOut = $tempDirectory.'/'.$layer.$thn_pajak_sppt.'.map';
$mapfileOut = fopen($fileOut, 'w+');

fwrite($mapfileOut, $mapfile);
fclose($mapfileOut);

# All that work for a dozen lines of output.
header('Content-type: application/xml; charset='.$CONFIGURATION['output-encoding']);
print "<results>";
print "<script><![CDATA[";

# This could be extended to better represent the selection and query polygon shapes later.
# Right now, it sets the layer to use the mapserver_url and then points mapserver to the
# proper map.
print " GeoMOOSE.clearLayerParameters('highlights');";
print " GeoMOOSE.turnLayerOff('highlights/highlights');";
//print " GeoMOOSE.changeLayerUrl('highlights', CONFIGURATION.mapserver_url);"; 
print " GeoMOOSE.updateLayerParameters('highlights', { 'map' : '".$fileOut."', 'FORMAT' : 'image/png', 'TRANSPARENT' : 'true'});";
print " GeoMOOSE.turnLayerOn('highlights/highlights');";
print " GeoMOOSE.refreshLayers('highlights/highlights');";
print "]]></script>";
print "<html><![CDATA[";
print 'MAP File: '.$uniqueId.'.map<br/>';
print "<b>Query ID: </b>" . $uniqueId.'<br/>';
#print processTemplate($results, $dict);
print "]]></html></results>";

function getArrZNT(){
return array(
  array('001','255 000 000','> 67390', 'BETWEEN 67390  AND 69700'),
  array('002','255 000 050','> 65120', 'BETWEEN 65120  AND 67390'),
  array('003','255 000 100','> 62890', 'BETWEEN 62890  AND 65120'),
  array('004','255 000 150','> 60700', 'BETWEEN 60700  AND 62890'),
  array('005','255 000 200','> 58550', 'BETWEEN 58550  AND 60700'),
  array('006','255 000 250','> 56440', 'BETWEEN 56440  AND 58550'),
  array('007','255 050 000','> 54370', 'BETWEEN 54370  AND 56440'),
  array('008','255 050 050','> 52340', 'BETWEEN 52340  AND 54370'),
  array('009','255 050 100','> 50350', 'BETWEEN 50350  AND 52340'),
  array('010','255 050 150','> 48400', 'BETWEEN 48400  AND 50350'),
  array('011','255 050 200','> 46490', 'BETWEEN 46490  AND 48400'),
  array('012','255 050 250','> 44620', 'BETWEEN 44620  AND 46490'),
  array('013','255 100 000','> 42790', 'BETWEEN 42790  AND 44620'),
  array('014','255 100 050','> 41000', 'BETWEEN 41000  AND 42790'),
  array('015','255 100 100','> 39250', 'BETWEEN 39250  AND 41000'),
  array('016','255 100 150','> 37540', 'BETWEEN 37540  AND 39250'),
  array('017','255 100 200','> 35870', 'BETWEEN 35870  AND 37540'),
  array('018','255 100 250','> 34240', 'BETWEEN 34240  AND 35870'),
  array('019','255 150 000','> 32650', 'BETWEEN 32650  AND 34240'),
  array('020','255 150 050','> 31100', 'BETWEEN 31100  AND 32650'),
  array('021','255 150 100','> 29590', 'BETWEEN 29590  AND 31100'),
  array('022','255 150 150','> 28120', 'BETWEEN 28120  AND 29590'),
  array('023','255 150 200','> 26690', 'BETWEEN 26690  AND 28120'),
  array('024','255 150 250','> 25300', 'BETWEEN 25300  AND 26690'),
  array('025','255 200 000','> 23950', 'BETWEEN 23950  AND 25300'),
  array('026','255 200 050','> 22640', 'BETWEEN 22640  AND 23950'),
  array('027','255 200 100','> 21370', 'BETWEEN 21370  AND 22640'),
  array('028','255 200 150','> 20140', 'BETWEEN 20140  AND 21370'),
  array('029','255 200 200','> 18950', 'BETWEEN 18950  AND 20140'),
  array('030','255 200 250','> 17800', 'BETWEEN 17800  AND 18950'),
  array('031','255 250 000','> 16690', 'BETWEEN 16690  AND 17800'),
  array('032','255 250 050','> 15620', 'BETWEEN 15620  AND 16690'),
  array('033','255 250 100','> 14590', 'BETWEEN 14590  AND 15620'),
  array('034','000 255 000','> 13600', 'BETWEEN 13600  AND 14590'),
  array('035','000 255 050','> 12650', 'BETWEEN 12650  AND 13600'),
  array('036','000 255 100','> 11740', 'BETWEEN 11740  AND 12650'),
  array('037','000 255 150','> 10870', 'BETWEEN 10870  AND 11740'),
  array('038','000 255 200','> 10040', 'BETWEEN 10040  AND 10870'),
  array('039','000 255 250','> 9250 ', 'BETWEEN 9250   AND 10040'),
  array('040','050 255 000','> 8500 ', 'BETWEEN 8500   AND 9250'),
  array('041','050 255 050','> 7790 ', 'BETWEEN 7790   AND 8500'),
  array('042','050 255 100','> 7120 ', 'BETWEEN 7120   AND 7790'),
  array('043','050 255 150','> 6490 ', 'BETWEEN 6490   AND 7120'),
  array('044','050 255 200','> 5900 ', 'BETWEEN 5900   AND 6490'),
  array('045','050 255 250','> 5350 ', 'BETWEEN 5350   AND 5900'),
  array('046','100 255 000','> 4840 ', 'BETWEEN 4840   AND 5350'),
  array('047','100 255 050','> 4370 ', 'BETWEEN 4370   AND 4840'),
  array('048','100 255 100','> 3940 ', 'BETWEEN 3940   AND 4370'),
  array('049','100 255 150','> 3550 ', 'BETWEEN 3550   AND 3940'),
  array('050','100 255 200','> 3200 ', 'BETWEEN 3200   AND 3550'),
  array('051','100 255 250','> 3000 ', 'BETWEEN 3000   AND 3200'),
  array('052','150 255 000','> 2850 ', 'BETWEEN 2850   AND 3000'),
  array('053','150 255 050','> 2708 ', 'BETWEEN 2708   AND 2850'),
  array('054','150 255 100','> 2573 ', 'BETWEEN 2573   AND 2708'),
  array('055','150 255 150','> 2444 ', 'BETWEEN 2444   AND 2573'),
  array('056','150 255 200','> 2261 ', 'BETWEEN 2261   AND 2444'),
  array('057','150 255 250','> 2091 ', 'BETWEEN 2091   AND 2261'),
  array('058','200 255 000','> 1934 ', 'BETWEEN 1934   AND 2091'),
  array('059','200 255 050','> 1789 ', 'BETWEEN 1789   AND 1934'),
  array('060','200 255 100','> 1655 ', 'BETWEEN 1655   AND 1789'),
  array('061','200 255 150','> 1490 ', 'BETWEEN 1490   AND 1655'),
  array('062','200 255 200','> 1341 ', 'BETWEEN 1341   AND 1490'),
  array('063','200 255 250','> 1207 ', 'BETWEEN 1207   AND 1341'),
  array('064','250 255 000','> 1086 ', 'BETWEEN 1086   AND 1207'),
  array('065','250 255 050','> 977  ', 'BETWEEN 977    AND 1086'),
  array('066','250 255 100','> 855  ', 'BETWEEN 855    AND 977'),
  array('067','000 000 255','> 748  ', 'BETWEEN 748    AND 855'),
  array('068','050 000 255','> 655  ', 'BETWEEN 655    AND 748'),
  array('069','100 000 255','> 573  ', 'BETWEEN 573    AND 655'),
  array('070','150 000 255','> 501  ', 'BETWEEN 501    AND 573'),
  array('071','200 000 255','> 426  ', 'BETWEEN 426    AND 501'),
  array('072','250 000 255','> 362  ', 'BETWEEN 362    AND 426'),
  array('073','000 050 255','> 308  ', 'BETWEEN 308    AND 362'),
  array('074','050 050 255','> 262  ', 'BETWEEN 262    AND 308'),
  array('075','100 050 255','> 223  ', 'BETWEEN 223    AND 262'),
  array('076','150 050 255','> 178  ', 'BETWEEN 178    AND 223'),
  array('077','200 050 255','> 142  ', 'BETWEEN 142    AND 178'),
  array('078','250 050 255','> 114  ', 'BETWEEN 114    AND 142'),
  array('079','000 100 255','> 91   ', 'BETWEEN 91     AND 114'),
  array('080','050 100 255','> 73   ', 'BETWEEN 73     AND 91'),
  array('081','100 100 255','> 55   ', 'BETWEEN 55     AND 73'),
  array('082','150 100 255','> 41   ', 'BETWEEN 41     AND 55'),
  array('083','200 100 255','> 31   ', 'BETWEEN 31     AND 41'),
  array('084','250 100 255','> 23   ', 'BETWEEN 23     AND 31'),
  array('085','000 150 255','> 17   ', 'BETWEEN 17     AND 23'),
  array('086','050 150 255','> 12   ', 'BETWEEN 12     AND 17'),
  array('087','100 150 255','> 8.4  ', 'BETWEEN 8.4    AND 12'),
  array('088','150 150 255','> 5.9  ', 'BETWEEN 5.9    AND 8.4'),
  array('089','200 150 255','> 4.1  ', 'BETWEEN 4.1    AND 5.9'),
  array('090','250 150 255','> 2.9  ', 'BETWEEN 2.9    AND 4.1'),
  array('091','000 200 255','> 2    ', 'BETWEEN 2      AND 2.9'),
  array('092','050 200 255','> 1.4  ', 'BETWEEN 1.4    AND 2'),
  array('093','100 200 255','> 1.05 ', 'BETWEEN 1.05   AND 1.4'),
  array('094','150 200 255','> 0.76 ', 'BETWEEN 0.76   AND 1.05'),
  array('095','200 200 255','> 0.55 ', 'BETWEEN 0.55   AND 0.76'),
  array('096','250 200 255','> 0.41 ', 'BETWEEN 0.41   AND 0.55'),
  array('097','000 250 255','> 0.31 ', 'BETWEEN 0.31   AND 0.41'),
  array('098','050 250 255','> 0.24 ', 'BETWEEN 0.24   AND 0.31'),
  array('099','100 250 255','> 0.17 ', 'BETWEEN 0.17   AND 0.24'),
  array('100','150 250 255','> 0    ', 'BETWEEN 0      AND 0.17')
);             
}              
?>
