#!/usr/bin/env bash
if [ ! "$EUID" == "0" ]
    then
        echo "Need start with sudo"
        exit
fi
A='npc'
if [ $# -gt 0 ]
    then
        A="$1"
fi
C="/usr/local/bin/$A"
E="$C"
if [ $# -gt 1 ]
    then
        A="sudo $A"
        E="sudo -u $2 $C"
        F="if [ ! \"\$EUID\" == \"0\" ]; then echo \"Need start with sudo\"; exit; fi"
fi
cat <<EOT > "$C"
#!/usr/bin/env bash
$F
case "\$1" in
"set" | "SET" )
${E}_setconfig.sh "\$2"
;;
"stop" | "STOP" )
${E}_endwork.sh
;;
"start" | "START" )
${E}_beginwork.sh
;;
"pull" | "PULL" )
${E}_integration.sh
;;
"help" | "HELP" )
echo "\"$A set config_name\" : setting configuration, parameter - name your config"
echo "\"$A stop\" : shutdown site"
echo "\"$A start\" : start site"
echo "\"$A pull\" : create backup & load last version"
;;
*)
echo "Usage: $A {set|help|stop|start|pull}"
;;
esac
EOT
chmod 755 "$C"
E="${C}_setconfig.sh"
B="$(pwd)/www/config.php";
D="$(pwd)/configs/config_";
cat <<EOT > "$E"
#!/usr/bin/env bash
if [ -z "\$1" ]; then echo "Not set parameter"; exit; fi
FILE="$D\$1.php"
if [ ! -f "\$FILE" ]; then echo "File config_\$1.php not found"; exit; fi
rm -f "$B"
cp "$D\$1.php" "$B"
chmod 755 "$B"
echo "Set config \$1 success"
EOT
chmod 755 "$E"
E="${C}_endwork.sh"
B="$(pwd)/www/index.php";
D="$(pwd)/www/stop.php";
F="$(pwd)/www/start.php";
cat <<EOT > "$E"
#!/usr/bin/env bash
FILE="$F"
if [ ! -f "\$FILE" ]
then
mv "$B" "$F"
mv "$D" "$B"
echo "Site shutdown success"
fi
EOT
chmod 755 "$E"
E="${C}_beginwork.sh"
B="$(pwd)/www/index.php";
D="$(pwd)/www/stop.php";
F="$(pwd)/www/start.php";
cat <<EOT > "$E"
#!/usr/bin/env bash
FILE="$D"
if [ ! -f "\$FILE" ]
then
mv "$B" "$D"
mv "$F" "$B"
echo "Site start success"
fi
EOT
chmod 755 "$E"
E="${C}_integration.sh"
B="$(pwd)";
cat <<EOT > "$E"
#!/usr/bin/env bash
cd "$B"
git stash
BR=\$(git branch | grep "*" | cut -c3-)
BN=\$(date '+%Y%m%d%H%M%S')
git checkout -b "\${BR}_\$BN"
echo "Backup to branch \${BR}_\$BN"
git checkout "\$BR"
${C}_endwork.sh
git pull
${C}_beginwork.sh
EOT
chmod 755 "$E"
echo "Please use \"$A command parameters\" to set configuration"
