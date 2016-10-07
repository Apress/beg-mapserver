#!/usr/bin/perl
use strict;
use mapscript;
use CGI ":cgi";

# Default values
#
my $script_name = "/cgi-bin/perlms_third.pl";

# path defaults
my $map_path = "/home/mapdata/";
my $map_file = "third.map";
my $img_path = "/var/www/htdocs/tmp/";

# Navigation defaults
my $zoomsize=2;
my $pan="CHECKED";
my $zoomout="";
my $zoomin="";

# Displayed layer defaults
my $urbanareas = "CHECKED";	
my $lakes = "CHECKED";			
my $states = "CHECKED";
my $roads = "CHECKED";

my $clickx = 320;	
my $clicky = 240;		
my $clkpoint = new mapscript::pointObj();
my $old_extent = new mapscript::rectObj();

my @extent = (-180, 0, -60, 90);
my $max_extent = new mapscript::rectObj(-180, 0, -60, 90);

# Get CGI parms
#
my $parms = new CGI;

# Retrieve mapfile and create a map from it
#
my $map = new mapscript::mapObj($map_path.$map_file);

# We've been invoked by the form, use form variables
# 
if ( $parms->param() ) {

	# If Refresh button clicked fake the map click
	#
	if ( $parms->param('refresh') ) {
		$clickx = 320;
		$clicky = 240;
	} else { 	

	# map was clicked, get the real coordinates
	#
		$clickx = $parms->param('img.x');
		$clicky = $parms->param('img.y');
	}

	# Set the mouse click location (we need it to zoom)
	#
	$clkpoint->setXY($clickx,$clicky);

	# Selected layers may have changed, set HTML 'checks'
	#
	my $layers = join(" ",$parms->param('layer'));
	my $this_layer = 0;

	if ($layers =~ /urbanareas/){ 
		$urbanareas = "CHECKED";
		$this_layer = $map->getLayerByName('urbanareas');
		$this_layer->{status} = 1;
	} else { 
		$urbanareas = "";
		$this_layer = $map->getLayerByName('urbanareas');
		$this_layer->{status} = 0;
	}
	
	if ($layers =~ /lakes/){ 
		$lakes = "CHECKED";
		$this_layer = $map->getLayerByName('lakes');
		$this_layer->{status} = 1;
	} else { 
		$lakes = "";
		$this_layer = $map->getLayerByName('lakes');
		$this_layer->{status} = 0;
	}
		
	if ($layers =~ /states/){
		$states = "CHECKED";
		$this_layer = $map->getLayerByName('states');
		$this_layer->{status} = 1;
	} else {
		$states = "";
		$this_layer = $map->getLayerByName('states');
		$this_layer->{status} = 0;
	}


	# invoked by form - retrieve extent
	#
	if ( $parms->param('extent') ) {
		@extent = split(" ", $parms->param('extent'));
	}

	# Set the map to the extent retrieved from the form
	#
	$map->setExtent($extent[0],$extent[1],$extent[2],$extent[3]);

	# Save this extent as a rectObj, we need it to zoom.
	#
	$old_extent->{minx} = $extent[0];
	$old_extent->{miny} = $extent[1];
	$old_extent->{maxx} = $extent[2];
	$old_extent->{maxy} = $extent[3];

	# Calculate the zoom factor to pass to zoomPoint method
	# and setup the variables for web page
	#
	#   zoomfactor = +/- N
	#   if N > 0 zooms in - N < 0 zoom out - N = 0 pan
	#
	my $zoom_factor = $parms->param("zoom")*$parms->param("zsize");
	if ($zoom_factor == 0) {
		$zoom_factor = 1;
		$pan = "CHECKED";
		$zoomout = "";
		$zoomin = "";
	} elsif ($zoom_factor < 0) {
		$pan = "";
		$zoomout = "CHECKED";
		$zoomin = "";
	} else {
		$pan = "";
		$zoomout = "";
		$zoomin = "CHECKED";
	}
	$zoomsize = abs( $parms->param('zsize') );

	# Zoom in (or out) to clkpoint
	#
	$map->zoomPoint($zoom_factor,$clkpoint,$map->{width},
		$map->{height},$old_extent,$max_extent);
}

# Set unique image names for map, reference and legend
#
	my $map_id = sprintf("%0.6d",rand(1000000));

	my $image_name = "third".$map_id.".png";
	my $image_url="/tmp/".$image_name;

	my $ref_name = "thirdref".$map_id.".gif";
	my $ref_url="/tmp/".$ref_name;

	my $leg_name = "thirdleg".$map_id.".png";
	my $leg_url="/tmp/".$leg_name;

