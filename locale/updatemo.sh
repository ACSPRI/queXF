#!/bin/bash
for f in `find ./ -iname *.po`; do
   BASE=`basename $f .po`
   DIR=`dirname $f`
   msgfmt $f -o $DIR/$BASE.mo
done
