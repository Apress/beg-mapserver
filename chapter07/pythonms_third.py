#!/usr/bin/python

import mapscript
import cgi
import random

#####################################################
# Convert image coordinates to map coordinates
#
def img2map (width, height, x, y, ext):

     x = 0
     y = 0
     dpp_x = 0
     dpp_y = 0

     minx = ext.minx          
     miny = ext.miny
     maxx = ext.maxx
     maxy = ext.maxy

     dpp_x = (maxx-minx)/width  # degrees per pixel
     dpp_y = (maxy-miny)/height
     x = minx + dpp_x*x         # degrees from left
     y = maxy - dpp_y*y         # degrees from top because
                                # pixels count down from top
     return (x, y)
#####################################################

# Default values
#
script_name = "/cgi-bin/pythonms_third.py"

# path defaults
map_path = "/home/mapdata/"
map_file = "third.map"
img_path = "/var/www/htdocs/tmp/"

# Navigation defaults
zoomsize=2
pan="CHECKED"
zoomout=""
zoomin=""

# Displayed layer defaults
urbanareas = "CHECKED"     
lakes = "CHECKED"               
states = "CHECKED"
roads = "CHECKED"

# map click defaults
clickx = 320     
clicky = 240          
clkpoint = mapscript.pointObj()

# extent defaults
old_extent = mapscript.rectObj()
extent = (-180.0, 0.0, -60.0, 90.0)
max_extent = mapscript.rectObj(-180.0, 0.0, -60.0, 90.0)

# Get CGI parms
#
parms = cgi.FieldStorage()

# Retrieve mapfile and create a map from it
#
map =  mapscript.mapObj(map_path+map_file)

# We've been invoked by the form, use form variables
# 
if (parms.getfirst('img.x') and parms.getfirst('img.y')) \
     or parms.getfirst('refresh'):

     if parms.getfirst('refresh'):# refresh, fake the coordinates
          clickx = 320
          clicky = 240
     else:                     # map click, use real coordinates
          clickx = int( parms.getfirst('img.x') )
          clicky = int( parms.getfirst('img.y') )

     # Set mouse click location in pointObj (we need it to zoom)
     #
     clkpoint.x = clickx
     clkpoint.y = clicky

     # Selected layers may have changed, set HTML 'checks'
     #
     layerlist = parms.getlist('layer')
     layers = " ".join(layerlist)

     if layers.find('urbanareas') > -1:
          urbanareas = "CHECKED"
          this_layer = map.getLayerByName('urbanareas')
          this_layer.status = 1
     else:
          urbanareas = ""
          this_layer = map.getLayerByName('urbanareas')
          this_layer.status = 0

     if layers.find('lakes') > -1:
          lakes = "CHECKED"
          this_layer = map.getLayerByName('lakes')
          this_layer.status = 1
     else:
          lakes = ""
          this_layer = map.getLayerByName('lakes')
          this_layer.status = 0

     if layers.find('states') > -1:     
          states = "CHECKED"
          this_layer = map.getLayerByName('states')
          this_layer.status = 1
     else:
          states = ""
          this_layer = map.getLayerByName('states')
          this_layer.status = 0

     # retrieve extent of displayed map
     #
     if parms.getfirst('extent'):
          extent = parms.getfirst('extent').split(' ')

     # Set the new map to the extent retrieved from the form
     #
     map.setExtent(float(extent[0]),float(extent[1]), \
          float(extent[2]),float(extent[3]))

     # Save this extent as a rectObj (we need it to zoom)
     #
     old_extent.minx = float(extent[0])
     old_extent.miny = float(extent[1])
     old_extent.maxx = float(extent[2])
     old_extent.maxy = float(extent[3])

     # Calculate the zoom factor to pass to zoomPoint method
     # and setup the variables for web page
     #
     #   zoomfactor = +/- N
     #   if N > 0 zooms in - N < 0 zoom out - N = 0 pan
     #
     zoom_factor = int(parms.getfirst('zoom') ) \
                    * int(parms.getfirst('zsize') )
     zoomsize = str( abs( int( parms.getfirst('zsize') ) ) )
     if zoom_factor == 0:
          zoom_factor = 1
          pan = "CHECKED"
          zoomout = ""
          zoomin = ""
     elif zoom_factor < 0:
          pan = ""
          zoomout = "CHECKED"
          zoomin = ""
     else:
          pan = ""
          zoomout = ""
          zoomin = "CHECKED"

     # Zoom in (or out) to clkpoint
     #
     map.zoomPoint(zoom_factor,clkpoint,map.width,     \
                    map.height,old_extent,max_extent)

