<?php

//------------------------------------------
// Arrow - display navigation arrows for IE

function Arrow( $which ) {

   // return javascript code to display navigation buttons
   // if the browser is IE-like or nothing if it's not

   $arrow = <<<ENDOFSCRIPT
   <script>
   if (navigator.appName!="Netscape") 
   {
     document.write(
     '<input name="$which" type="image" src="$which.png">'
     )
   }
   </script>
ENDOFSCRIPT;

   return $arrow;

} // end Arrow


//------------------------------------------
// HandleIE - allow spatial query when using IE

function HandleIE($mode, $image_url) {

   // browser is IE *and* mode is browse display map image in 
   // <img> tag otherwise display map image in <input> tag

   if ($mode == "browse" ) { 
   $jscript = <<<ENDOFSCRIPT
   <script>

   if (navigator.appName!="Netscape") 
   {
      document.write(
        '<img name="img" src="$image_url" '
       +'width=640 height=480 usemap="#stores">'
      )
   } else {
      document.write(
        '<input name="img" type="image" src="$image_url" '
       +'width=640 height=480 usemap="#stores">'
      )
   }
   </script>
ENDOFSCRIPT;
   } else { 
   $jscript = <<<ENDOFSCRIPT
   <script>
      document.write(
      '<input name="img" type="image" src="$image_url" '
     +'width=640 height=480 usemap="#stores">'
      )
   </script>
ENDOFSCRIPT;
   }
   return $jscript;

} // end HandleIE


//------------------------------------------
// img2map - convert image coords to map coords

function img2map($width,$height,$point,$ext) {

   // valid point required

   if ($point->x && $point->y){

      // find degrees per pixel
 
      $dpp_x = ($ext->maxx - $ext->minx)/$width; 
      $dpp_y = ($ext->maxy - $ext->miny)/$height;

      // calculate map coordinates

      $p[0] = $ext->minx + $dpp_x*$point->x;
      $p[1] = $ext->maxy - $dpp_y*$point->y;
   }

   return $p;

} // end img2map


//------------------------------------------
// map2img - convert map coords to image coords

function map2img($width,$height,$point,$ext) {

   // valid point required
  
   if ($point->x && $point->y){ 

      // find pixels per degree
        
      $ppd_x = $width/($ext->maxx - $ext->minx);
      $ppd_y = $height/($ext->maxy - $ext->miny);

      // calculate image coordinates
        
      $p[0] =  $ppd_x * ($point->x - $ext->minx);
      $p[1] =  $height - $ppd_y * ($point->y - $ext->miny);
      settype($p[0],"integer");
      settype($p[1],"integer");
   }

   return $p;

} // end map2img


//------------------------------------------
// MarkSpot - return an HTML imagemap area tag 

function MarkSpot($seq,$width,$height,$point,$ext,$row) {

   // Given the map size in pixels and the geographic 
   // extent of the map returns an <area> tag that 
   // contains Javascript event handlers that popup
   // and hide tooltips on mouseovers. 

   // hotspot is point coordinates +/- $size pixels

   $size = 15;

   // get hotspot coords in pixels

   list($x, $y) = map2img($width,$height,$point,$ext);

   // calculate coordinates of imagemap area

   $xm = $x - $size; if ($xm < 0 ) {$xm = 0;}
   $ym = $y - $size; if ($ym < 0 ) {$ym = 0;}
   $xp = $x + $size; if ($xp > $width ) {$xp = $width;}
   $yp = $y + $size; if ($yp > $height ) {$yp = $height;}

   // create <area> tag

   $area = "<area name=\"$seq\" "; 
   $area = $area."onmouseover=\"return overlib(";
   $area = $area."'<font><table width=300>";
   $area = $area."<tr><td><img src=steamingcup.gif></td>";
   $area = $area."<td>Slurp & Burp Restaurants</td></tr>";
   $area = $area."<tr><td></td><td><HR></td></tr>";
   $area = $area."<tr><td>Store#:</td><td>$row[0]</td></tr>";
   $area = $area."<tr><td>Address:</td><td>$row[1]</td></tr>";
   $area = $area."<tr><td>Phone:</td><td>$row[4]</td></tr>";
   $area = $area."<tr><td>Hours:</td><td>$row[5] - $row[6]</td></tr>";
   $area = $area."</table>',FGCOLOR,'#FFFFFF',BGCOLOR,'#000000',";
   $area = $area."WIDTH,300,HAUTO,VAUTO);\" ";
   $area = $area."onmouseout=\"return nd();\" ";
   $area = $area."onclick=\"return false;\" ";
   $area = $area."coords=\"$xm,$ym,$xp,$yp\" href=\"#\">\n";

   // return <area> tag

   return $area;

} // end MarkSpot


