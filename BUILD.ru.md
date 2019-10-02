# Как собрать Zabbix из данного репозитария

[In English / По-английски](BUILD.md)

## Сборка на Oracle Linux 7 с поддержкой MySQL (MariaDB)

### 1. Для подготовки к сборки на Oracle Linux 7 нужно установить дополнительные пакеты:

~~~~
yum group install "Development Tools"
yum-config-manager --enable ol7_optional_latest
yum-config-manager --enable ol7_developer
yum install -y wget unzip libxml2-devel openssl-devel libcurl-devel net-snmp-devel libevent-devel sqlite-devel pcre-devel libssh2-devel iksemel-devel OpenIPMI-devel unixODBC-devel
yum install -y MariaDB-client MariaDB-devel MariaDB-shared
wget https://github.com/sass/dart-sass/releases/download/1.22.10/dart-sass-1.22.10-linux-x64.tar.gz
tar -zxf dart-sass-1.22.10-linux-x64.tar.gz
export PATH="~/dart-sass:$PATH"
~~~~

### 2. Скачать и распаковать свежую версию исходного кода:

~~~~
wget https://github.com/CHERTS/zabbix_34x_next/archive/master.zip -O zabbix-3.4.16.zip
unzip zabbix-3.4.16.zip
cd zabbix_34x_next-master
~~~~

### 3. Сборка всех компонентов Zabbix с поддержкой (MariaDB) MySQL:

~~~~
bash bootstrap.sh
./configure --with-libpthread --with-libpcre --with-libcurl --with-libxml2 --with-net-snmp --with-openssl --enable-ipv6 --with-ssh2 --with-jabber --with-openipmi --with-unixodbc --enable-server --enable-proxy --enable-agent --sysconfdir=/etc/zabbix --with-mysql
sed -i 's/sass --no-cache --sourcemap=none/sass/g' Makefile
make
make dbschema
make gettext
make css
~~~~

### 4. После успешной сборки на шаге 3 можно использовать бинарные файлы zabbix, скопируем их в текущий каталог:

~~~~
cp src/zabbix_server/zabbix_server zabbix_server_mysql_v3.4.16
cp src/zabbix_proxy/zabbix_proxy zabbix_proxy_mysql_v3.4.16
cp src/zabbix_agent/zabbix_agentd zabbix_agentd_v3.4.16
cp src/zabbix_sender/zabbix_sender zabbix_sender_v3.4.16
cp src/zabbix_get/zabbix_get zabbix_get_v3.4.16
~~~~

Проверим наличие бинарных файлов:

~~~~

# ls -l | grep 'zabbix_'
-rwxr-xr-x  1 root root  547032 Aug 20 15:47 zabbix_get_v3.4.16
-rwxr-xr-x  1 root root 4756752 Aug 20 15:44 zabbix_proxy_mysql_v3.4.16
-rwxr-xr-x  1 root root  641352 Aug 20 15:47 zabbix_sender_v3.4.16
-rwxr-xr-x  1 root root 6188120 Aug 20 15:44 zabbix_server_mysql_v3.4.16
~~~~

Теперь Вы можете остановить свои компоненты zabbix версии 3.4.15 и заменить их данной сборкой.


## Сборка на Ubuntu 18.04 LTS (Bionic Beaver) с поддержкой MySQL (MariaDB)

### 1. Для подготовки к сборки на Ubuntu 18.04 нужно установить дополнительные пакеты:

~~~~
sudo apt-get update
sudo apt-get install -y autoconf automake gcc make wget unzip libxml2-dev libssl-dev libcurl4-openssl-dev libsnmp-dev libevent-dev libsqlite3-dev libpcre2-dev libssh2-1-dev libiksemel-dev libmariadbclient-dev-compat libopenipmi-dev unixodbc-dev
wget https://github.com/sass/dart-sass/releases/download/1.22.10/dart-sass-1.22.10-linux-x64.tar.gz
tar -zxf dart-sass-1.22.10-linux-x64.tar.gz
export PATH="~/dart-sass:$PATH"
~~~~

### 2. Скачать и распаковать свежую версию исходного кода:

~~~~
wget https://github.com/CHERTS/zabbix_34x_next/archive/master.zip -O zabbix-3.4.16.zip
unzip zabbix-3.4.16.zip
cd zabbix_34x_next-master
~~~~

### 3. Сборка всех компонентов Zabbix с поддержкой (MariaDB) MySQL:

~~~~
bash bootstrap.sh
./configure --with-libpthread --with-libpcre --with-libcurl --with-libxml2 --with-net-snmp --with-openssl --enable-ipv6 --with-ssh2 --with-jabber --with-openipmi --with-unixodbc --enable-server --enable-proxy --enable-agent --sysconfdir=/etc/zabbix --with-mysql
sed -i 's/sass --no-cache --sourcemap=none/sass/g' Makefile
make
make dbschema
make gettext
make css
~~~~

### 4. После успешной сборки на шаге 3 можно использовать бинарные файлы zabbix, скопируем их в текущий каталог:

~~~~
cp src/zabbix_server/zabbix_server zabbix_server_mysql_v3.4.16
cp src/zabbix_proxy/zabbix_proxy zabbix_proxy_mysql_v3.4.16
cp src/zabbix_agent/zabbix_agentd zabbix_agentd_v3.4.16
cp src/zabbix_sender/zabbix_sender zabbix_sender_v3.4.16
cp src/zabbix_get/zabbix_get zabbix_get_v3.4.16
~~~~

Проверим наличие бинарных файлов:

~~~~

# ls -l | grep 'zabbix_'
-rwxr-xr-x  1 root root  547032 Aug 21 11:17 zabbix_get_v3.4.16
-rwxr-xr-x  1 root root 4756752 Aug 21 11:17 zabbix_proxy_mysql_v3.4.16
-rwxr-xr-x  1 root root  641352 Aug 21 11:17 zabbix_sender_v3.4.16
-rwxr-xr-x  1 root root 6188120 Aug 21 11:17 zabbix_server_mysql_v3.4.16
~~~~

Теперь Вы можете остановить свои компоненты zabbix версии 3.4.15 и заменить их данной сборкой.