# We've dropped thru because the script was invoked directly 
# or we've finished panning, zooming and seting layers on or off
# 

# Set unique image names for map, reference and legend
#
map_id = str(random.randrange(999999)).zfill(6)

image_name = "pythird" + map_id + ".png"
image_url="/tmp/" + image_name

ref_name = "pythirdref" + map_id + ".gif"
ref_url="/tmp/" + ref_name

leg_name = "pythirdleg" + map_id + ".png"
leg_url="/tmp/" + leg_name

# Draw and save map image
#
image=map.draw()
image.save(img_path + image_name)

# Draw and save reference image
#
ref = map.drawReferenceMap()
ref.save(img_path + ref_name)

# Draw and save legend image
#
leg = map.drawLegend()
leg.save(img_path + leg_name)

# Get extent of map after any zooming or panning
# (we'll save it in a form variable)
#
new_extent = str(map.extent.minx)+" "+str(map.extent.miny) \
          + " " + str(map.extent.maxx) \
          + " " + str(map.extent.maxy)

# get the scale of the image to display on the web page
#
scale = map.scale

# Convert mouse click from image coordinates to map coordinates
#
clkgeo = img2map(map.width,map.height, \
             clkpoint.x,clkpoint.y,old_extent)
x_geo = clkgeo[0]
y_geo = clkgeo[1]

Mapvars= {'image_url':image_url,'ref_url':ref_url, 'scale':scale, \
          'x_geo':x_geo, 'y_geo':y_geo, 'new_extent':new_extent, \
          'leg_url':leg_url, 'pan':pan, 'zoomin':zoomin, \
          'zoomout':zoomout,'zoomsize':zoomsize, 'lakes':lakes, \
          'states':states, 'roads':roads, 'urbanareas':urbanareas}

# We're done, output the HTML form
#
print "Content-type: text/html"
print
print "<html>"
print "<header><title>Python Mapscript Third Map</title></header>"
print "<body bgcolor=\"#E6E6E6\">"

print """
 <form method=post action="/cgi-bin/pythonms_third.py">
  <table width="100" border="1">
   <tr><td width="60" rowspan="6">
       <input name="img" type="image" src="%(image_url)s"
          width=640 height=480 border=2>
       </td>
       <td width="40" align="center" colspan="3">
       <img SRC="%(ref_url)s" width=300 height=225 border=1>
       </td>
   </tr>
   <tr><td align="left" colspan="3"><font size="-1">
       Map scale:&nbsp &nbsp &nbsp 1:%(scale)10d scale
       </font></td></tr>

   <tr><td align="left" colspan="3"><font size="-1">
       Click x,y:&nbsp &nbsp &nbsp &nbsp %(x_geo)6.6f, %(y_geo)6.6f
       </font></td></tr>

   <tr><td align="left" colspan="3"><font size="-1">
       <input type="hidden" name="extent" value="%(new_extent)s">
       Map Extent:&nbsp %(new_extent)s
       </font></td></tr>

   <tr><td><B><center>Legend</center></B></td>
       <td><B><center>Navigation</center></B></td>
       <td><B><center>Layers</center></B></td></tr>

   <tr><td rowspan="2"><img src="%(leg_url)s"></td>
       <td align="left"><font size="-1">
        <input type=radio name="zoom" value=0 %(pan)s> 
     Pan<br>
        <input type=radio name="zoom" value=1 %(zoomin)s> 
     Zoom In<br>
        <input type=radio name="zoom" value=-1 %(zoomout)s> 
     Zoom Out<br>
        <input type=text name="zsize" value="%(zoomsize)s" size=2> 
     Size<br>
     <center>
        <input type=submit name="refresh" value="Refresh">
     </center>
       </td>

       <td align="top">
       <input type="checkbox" name="layer" 
          value="urbanareas" %(urbanareas)s >
          Urban Areas<BR>
       <input type="checkbox" name="layer" 
          value="lakes" %(lakes)s >
          Lakes<BR>
       <input type="checkbox" name="layer" 
          value="states" %(states)s >
          State Boundaries<BR>
       <input type="checkbox" name="layer" 
          value="roads" %(roads)s >
          Roads<BR></font>
       </td>
   </tr>
  </table>
 </form>
""" % Mapvars


print "</body>"
print "</html>"

# we're done!