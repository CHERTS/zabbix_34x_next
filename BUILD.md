# How to build Zabbix from this repository

[По-русски / In Russian](BUILD.ru.md)

## Build on Oracle Linux 7 with MySQL (MariaDB) support

### 1. To prepare for build on Oracle Linux 7, you need to install additional packages:

~~~~
yum group install "Development Tools"
yum-config-manager --enable ol7_optional_latest
yum-config-manager --enable ol7_developer
rpm -Uvh https://repo.zabbix.com/zabbix/3.4/rhel/7/x86_64/zabbix-release-3.4-2.el7.noarch.rpm
yum clean all
yum install -y wget unzip libxml2-devel openssl-devel libcurl-devel net-snmp-devel libevent-devel sqlite-devel pcre-devel libssh2-devel iksemel-devel
yum install -y MariaDB-client MariaDB-devel MariaDB-shared
~~~~

### 2. Download and unzip the latest version of the source code:

~~~~
wget https://github.com/CHERTS/zabbix_34x_next/archive/master.zip -O zabbix-3.4.16.zip
unzip zabbix-3.4.16.zip
~~~~

### 3. Build all Zabbix components with MySQL (MariaDB) support:

~~~~
autoreconf
./configure --with-libpcre --with-libcurl --with-libxml2 --with-net-snmp --with-openssl --enable-ipv6 --with-ssh2 --with-jabber --enable-server --enable-proxy --enable-agent --sysconfdir=/etc/zabbix --with-mysql
make
~~~~

### 4. After successful build, in step 3 you can use zabbix binaries, copy them to the current directory:

~~~~
cp src/zabbix_server/zabbix_server zabbix_server_mysql_v3.4.16
cp src/zabbix_proxy/zabbix_proxy zabbix_proxy_mysql_v3.4.16
cp src/zabbix_agent/zabbix_agentd zabbix_agentd_v3.4.16
cp src/zabbix_sender/zabbix_sender zabbix_sender_v3.4.16
cp src/zabbix_get/zabbix_get zabbix_get_v3.4.16
~~~~

Check for the presence of binary files:

~~~~

# ls -l | grep 'zabbix_'
-rwxr-xr-x  1 root root  547032 Aug 20 15:47 zabbix_get_v3.4.16
-rwxr-xr-x  1 root root 4756752 Aug 20 15:44 zabbix_proxy_mysql_v3.4.16
-rwxr-xr-x  1 root root  641352 Aug 20 15:47 zabbix_sender_v3.4.16
-rwxr-xr-x  1 root root 6188120 Aug 20 15:44 zabbix_server_mysql_v3.4.16
~~~~

Congratulations! Now you can stop your zabbix components version 3.4.15 and replace them with this build.

## Build on Ubuntu 18.04 LTS (Bionic Beaver) with MySQL (MariaDB) support

### 1. To prepare for build on Ubuntu 18.04, you need to install additional packages:

~~~~
sudo wget https://repo.zabbix.com/zabbix/3.4/ubuntu/pool/main/z/zabbix-release/zabbix-release_3.4-1%2Bbionic_all.deb
sudo dpkg -i zabbix-release_3.4-1+bionic_all.deb && sudo rm -f zabbix-release_3.4-1+bionic_all.deb
sudo apt-get update
sudo apt-get install -y autoconf automake gcc make wget unzip libxml2-dev libssl-dev libcurl4-openssl-dev libsnmp-dev libevent-dev libsqlite3-dev libpcre2-dev libssh2-1-dev libiksemel-dev libmariadbclient-dev-compat
~~~~

### 2. Download and unzip the latest version of the source code:

~~~~
wget https://github.com/CHERTS/zabbix_34x_next/archive/master.zip -O zabbix-3.4.16.zip
unzip zabbix-3.4.16.zip
~~~~

### 3. Build all Zabbix components with MySQL (MariaDB) support:

~~~~
autoreconf
./configure --with-libpcre --with-libcurl --with-libxml2 --with-net-snmp --with-openssl --enable-ipv6 --with-ssh2 --with-jabber --enable-server --enable-proxy --enable-agent --sysconfdir=/etc/zabbix --with-mysql
make
~~~~

### 4. After successful build, in step 3 you can use zabbix binaries, copy them to the current directory:

~~~~
cp src/zabbix_server/zabbix_server zabbix_server_mysql_v3.4.16
cp src/zabbix_proxy/zabbix_proxy zabbix_proxy_mysql_v3.4.16
cp src/zabbix_agent/zabbix_agentd zabbix_agentd_v3.4.16
cp src/zabbix_sender/zabbix_sender zabbix_sender_v3.4.16
cp src/zabbix_get/zabbix_get zabbix_get_v3.4.16
~~~~

Check for the presence of binary files:

~~~~

# ls -l | grep 'zabbix_'
-rwxr-xr-x  1 root root  547032 Aug 21 11:17 zabbix_get_v3.4.16
-rwxr-xr-x  1 root root 4756752 Aug 21 11:17 zabbix_proxy_mysql_v3.4.16
-rwxr-xr-x  1 root root  641352 Aug 21 11:17 zabbix_sender_v3.4.16
-rwxr-xr-x  1 root root 6188120 Aug 21 11:17 zabbix_server_mysql_v3.4.16
~~~~

Congratulations! Now you can stop your zabbix components version 3.4.15 and replace them with this build.