//------------------------------------------
// CreateTTimagemap - create atooltip imagemap

function CreateTTimagemap($qresult,$map) {

   // return an imagemap with an <area> tag 
   // for each row of the query results.

   $hotSpot = ms_newPointObj();

   $imagemap = "<map name=\"stores\">";

   // scan the query results

   for ( $i = 0; $i < count($qresult); $i++) {

      $row = $qresult[$i];
      $hotSpot->setXY($row[3],$row[2]);
      $newarea = MarkSpot($i,$map->width,$map->height,
                           $hotSpot,$map->extent,$row);
      $imagemap = $imagemap."\n".$newarea;
   }

   // close the imagemap tag

   $imagemap = $imagemap."\n</map>\n";

   return $imagemap;

} // end CreateTTimageMap


//------------------------------------------
// GetStoreMenu - returns string containing store menu

function GetStoreMenu($store_id) {

   // Retrieve products/services menu for a store

   @mysql_connect("localhost", "mysql", "password")
            or die("Could not connect to MySQL server!");

   @mysql_select_db("restaurant")
            or die("Could not select database");

   $query = "SELECT product.description FROM store, menu, product ";
   $query = $query."WHERE store.id=menu.store_id ";
   $query = $query."AND menu.product_id=product.id ";
   $query = $query."AND  store.id=$store_id";

   $result = mysql_query($query);

   // save each menu item in an array element

   $i = 0;
   while ( $row = mysql_fetch_array($result,MYSQL_NUM) ) {
      $item[$i] = $row[0];
      $i++;
   }

   // return menu as a string

   return join("|",$item);

} // end GetStoreMenu


//------------------------------------------
// GetStoreTable - returns array containing store table

function GetStoreTable() {

     // Retrieve store table from MySQL database

     @mysql_connect("localhost", "mysql", "password")
              or die("Could not connect to MySQL server!");

     @mysql_select_db("restaurant")
              or die("Could not select database");

     $query = "SELECT * FROM store";
     $result = mysql_query($query);

     // save each row of result in an array

     $i = 0;
     while ( $row = mysql_fetch_array($result,MYSQL_NUM) ) {
        $qresult[$i] = $row;
        $i++;
     }

     // return array of results

     return $qresult;

} // end GetStoreTable


//------------------------------------------
// AddPoints - add store locations to 'poi' map layer

function AddPoints ( $map, $qresult ) {

     // Use lat/long info from query results to add points 
     // to the points-of-interest layer of the map
     // shape index is set to the store-id 
     // (this requires a patched version of Mapscript)

     $this_layer = $map->getLayerByName('poi');

     $i = 0;
     foreach($qresult as $row) {
        $poi[$i] = ms_newPointObj();
        $ln[$i] =  ms_newLineObj();
        $shp[$i] = ms_newShapeObj(MS_SHAPE_POINT);
        $poi[$i]->setXY($row[3],$row[2]);
        $ln[$i]->add($poi[$i]);		
        $shp[$i]->add($ln[$i]);
        $shp[$i]->set(index, $row[0]);
        $this_layer->addFeature( $shp[$i] );
        $i++;
     }

     return;

} // end AddPoints


//------------------------------------------
// NearbyStores - return a list of stores near click point

function NearbyStores($point,$map,$radius) {

     // get query layer

     $qlayer = $map->getLayerByName('poi');
     $qlayer->set("tolerance",$radius);

     // query the query layer - $radius is set in browser
     // queryByPoint ignores TOLERANCE units using native map units
     // instead - in this case decimal degrees. The number of miles
     // per degree is (approximately of course) 69.04 therefore
     // the correction from degrees to miles. This would have to
     // change if TOLERANCEUNITS, the map or scale units change.

     @$qlayer->queryByPoint($point, MS_MULTIPLE, $radius/69.04);
     $numResults = $qlayer->getNumResults();

     // we've got results, store id equals shape index

     if ($numResults != 0) {
          for ($i = 0; $i < $numResults; $i++) {
              $query_result = $qlayer->getResult($i);
              $StoreList[$i] = $query_result->shapeindex;
          }
     } else {

          $StoreList = "";      // no results

     }

     return $StoreList;

} // end NearbyStores


