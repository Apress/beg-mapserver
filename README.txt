INSTALLING SOURCE CODE FOR MapServer: Open Source GIS Development

Each Chapter folder contains the code files for the associated chapter. In 
addition, other files (such as spatial datasets, DBase files, and MySQL scripts) 
will also be found in the appropriate Chapter folders. 


Installation is a matter of copying the files to the appropriate destination. 
All PHP, HTML, and image files must be copied to Apache's DocumentRoot;
Mapfiles and shapefile components copied to the DataDirectory; Perl and Python 
scripts to Apache's script directory. The script for creating the database used 
in Chapter 9 can be located anywhere outside the Apache tree (for security 
reasons). The patched version of the PHP/Mapscript file must be copied to the 
mapscript source directory. Locations are documented in the list below.

The following locations are appropriate for the development environment:

	Apache DocumentRoot 	(/var/www/htdocs/)
	DataDirectory  		(/home/mapdata/)
	Apache script directory (/var/www/cgi-bin/)
	PHP/MapScript directory (/usr/local/src/mapserver-4.4.1/mapscript/php3/)

==========================================================
Chapter02	CHAPTER 2 Simple MapServer Examples

first.map			DataDirectory
hello.map			DataDirectory

first.html			DocumentRoot
hello.html			DocumentRoot

==========================================================
Chapter03	CHAPTER 3 Creating the Mapping Application

second.map			DataDirectory

second.html			DocumentRoot
second_i.html			DocumentRoot

==========================================================
Chapter04	CHAPTER 4 Modifying a Map’s Look and Feel

third.map			DataDirectory

third.html			DocumentRoot
third_i.html			DocumentRoot
third_usaref.gif		DocumentRoot

==========================================================
Chapter05	CHAPTER 5 Using Query Mode

cities.dbf			DataDirectory
cities.shp			DataDirectory
cities.shx			DataDirectory

countries.dbf			DataDirectory
countries.shp			DataDirectory
countries.shx			DataDirectory

fourth.map			DataDirectory

fourth.html			DocumentRoot
fourth_i.html			DocumentRoot

fourth_cities_footer.html	DocumentRoot
fourth_cities_header.html	DocumentRoot
fourth_cities_query.html	DocumentRoot
fourth_countries_footer.html	DocumentRoot
fourth_countries_header.html	DocumentRoot
fourth_countries_query.html	DocumentRoot
fourth_empty.html		DocumentRoot
fourth_web_footer.html		DocumentRoot
fourth_web_header.html		DocumentRoot

fourth_join.dbf			DocumentRoot

fourth_worldref.gif		DocumentRoot

==========================================================
Chapter06	CHAPTER 6 Using Perl MapScript

perlms_hello.pl			Apache script directory
perlms_third.pl			Apache script directory

==========================================================
Chapter07	CHAPTER 7 Using Python MapScript

pythonms_hello.py		Apache script directory
pythonms_third.py		Apache script directory


==========================================================
Chapter08	CHAPTER 8 Using PHP/MapScript

phpms_hello.php			DocumentRoot
phpms_third.php			DocumentRoot

==========================================================
Chapter09	CHAPTER 9 Extending the Capabilities of MapScript with MySQL

nrn_geo.dbf			DataDirectory
nrn_geo.prj			DataDirectory
nrn_geo.shp			DataDirectory
nrn_geo.shx			DataDirectory

roads_type.dbf			DataDirectory
roads_type.shp			DataDirectory
roads_type.shx			DataDirectory

waterp_geo.dbf			DataDirectory
waterp_geo.prj			DataDirectory
waterp_geo.shp			DataDirectory
waterp_geo.shx			DataDirectory

fifth.map			DataDirectory

phpms_fifth.php			DocumentRoot


cup.gif				DocumentRoot
up.png				DocumentRoot
down.png			DocumentRoot
right.png			DocumentRoot
left.png			DocumentRoot
fifth_wpgref.gif		DocumentRoot
steamingcup.gif			DocumentRoot

php_mapscript.c-patchedversion	PHP/MapScript directory

mapserver_create_restaurant	user's home directory

==========================================================
0507191727

