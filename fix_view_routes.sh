#!/bin/bash

# Set the directory where your Blade views are located
VIEW_DIR="resources/views"

# The string to find and the string to replace it with
OLD_STRING="'panel.isp."
NEW_STRING="'panel.admin."

# Check if the view directory exists
if [ ! -d "$VIEW_DIR" ]; then
  echo "Error: View directory '$VIEW_DIR' not found."
  echo "Please run this script from your project's root directory."
  exit 1
fi

echo "Searching for '$OLD_STRING' in all .blade.php files inside '$VIEW_DIR'..."

# Use find and sed to perform the replacement
# The -print0 and xargs -0 handle filenames with spaces or special characters
find "$VIEW_DIR" -type f -name "*.blade.php" -print0 | xargs -0 sed -i "s/${OLD_STRING}/${NEW_STRING}/g"

echo "Replacement complete!"
echo "It's recommended to clear your application caches now."
echo "Run: php artisan optimize:clear"