//------------------------------------------
// BuildResultTable - build HTML table of nearby stores

function BuildResultTable($nearby,$qresult) {

   // assemble the table of nearby stores

   $result_table = "<table border=1>\n<tr>";
   $result_table = $result_table."<th>Store</th>";
   $result_table = $result_table."<th>Address</th>";
   $result_table = $result_table."<th>Latitude</th>";
   $result_table = $result_table."<th>Longitude</th>";
   $result_table = $result_table."<th>Phone</th>";
   $result_table = $result_table."<th>Open</th>";
   $result_table = $result_table."<th>Close</th>";
   $result_table = $result_table."<th>Menu</th></tr>\n";

   // there are stores nearby

   if ( $nearby ) {
      foreach ($nearby as $store) {
         $row = $qresult[$store - 1];
         $menu = GetStoreMenu($store);
         $result_table = $result_table."<tr>";
         for ($j = 0; $j < 7; $j++) {
            $result_table = $result_table."<td>$row[$j]</td>";
         }
         $result_table = $result_table."<td>$menu</td></tr>\n";
      } 

   // there are NO stores nearby

   } else {
     $result_table = $result_table."<tr><td colspan=8>No results</td></tr>";
   } 
   $result_table = $result_table."</table>";

   return $result_table;

} // end BuildResultTable


//------------------------------------------

// Who are we

$script_name = "phpms_fifth.php";


// Define some default values to use
// before form variables are available


// path defaults

$map_path = "/home/mapdata/";
$map_file = "fifth.map";
$img_path = "/var/www/htdocs/tmp/";


// Navigation defaults

$zoomsize = 2;
$pan = "CHECKED";
$zoomout = "";
$zoomin = "";


// Displayed layer defaults

$hoods = "CHECKED";     
$rivers = "CHECKED";               
$streets = "CHECKED";
$majorstreets = "CHECKED";


// Map mode

$browse = "CHECKED";
$nquery = "";
$radius = 1;          // sets TOLERANCE for point query
                      // TOLERANCEUNITS miles specified in mapfile


// Default click point

$clickx = 320;     
$clicky = 240;          
$clkpoint = ms_newPointObj();
$old_extent = ms_newRectObj();


// Default extent & maximum extent are the same

$extent = array(-97.384655, 49.697475, -96.877772, 50.077168);
$max_extent = ms_newRectObj();
$max_extent->setextent(-97.384655, 49.697475, -96.877772, 50.077168);


// Retrieve mapfile and create a map from it

$map = ms_newMapObj($map_path.$map_file);


// First time we're invoked use default variable and drop
// through to create map image for the first time else use
// form variables to pan and zoom before creating map image


