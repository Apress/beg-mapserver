#!/usr/bin/python

import mapscript
import random

# path defaults
#
map_path = "/home/mapdata/"
map_file = "hello.map"

# Create a unique image name every time through
#
image_name = "pythonms_hello" \
             + str(random.randrange(999999)).zfill(6) \
             + ".png"

# Create a new instance of a map object
#
map =  mapscript.mapObj(map_path+map_file)

# Create an image of the map and save it to disk
#
img=map.draw()
img.save("/var/www/htdocs/tmp/" + image_name)

# Output the HTML form and map image
#
print "Content-type: text/html"
print
print "<html>"
print "<header><title>Python Mapscript Hello World</title></header>"
print "<body>"
print """
<form name="hello" action="pythonms_hello.py" method="POST">
<input type="image" name="img" src="/tmp/%s">
</form>
""" % image_name

print "</body>"
print "</html>"