#!/bin/sh
VERSION=$(cat iou-web.spec| grep Version | cut -d' ' -f2)
RELEASE=$(cat iou-web.spec| grep Release | cut -d' ' -f2)
WORK_DIR=$(pwd)
BUILD_DIR=/tmp/iou-web

mkdir -p $BUILD_DIR
mkdir -p $BUILD_DIR/yum

rpmbuild -tb $BUILD_DIR/iou-web-$VERSION.tar.gz
rpm --resign --define "_gpg_name Andrea Dainese" /root/rpmbuild/RPMS/i386/iou-web-${VERSION}-${RELEASE}.i386.rpm
cp -a /root/rpmbuild/RPMS/i386/iou-web-${VERSION}-${RELEASE}.i386.rpm $BUILD_DIR/yum
createrepo $BUILD_DIR/yum

cat > $BUILD_DIR/yum/RPM-GPG-KEY-iou-web << EOF
-----BEGIN PGP PUBLIC KEY BLOCK-----
Version: GnuPG v2.0.14 (GNU/Linux)

mQGiBEr+s9sRBADe6dUVkYfdNNcla1apoLH3ahXQX0cKktOQQ9l0mXzRmjw02u18
P5reN+nNT0EQ8DdcWQ3fapFP71juRYl6un4pihsRnkiYzwdsHnfRpOaKYm99GSTl
urVYgn69An86OX0KF5nwacbVo8heEeQ0Tj3IFcWglkaMH/8NUSyE90HBZwCgnWQK
pBb2eglLLbmSNhrxU5tzdX0D/1E9FI4vdOoj9xlJf6wEVoW2rns34a/pioTIknhH
hs/GiW6VN64f+w26cb4b6TF5MMDxQbK7fTWN1aSSudDps8Wqbmu3c+ZUfOpVDgV5
qDsmhsOK8dzzwCgIu5r6SdyUBOhRnFOPcL1eajMT2N2UrxkFAO7yH8XIC/mbstF7
zB9oA/9zAVxuHi/pnfDyMZG6Pgk9zjoTiF2kct8PsMotM72pVL5/Ua46q18TbF2g
bPYe83NR/1/j5pcf+A658wiHZeIFJjoeN/rr3tqOiDPY+M9gyoYR+2AIy6LcMmBN
s5mElaFFuyYiDRMdpIvqyF8Av1ZRl3KDbKF1yfifgwWKbUJ4j7QpQW5kcmVhIERh
aW5lc2UgPGFuZHJlYS5kYWluZXNlQGdtYWlsLmNvbT6IYAQTEQIAIAUCSv6z2wIb
IwYLCQgHAwIEFQIIAwQWAgMBAh4BAheAAAoJEImuK5PnZeRF9H4An2huNUoH7NkC
myXmPCR+VK/JPFPsAJwJOuTJV7ONtjqI3OjOzWDNCYkEIbkCDQRK/rPbEAgAreuM
ha5OduiTRt1LsRKMphRkYUybXkRGPgooto0tXD2o7L4O8gdRXS5rIZ8OdmSWSNsw
LkdXzIyimWzYcMoV2+NF5hd+7k2SQ6M5QQLRg0YcWghfPnIv2OH/P2JBwo6Lf5Ug
79DilE59nR6XPdatdlTcAqWcvno4Rcyx69x+93utShCLb5SLG4IqSA5aL+DtFgcM
o6xHsg8UMZAF0vW/XVv8GN/TKo6FhhtkrM5Vj2p9OIwTaDUdvPhbgNTdJrHKXuBU
n/TRxJYpUyCgd3SdfsuZ9As4Z2/dm/5nH+R8WtxipqeNhTOkeJGwZDJ0jJJEIHuB
RjDkK9grs1dz3/9iZwADBQf+N98KmglWHxH7h7d045SJA+zs9IxR4EMXCUP8Xmqr
UFwp+w8VpeBPJLCU2jMj8qTIz9MaZCWRu06im30N8jaq2pSUAC0D9u8+zJsGi6Bx
PXG7nzup4yPaR3GkE4sGWIWVjnUH/p43M8La0p6BXL+TLJqkam/ch91Kly9k4K5o
yTcUNWVZzLkCY8iDVBo8Ktno75/5FA0Gqx0chn9614DKgAyRT8aTWMSwu+rSfCI4
zLAM3KExACUdSu2B4zUqdweX0GqCjiSpdnTdeu6RB8RmziBwo35voJzsMmb/1QeJ
0nEKDcUObP/syb97gOFKwEfYitOFMmYzgbVYnWT2jBJE+ohJBBgRAgAJBQJK/rPb
AhsMAAoJEImuK5PnZeRFu6cAn2gZVzUUqY/baT9l4kus11IaPdIsAKCckx6MIkdL
wRhWwZ/3JpGtBP8/hQ==
=soVA
-----END PGP PUBLIC KEY BLOCK-----
EOF
