#!/bin/bash
#Source: http://stackoverflow.com/questions/7496156/gettext-how-to-update-po-and-pot-files-after-the-source-is-modified
echo '' > messages.po # xgettext needs that file, and we need it empty
cd ..
find . -type f -iname "*.php" | xgettext --keyword='T_' -o locale/messages.po -j -f -
cd locale
msgmerge -N quexf.pot messages.po > new.po
mv new.po quexf.pot
rm messages.po
