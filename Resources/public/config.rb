# Require any additional compass plugins here.
# require 'sass-media_query_combiner'

# Set this to the root of your project when deployed:
http_path = "/"

# Set the images directory relative to your http_path or change
# the location of the images themselves using http_images_path:
http_images_dir = "img"

# Production Assets URL
# http_images_path = "http://your-url-goes-here/img"

# Project Assets Location
css_dir = "/"
sass_dir = "scss"
images_dir = "img"
javascripts_dir = "js"

# To enable relative paths to assets via compass helper functions Uncomment the following line:
# relative_assets = true

# You can select your preferred output style here (can be overridden via the command line):
# output_style = :expanded or :nested or :compact or :compressed
# output_style = :compressed
output_style = :expanded

# To disable debugging comments that display the original location of your selectors. Uncomment:
line_comments = false

# disable asset cache buster
asset_cache_buster do |http_path, real_path|
  nil
end

# For use in chrome dev:
# sass_options = { :debug_info => true }

# If you prefer the indented syntax, you might want to regenerate this
# project again passing --syntax sass, or you can uncomment this:
# preferred_syntax = :sass
# and then run:
# sass-convert -R --from scss --to sass sass scss && rm -rf sass && mv scss sass




