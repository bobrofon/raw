#! /system/bin/sh

sf='/sdcard/top_secret.txt'

echo "get 'mount' output for root user"
echo "[mount]" > $sf
mount >> $sf
echo >> $sf
echo "done"

echo "get sshfs mountpoints from all mount namespaces"
echo "[sshfs | all namespaces]" >> $sf
oldifs="$IFS"
IFS=$'\n'
mountpoints=($(cat /proc/*/task/*/mounts 2>/dev/null | grep sshfs | sort -u))
IFS="$oldifs"
printf "%s\n" "${mountpoints[@]}" >> $sf
echo >> $sf
echo "done"

echo "get permissions info"
echo "[permissions]" >> $sf
mount_paths=()
for i in "${mountpoints[@]}"; do
	mountpoint=($i)
	dir=$(dirname "${mountpoint[1]}")

	echo "[$dir -l]" >> $sf
	ls -l $dir >> $sf
 	echo "[$dir -n]" >> $sf
	ls -n $dir >> $sf

 	mount_paths+=($dir)
done
echo >> $sf
echo "done"

echo "get sshfs options"
echo "[sshfs options]" >> $sf
sed 's/.*Options//' /data/data/ru.nsu.bobrofon.easysshfs/shared_prefs/mountpoints.xml >> $sf
echo >> $sf
echo "done"

echo "get selinux events"
echo "[selinux]" >> $sf
dmesg | grep 'avc: ' >> $sf
echo >> $sf
echo "done"
