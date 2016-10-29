<?php
$DEBUG = true;

include('config.php');

# Turn off the warning reporting
if(!$DEBUG) {
	error_reporting(E_ERROR | E_PARSE);
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

# If we have a query layer from which to pull shapes, we'll do that.
if(isset($selectLayer) and $selectLayer != null and $selectLayer != '') {
	$queryMap = getMapfile($mapbook, $selectLayer);
	$layer = array_reverse(explode('/', $selectLayer));
	$layer = $layer[0];
  if ($DEBUG){
    errot_log('MapFile: '.$queryMap);
    error_log("Layer: $layer");
  }

	# open the map.
	$map = ms_newMapObj($CONFIGURATION['root'].$queryMap);
	# get it's projection.
	$map_proj = $map->getProjection();
	# turn it into a real projection object if it's not null.
	if($map_proj != NULL) {
		$map_proj = ms_newprojectionobj($map_proj);
	}
}


// # Load up the select map.
// $selectMap = getMapfile($mapbook, $selectLayer);

// $map = ms_newMapObj($CONFIGURATION['root'].$selectMap);
// $layersToQuery = array();

// $layerPath = array_reverse(explode('/', $selectLayer));
// $layerName = $layerPath[0];

// $results = '';

// for($i = 0; $i < $map->numlayers; $i++) {
	// $layer = $map->getLayer($i);
	// $layer->set('status', MS_OFF);	# Turn off extraneous layers
	// $layer->set('template', ''); # this should prevent layers from being queried.
	// if($layerName == 'all' or $layer->name == $layerName or $layer->group == $layerName) {
		// $layersToQuery[] = $layer;
		// if($DEBUG) { error_log('Added layer to query stack: '.$layer->name); }
	// }
// }

$uniqueId = 'select_'.getmypid().time();

# Form the mapfile.
$mapfile = implode('', file($CONFIGURATION['highlight_map']));
$mapfile = processTemplate($mapfile, $dict);

$mapfileOut = fopen($tempDirectory.'/'.$uniqueId.'.map', 'w+');
fwrite($mapfileOut, $mapfile);
fclose($mapfileOut);


# All that work for a dozen lines of output.
header('Content-type: application/xml; charset='.$CONFIGURATION['output-encoding']);
print "<results>";
print "<script><![CDATA[";

# This could be extended to better represent the selection and query polygon shapes later.
# Right now, it sets the layer to use the mapserver_url and then points mapserver to the
# proper map.
print " GeoMOOSE.clearLayerParameters('highlight');";
print " GeoMOOSE.turnLayerOff('highlight/highlight');";
print " GeoMOOSE.changeLayerUrl('highlight', CONFIGURATION.mapserver_url);";
print " GeoMOOSE.updateLayerParameters('highlight', { 'map' : '".$tempDirectory."/".$uniqueId.".map', 'FORMAT' : 'image/png', 'TRANSPARENT' : 'true'});";
print " GeoMOOSE.turnLayerOn('highlight/highlight');";
print " GeoMOOSE.refreshLayers('highlight/highlight');";
print "]]></script>";
print "<html><![CDATA[";
print '<b>Found Shapes: </b>'.sizeof($foundShapes).'<br/>';
print "<b>Query ID: </b>" . $uniqueId.'<br/>';
print processTemplate($results, $dict);
print "]]></html></results>";

?>