if (( $_POST['img_x'] and $_POST['img_y'] ) or 
   $_POST['refresh'] or $_POST['left_x'] or 
     $_POST['right_x'] or $_POST['up_x'] or $_POST['down_x']) {


     // Refresh button clicked, fake the map click

     if ( $_POST['refresh'] ) {
          $clickx = 320;
          $clicky = 240;


     // left arrow clicked - pan left 1/4 image width

     } elseif ( $_POST['left_x'] ) {
          $clickx = 160;
          $clicky = 240;


     // right arrow clicked - pan right 1/4 image width

     } elseif ( $_POST['right_x'] ) {
          $clickx = 480;
          $clicky = 240;


     // up arrow clicked - pan up 1/4 image height

     } elseif ( $_POST['up_x'] ) {
          $clickx = 320;
          $clicky = 120;


     // down arrow clicked - pan down 1/4 image height

     } elseif ( $_POST['down_x'] ) {
          $clickx = 320;
          $clicky = 360;


     // map was clicked, get the real coordinates

     } else {    
          $clickx = $_POST['img_x'];
          $clicky = $_POST['img_y'];
         
     }


     // Set the mouse click location (we need it to zoom)
     
     $clkpoint->setXY($clickx,$clicky);


     // mode or search radius may have changed, update 'em

     if ($_POST['mode'] == "nquery") {
          $nquery = "CHECKED";
          $browse = "";
     } else {
          $nquery = "";
          $browse = "CHECKED";
     }
     $radius = abs( $_POST['radius'] );


     // Selected layers may have changed, reset HTML 'checks'
     
     $layers = join(" ",$_POST['layer']);

     if (preg_match("/hoods/", $layers)){ 
          $hoods = "CHECKED";
          $this_layer = $map->getLayerByName('hoods');
          $this_layer->set('status', MS_ON);
     } else { 
          $hoods = "";
          $this_layer = $map->getLayerByName('hoods');
          $this_layer->set('status', MS_OFF);
     }

     if (preg_match("/rivers/", $layers)){      
          $rivers = "CHECKED";
          $this_layer = $map->getLayerByName('rivers');
          $this_layer->set('status', MS_ON);
     } else { 
          $rivers = "";
          $this_layer = $map->getLayerByName('rivers');
          $this_layer->set('status', MS_OFF);
     }

     if (preg_match("/majorstreets/", $layers)){           
          $majorstreets = "CHECKED";
          $this_layer = $map->getLayerByName('majorstreets');
          $this_layer->set('status', MS_ON);
     } else {
          $majorstreets = "";
          $this_layer = $map->getLayerByName('majorstreets');
          $this_layer->set('status', MS_OFF);
     }

     if (preg_match("/streets/", $layers)){           
          $streets = "CHECKED";
          $this_layer = $map->getLayerByName('streets');
          $this_layer->set('status', MS_ON);
     } else {
          $streets = "";
          $this_layer = $map->getLayerByName('streets');
          $this_layer->set('status', MS_OFF);
     }


     // since we were invoked by the form - retrieve previous map extent
     
     if ( $_POST['extent'] ) {
          $extent = split(" ", $_POST['extent']);
     }


     // Set the map to the extent retrieved from the form
     
     $map->setExtent($extent[0],$extent[1],$extent[2],$extent[3]);


     // Save this extent as a rectObj, we need it to zoom.
     
     $old_extent->setextent($extent[0],$extent[1],$extent[2],$extent[3]);


     // convert click point to geo coordinates before zoom or pan
     // we need it for point query

     list($qx,$qy) = img2map($map->width,$map->height,$clkpoint,$old_extent);
     $qpoint = ms_newPointObj();
     $qpoint->setXY($qx,$qy);


     // Calculate the zoom factor to pass to zoomPoint method
     // and setup the pan and zoom variables for web page
     
     //   zoomfactor = +/- N
     //   if N > 0 zooms in - N < 0 zoom out - N = 0 pan
     //
     $zoom_factor = $_POST['zoom'] * $_POST['zsize'];

     if ($zoom_factor == 0) {
          $zoom_factor = 1;
          $pan = "CHECKED";
          $zoomout = "";
          $zoomin = "";
     } elseif ($zoom_factor < 0) {
          $pan = "";
          $zoomout = "CHECKED";
          $zoomin = "";
     } else {
          $pan = "";
          $zoomout = "";
          $zoomin = "CHECKED";
     }
     $zoomsize = abs( $_POST['zsize'] );


     // Zoom in (or out) to clkpoint

     if ($_POST['mode'] == "browse") {
     $map->zoomPoint($zoom_factor,$clkpoint,$map->width,
          $map->height,$old_extent,$max_extent); 
     }
} 


// Retrieve store table, 
// add points to the points-of-interest layer, 
// build tooltip imagemap 

$qresult = GetStoreTable();
AddPoints( $map, $qresult );
$image_map = CreateTTimagemap($qresult,$map);


// The points-of-interest layer has been populated 
// and can now be queried if in query mode 

if ( $_POST['mode'] == "nquery" ) {

     // find nearby stores

     $nearby = NearbyStores($qpoint,$map,$radius);

     // build HTML table of nearby stores

     $result_table = BuildResultTable($nearby,$qresult);
       
}


// create unique names for map and reference images

$map_id = sprintf("%0.6d",rand(0,999999));
$image_name = "fifth".$map_id.".png";
$image_url="/tmp/".$image_name;
$ref_name = "fifthref".$map_id.".gif";
$ref_url="/tmp/".$ref_name;


// Draw and save map image

$image=$map->draw();
$image->saveImage($img_path.$image_name);


// Draw and save reference image

$ref = $map->drawReferenceMap();
$ref->saveImage($img_path.$ref_name);


// Get new extent of map (we'll save it in a form variable)

$new_extent = sprintf("%3.6f",$map->extent->minx)." "
             .sprintf("%3.6f",$map->extent->miny)." "
             .sprintf("%3.6f",$map->extent->maxx)." "
             .sprintf("%3.6f",$map->extent->maxy);


// get the scale of the image to display on the web page

$scale = sprintf("%10d",$map->scale);


// Convert mouse click from image coordinates to map coordinates

list($mx,$my) = img2map($map->width,$map->height,$clkpoint,$old_extent);
$mx_str = sprintf("%3.6f",$mx);
$my_str = sprintf("%3.6f",$my);


