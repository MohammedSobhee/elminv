#!/bin/bash
# ---------------------------------------------------------------------------------------------------------
# Fix laravel-mix manifest
# ---------------------------------------------------------------------------------------------------------
cp ./public/assets/mix-manifest.json ./public/
rm ./public/assets/mix-manifest.json
cd public
if [[ "$OSTYPE" == "darwin"* ]]; then
  sed -i "" 's+"/js/+"/assets/js/+g' mix-manifest.json
else
  sed -i 's+"/js/+"/assets/js/+g' mix-manifest.json
fi
cd ..
