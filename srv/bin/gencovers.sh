#!/bin/sh

thumbnails() {
  cd $@
  pwd
  ls
  if [ -f "cover.jpg" ] ; then
    for PX in 64 128 320 480
    do
      if [ ! -f "cover-${PX}x.jpg" ] ; then
        convert -resize ${PX}x${PX} cover.jpg "cover-${PX}px.jpg"
      fi
    done
  fi

  IFS=$'\n'
  for FILE in $( ls )
  do
    if [ -d $FILE ] ; then
      thumbnails $FILE
    fi
  done
  cd ..
}

thumbnails $@