# Draw and save map image
#
	my $image=$map->draw();
	$map->drawLabelCache($image);
	$image->save($img_path.$image_name);

# Draw and save reference image
#
	my $ref = $map->drawReferenceMap();
	$ref->save($img_path.$ref_name);

# Draw and save legend image
#
	my $leg = $map->drawLegend();
	$leg->save($img_path.$leg_name);

# Get new extent of map (we'll save it in a form variable)
#
	my $new_extent = sprintf("%3.6f",$map->{extent}->{minx})." "
                        .sprintf("%3.6f",$map->{extent}->{miny})." "
                        .sprintf("%3.6f",$map->{extent}->{maxx})." "
                        .sprintf("%3.6f",$map->{extent}->{maxy});

# get the scale of the image to display on the web page
#
	my $scale = sprintf("%10d",$map->{scale});

# Convert mouse click from image coordinates to map coordinates
#
	my ($mx,$my) = img2map($map->{width},$map->{height},
				$clkpoint,$old_extent);
	my $mx_str = sprintf("%3.6f",$mx);
	my $my_str = sprintf("%3.6f",$my);

# We're done, output the HTML form
#
print $parms->header();
print $parms->start_html(-title=>'Perl Mapscript Third Map');

print <<EOF;

<html>
<head><title>MapScript Third Map</title></head>
<body bgcolor="#E6E6E6">
 <FORM METHOD=POST ACTION="$script_name">
  <table width="100%" border="1">
   <tr><td width="60%" rowspan="6">
       <input name="img" type="image" src="$image_url" 
		width=640 height=480 border=2>
       </td>
       <td width="40%" align="center" colspan="3">
       <img SRC="$ref_url" width=300 height=225 border=1>
       </td>
   </tr>
   <tr><td align="left" colspan="3"><font size="-1">
       Map scale:&nbsp &nbsp &nbsp 1:$scale</font></td></tr>

   <tr><td align="left" colspan="3"><font size="-1">
       Click x,y:&nbsp &nbsp &nbsp &nbsp $mx_str, $my_str</font>
	</td></tr>

   <tr><td align="left" colspan="3"><font size="-1">
       <input type="hidden" name="extent" value="$new_extent">
       Map Extent:&nbsp $new_extent</font></td></tr>

   <tr><td><B><center>Legend</center></B></td>
       <td><B><center>Navigation</center></B></td>
       <td><B><center>Layers</center></B></td></tr>

   <tr><td rowspan="2"><img src="$leg_url"></td>
       <td align="left"><font size="-1">
        <INPUT TYPE=RADIO NAME="zoom" VALUE=0 $pan> 
	Pan<br>
        <INPUT TYPE=RADIO NAME="zoom" VALUE=1 $zoomin> 
	Zoom In<br>
        <INPUT TYPE=RADIO NAME="zoom" VALUE=-1 $zoomout> 
	Zoom Out<br>
        <INPUT TYPE=TEXT NAME="zsize" VALUE="$zoomsize" SIZE=2> 
	Size<br>
	<center>
        <INPUT TYPE=SUBMIT NAME="refresh" VALUE="Refresh">
	</center>
       </td>

       <td align="top">
       <input type="checkbox" name="layer" 
		value="urbanareas" $urbanareas >
          Urban Areas<BR>
       <input type="checkbox" name="layer" 
		value="lakes" $lakes >
          Lakes<BR>
       <input type="checkbox" name="layer" 
		value="states" $states >
          State Boundaries<BR>
       <input type="checkbox" name="layer" 
		value="roads" $roads >
          Roads<BR></font>
       </td>
   </tr>
  </table>

 </form>
</body>
</html>


EOF


#####################################################
# Convert coordinates image to map 
#
sub img2map {
my ($width, $height, $point, $ext) = @_;
my ($x, $y, $dpp_x, $dpp_y) = (0,0,0,0);

   my $minx = $ext->{minx};
   my $miny = $ext->{miny};
   my $maxx = $ext->{maxx};
   my $maxy = $ext->{maxy};

   if ($point->{x} && $point->{y}){ 
       $x = $point->{x};
       $y = $point->{y};

       $dpp_x = ($maxx-$minx)/$width; 
       $dpp_y = ($maxy-$miny)/$height;

       $x = $minx + $dpp_x*$x;
       $y = $maxy - $dpp_y*$y;
   }
   return ($x, $y);
}
