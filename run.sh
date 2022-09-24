#!/bin/bash

docker build -t bandcamp-tools . || exit 1

echo
echo "starting application"
docker run --rm -it \
  -e FPM_USER=$( id -u) \
  -v $(pwd)/srv:/srv \
  -p 8080:80 \
  bandcamp-tools $@
