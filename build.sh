#!/bin/sh

export WORK_DIR=$(pwd)
export BUILD_DIR=/tmp/iou-web
export VERSION=$(cat html/includes/conf.php | grep VERSION | cut -d\' -f4 | cut -d- -f1)
export RELEASE=$(cat html/includes/conf.php | grep VERSION | cut -d\' -f4 | cut -d- -f2)

rm -f iou-web.spec control
cat $WORK_DIR/iou-web.spec.template | sed "s/%VERSION%/${VERSION}/" | sed "s/%RELEASE%/${RELEASE}/" > $WORK_DIR/iou-web.spec
cat $WORK_DIR/control.template | sed "s/%VERSION%/${VERSION}/" | sed "s/%RELEASE%/${RELEASE}/" > $WORK_DIR/control
cat $WORK_DIR/FOOTER.html.template | sed "s/%VERSION%/${VERSION}/" | sed "s/%RELEASE%/${RELEASE}/" > $WORK_DIR/html/includes/FOOTER.html

rm -rf $BUILD_DIR
mkdir -p $BUILD_DIR
echo $VERSION-$RELEASE > $BUILD_DIR/version
cp whatsnew $BUILD_DIR/whatsnew
cat latest | sed "s/%VERSION%/${VERSION}/" | sed "s/%RELEASE%/${RELEASE}/" > $BUILD_DIR/latest
cd ..
tar -chzf $BUILD_DIR/iou-web-${VERSION}.tar.gz --exclude=.gitignore --exclude=.git --exclude=downloads --exclude=custom.php --exclude=database.sdb* iou-web-${VERSION}

cd $WORK_DIR
./build_deb.sh
cd $WORK_DIR
./build_rpm.sh
cd $WORK_DIR
rm -f iou-web.spec control

mv $BUILD_DIR/iou-web-${VERSION}.tar.gz $BUILD_DIR/iou-web-${VERSION}-${RELEASE}.tar.gz
