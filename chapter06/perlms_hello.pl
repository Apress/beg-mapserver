#!/usr/bin/perl
use strict;
use mapscript;
use CGI ":cgi";
my $resp = new CGI;
# Create a unique image name every time through
#
my $image_name = sprintf("ms-hello%0.6d",rand(1000000)).".png";
# Create a new instance of a map object
#
my $map = new mapscript::mapObj("/home/mapdata/hello.map");
# Create an image of the map and save it to disk
#
my $img = $map->draw();
$img->save("/var/www/htdocs/tmp/".$image_name);
# Output the HTML form and map image
#
print $resp->header();
print $resp->start_html(-title=>'MapScript Hello World ');
print <<END_OF_HTML;
<form name="pointmap" action="perlms_hello.pl" method="POST">
<input type="image" name="img" src="/tmp/$image_name">
</form>
END_OF_HTML
print $resp->end_html();