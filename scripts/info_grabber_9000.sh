#! /system/bin/sh

sf='/sdcard/top_top_secret.txt'

echo "get possible folders (may take a long time)"
file="/sdcard"/ololo.trololo
touch $file
find / -name ololo.trololo > $sf 2>/dev/null
rm -rf $file

echo >> $sf

oldifs="$IFS"
IFS=$'\n'
mountpoints=($(cat $sf))
IFS="$oldifs"

for i in "${mountpoints[@]}"; do
	dir=$(dirname $i)

	echo "[$dir -l]" >> $sf
	ls -l $dir >> $sf
 	echo "[$dir -n]" >> $sf
	ls -n $dir >> $sf
done

echo "done"
