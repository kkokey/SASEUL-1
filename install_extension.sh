pecl install ast-1.0.0

git clone git://github.com/encedo/php-ed25519-ext.git
cd php-ed25519-ext
phpize
./configure
make
make install

phpConfigFilePath=`php --ini | grep -o "/.*/php.ini"`

echo '
extension=memcached.so
extension=ed25519.so
extension=mongodb.so
' >> $phpConfigFilePath