$NavigateIE = HandleIE($_POST['mode'],$image_url);


// We're done, output the HTML form

?>

<html>
<head>
<title>MapScript Fifth Map</title>

<script type="text/javascript">
  var ol_textsize = "5px";
  var ol_width = 300;
</script>
<script type="text/javascript" src="overlib.js">
<!-- overLib (c) Erik Bosrup --> </script>

</head>

<body bgcolor="#E6E6E6">

    <!-- overLib needs this tag right after body-->

    <div id="overDiv" 
         style="position:absolute; visibility:hidden; z-index:1000;">
    </div>

<!-- image map stores tooltip info and displays it -->
<?php echo $image_map; ?>

 <form method=post action="<?php echo $script_name;?>">
  <table width="100%" border="1">
   <tr><td width="60%" rowspan="6">
     <table border="0">

<!-- Display up arrow if browser is IE-like -->
     <tr><td align="center" colspan="3"><?php echo Arrow("up"); ?>
         </td></tr>

<!-- Display left arrow if browser is IE-like -->
     <tr><td valign="center"><?php echo Arrow("left"); ?>
         </td>

<!-- Displays image as <img> or <input> depending on mode and browser -->
         <td><?php echo $NavigateIE; ?>
         </td>

<!-- Display right arrow if browser is IE-like -->
         <td valign="center"><?php echo Arrow("right"); ?>
         </td></tr>

<!-- Display down arrow if browser is IE-like -->
     <tr><td align="center" colspan="3"><?php echo Arrow("down"); ?>
         </td></tr>

     </table>
     </td>
     <td width="40%" align="center" colspan="3">

<!-- Displays reference image -->
     <img SRC="<?php echo $ref_url; ?>" width=300 height=225 border=1>
     </td></tr>

<!-- Displays map scale -->
   <tr><td align="left" colspan="3"><font size="-1">
       Map scale:&nbsp &nbsp &nbsp 1:<?php echo $scale; ?>
       </font></td></tr>

<!-- Display click coordinates -->
   <tr><td align="left" colspan="3"><font size="-1">
       Click x,y:&nbsp &nbsp &nbsp &nbsp <?php echo $mx_str; ?>, 
       <?php echo $my_str; ?>
       </font></td></tr>

<!-- Display map extent and save it as hidden variable -->
   <tr><td align="left" colspan="3"><font size="-1">
       <input type="hidden" name="extent" 
              value="<?php echo $new_extent; ?>">
       Map Extent:&nbsp <?php echo $new_extent; ?></font></td></tr>

   <tr><td><B><center>Mode</center></B></td>
       <td><B><center>Navigation</center></B></td>
       <td><B><center>Layers</center></B></td></tr>

<!-- Select map mode -->
   <tr><td rowspan="2">
       <input type=radio name=mode 
        value="browse" <?php echo $browse; ?> >Browse<BR>
       <input type=radio name=mode 
        value="nquery" <?php echo $nquery; ?> >Query<BR>
       Search radius:<BR>
       <input type=text name=radius size="4" 
        value="<?php echo $radius; ?>">(miles)

<!-- Navigation controls -->
       <td align="left"><font size="-1">
       <input type=radio name="zoom" 
        value=0 <?php echo $pan; ?>> Pan<br>
       <input type=radio name="zoom" 
        value=1 <?php echo $zoomin; ?>> Zoom In<br>
       <input type=radio name="zoom" 
        value=-1 <?php echo $zoomout; ?>> Zoom Out<br>
       <input type=text name="zsize" 
        value="<?php echo $zoomsize; ?>" SIZE=2>Size<br>
       <center>
          <input type=submit name="refresh" value="Refresh">
       </center></td>

<!-- Layer selection -->
       <td align="top">
       <input type="checkbox" name="layer[]" 
          value="hoods" <?php echo $hoods; ?> >
          'hoods<BR>
       <input type="checkbox" name="layer[]" 
          value="rivers" <?php echo $rivers; ?> >
          Rivers<BR>
       <input type="checkbox" name="layer[]" 
          value="majorstreets" <?php echo $majorstreets; ?> >
          Major streets<BR>
       <input type="checkbox" name="layer[]" 
          value="streets" <?php echo $streets; ?> >
          Streets</font>
       </td></tr>
  </table>
 </form>

<!-- Display table of nearby stores in query mode -->
 <?php echo $result_table; ?>

</body>
</html>