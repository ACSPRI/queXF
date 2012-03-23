#!/bin/bash
for f in `find ./ -iname 'quexf-*.po'`; do
   BASE=`basename $f .po`
   DIR=`dirname $f`
   L=${BASE:6:2}
   mv $f $L/LC_MESSAGES/$L.po
done
