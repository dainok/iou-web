#!/bin/sh
ARCH=$(cat control | grep Architecture | cut -d: -f2 | sed 's/ //')
VERSION=$(cat control | grep Version | cut -d: -f2 | sed 's/ //')
CONTROL=$(mktemp -dt)
DATA=$(mktemp -dt)
WORK_DIR=$(pwd)
BUILD_DIR=/tmp/iou-web

# Data DIR
cd $WORK_DIR
mkdir -p $DATA/opt/iou
mkdir -p $DATA/etc/sudoers.d
mkdir -p $DATA/etc/apache2/sites-available
mkdir -p $DATA/etc/apache2/sites-enabled
mkdir -p $DATA/etc/logrotate.d
ln -s ../sites-available/iou $DATA/etc/apache2/sites-enabled/001-iou
cp -a bin/ $DATA/opt/iou/
cp -a data/ $DATA/opt/iou/
cp -a html/ $DATA/opt/iou/
cp -a cgi-bin/ $DATA/opt/iou/
cp -a conf/apache.conf $DATA/etc/apache2/sites-available/iou
cat conf/sudo.conf | sed 's/apache/www-data/' | sed 's/apachectl/apache2ctl/' > $DATA/etc/sudoers.d/iou
cat conf/logrotate.conf | sed 's/apachectl/apache2ctl/' > $DATA/etc/logrotate.d/iou
cd $DATA
tar czf data.tar.gz opt etc

# Script postinst
cat > $CONTROL/postinst << EOF
#!/bin/sh
mkdir -p /tmp/iou
find /tmp/iou -type d -exec chmod 2755 {} \;
find /tmp/iou -type f -exec chmod 0644 {} \;
rm -f /etc/apache2/sites-enabled/000-default
rm -rf /opt/iou/log /opt/iou/html/downloads /opt/iou/labs /tmp/iou-web-export.db /tmp/iou-web-import.db /var/lib/php/session/*
mkdir -p /opt/iou/labs /opt/iou/data/Export /opt/iou/data/Import /opt/iou/data/Sniffer /opt/iou/data/Logs
chown www-data:www-data /opt/iou/bin /opt/iou/labs
chmod 755 /opt/iou/labs /opt/iou/data /opt/iou/data/Export /opt/iou/data/Import /opt/iou/data/Sniffer /opt/iou/data/Logs
chown -R www-data:www-data /opt/iou/data /tmp/iou
chmod 440 /etc/sudoers.d/iou
grep xml.cisco.com /etc/hosts > /dev/null
if [ $? -ne 0 ]; then
        echo '127.0.0.127 xml.cisco.com' >> /etc/hosts
fi
if [ ! -f /opt/iou/data/database.sdb ]; then
	cp -a /opt/iou/data/template.sdb /opt/iou/data/database.sdb
fi
echo -n "Updating the database: "
php -q /opt/iou/html/update.php
echo "done"
service apache2 restart
update-rc.d apache2 defaults
test -e /usr/lib/libcrypto.so.4
if [ $? -ne 0 ]; then
    rm -f /usr/lib/libcrypto.so.4
    if [ -f /usr/lib/libcrypto.so.10 ]; then
        ln -s libcrypto.so.10 /usr/lib/libcrypto.so.4
    then
        echo "File /usr/lib/libcrypto.so.10 not found, IOL won't start."
        exit 1
    fi
fi
EOF

# Control DIR
cd $WORK_DIR
cp -a control $CONTROL/
cd $DATA
find -type f | xargs md5sum > $CONTROL/md5sums
echo 2.0 > $CONTROL/debian-binary
cd $CONTROL
tar czf control.tar.gz md5sums control postinst

# Sign
cat $CONTROL/debian-binary $CONTROL/control.tar.gz $DATA/data.tar.gz > $CONTROL/combined-contents
gpg -abs -o $CONTROL/_gpgorigin $CONTROL/combined-contents

# Create the DEB package
mkdir -p $BUILD_DIR/apt/dists/binary-i386
mkdir -p $BUILD_DIR/apt/dists/binary-amd64
cd $WORK_DIR
ar -cr $BUILD_DIR/apt/iou-web_${VERSION}_${ARCH}.deb $CONTROL/debian-binary $CONTROL/control.tar.gz $DATA/data.tar.gz $CONTROL/_gpgorigin
cd $BUILD_DIR/apt
dpkg-scanpackages . > $BUILD_DIR/apt/dists/binary-i386/Packages
dpkg-scanpackages . > $BUILD_DIR/apt/dists/binary-amd64/Packages
gzip -c $BUILD_DIR/apt/dists/binary-i386/Packages > $BUILD_DIR/apt/dists/binary-i386/Packages.gz
gzip -c $BUILD_DIR/apt/dists/binary-amd64/Packages > $BUILD_DIR/apt/dists/binary-amd64/Packages.gz

# Cleaning
rm -Rf $CONTROL $DATA
exit 0
