Name:		zabbix
Version:	3.4.16
Release:	1
Summary:	The Enterprise-class open source monitoring solution
Group:		Applications/Internet
License:	GPLv2+
URL:		http://www.zabbix.com/
Source0:	https://github.com/CHERTS/zabbix_34x_next/releases/download/v%{version}%{?alphatag:%{alphatag}}/zabbix-%{version}%{?alphatag:%{alphatag}}.tar.gz
Source1:	zabbix-web22.conf
Source2:	zabbix-web24.conf
Source3:	zabbix-logrotate.in
Source4:	zabbix-java-gateway.init
Source5:	zabbix-agent.init
Source6:	zabbix-server.init
Source7:	zabbix-proxy.init
Source10:	zabbix-agent.service
Source11:	zabbix-server.service
Source12:	zabbix-proxy.service
Source13:	zabbix-java-gateway.service
Source14:	zabbix_java_gateway-sysd
Source15:	zabbix-tmpfiles.conf
Patch0:		config.patch
Patch1:		fonts-config.patch
Patch2:		fping3-sourceip-option.patch

Buildroot:	%{_tmppath}/zabbix-%{version}-%{release}-root-%(%{__id_u} -n)

%if 0%{?rhel} >= 6
%define build_server 1
%endif

BuildRequires:	gcc
BuildRequires:	autoconf
BuildRequires:	automake
BuildRequires:	mysql-devel
BuildRequires:	postgresql-devel
BuildRequires:	net-snmp-devel
BuildRequires:	openldap-devel
BuildRequires:	gnutls-devel
BuildRequires:	sqlite-devel
BuildRequires:	unixODBC-devel
BuildRequires:	curl-devel >= 7.13.1
BuildRequires:	OpenIPMI-devel >= 2
BuildRequires:	libssh2-devel >= 1.0.0
BuildRequires:	java-devel >= 1.6.0
BuildRequires:	libxml2-devel
BuildRequires:	pcre-devel
BuildRequires:	libevent-devel
%if 0%{?rhel} >= 6
BuildRequires:	openssl-devel >= 1.0.1
%endif
%if 0%{?rhel} >= 7
BuildRequires:	systemd
%endif

%description
Zabbix is the ultimate enterprise-level software designed for
real-time monitoring of millions of metrics collected from tens of
thousands of servers, virtual machines and network devices.

%package agent
Summary:			Zabbix Agent
Group:				Applications/Internet
Requires:			logrotate
Requires(pre):		/usr/sbin/useradd
%if 0%{?rhel} >= 7
Requires(post):		systemd
Requires(preun):	systemd
Requires(preun):	systemd
%else
Requires(post):		/sbin/chkconfig
Requires(preun):	/sbin/chkconfig
Requires(preun):	/sbin/service
Requires(postun):	/sbin/service
%endif
Obsoletes:			zabbix

%description agent
Zabbix agent to be installed on monitored systems.

%package get
Summary:			Zabbix Get
Group:				Applications/Internet

%description get
Zabbix get command line utility

%package sender
Summary:			Zabbix Sender
Group:				Applications/Internet

%description sender
Zabbix sender command line utility

%package proxy-mysql
Summary:			Zabbix proxy for MySQL or MariaDB database
Group:				Applications/Internet
Requires:			fping
%if 0%{?rhel} >= 7
Requires(post):		systemd
Requires(preun):	systemd
Requires(postun):	systemd
%else
Requires(post):		/sbin/chkconfig
Requires(preun):	/sbin/chkconfig
Requires(preun):	/sbin/service
Requires(postun):	/sbin/service
%endif
Provides:			zabbix-proxy = %{version}-%{release}
Provides:			zabbix-proxy-implementation = %{version}-%{release}
Obsoletes:			zabbix
Obsoletes:			zabbix-proxy

%description proxy-mysql
Zabbix proxy with MySQL or MariaDB database support.

%package proxy-pgsql
Summary:			Zabbix proxy for PostgreSQL database
Group:				Applications/Internet
Requires:			fping
%if 0%{?rhel} >= 7
Requires(post):		systemd
Requires(preun):	systemd
Requires(postun):	systemd
%else
Requires(post):		/sbin/chkconfig
Requires(preun):	/sbin/chkconfig
Requires(preun):	/sbin/service
Requires(postun):	/sbin/service
%endif
Provides:			zabbix-proxy = %{version}-%{release}
Provides:			zabbix-proxy-implementation = %{version}-%{release}
Obsoletes:			zabbix
Obsoletes:			zabbix-proxy

%description proxy-pgsql
Zabbix proxy with PostgreSQL database support.

%package proxy-sqlite3
Summary:			Zabbix proxy for SQLite3 database
Group:				Applications/Internet
Requires:			fping
%if 0%{?rhel} >= 7
Requires(post):		systemd
Requires(preun):	systemd
Requires(postun):	systemd
%else
Requires(post):		/sbin/chkconfig
Requires(preun):	/sbin/chkconfig
Requires(preun):	/sbin/service
Requires(postun):	/sbin/service
%endif
Provides:			zabbix-proxy = %{version}-%{release}
Provides:			zabbix-proxy-implementation = %{version}-%{release}
Obsoletes:			zabbix
Obsoletes:			zabbix-proxy

%description proxy-sqlite3
Zabbix proxy with SQLite3 database support.

%package java-gateway
Summary:			Zabbix java gateway
Group:				Applications/Internet
%if 0%{?rhel} >= 7
Requires:			java-headless >= 1.6.0
%else
Requires:			java >= 1.6.0
%endif
%if 0%{?rhel} >= 7
Requires(post):		systemd
Requires(preun):	systemd
Requires(postun):	systemd
%else
Requires(post):		/sbin/chkconfig
Requires(preun):	/sbin/chkconfig
Requires(preun):	/sbin/service
Requires(postun):	/sbin/service
%endif
Obsoletes:			zabbix

%description java-gateway
Zabbix java gateway

%if 0%{?build_server}
%package server-mysql
Summary:			Zabbix server for MySQL or MariaDB database
Group:				Applications/Internet
Requires:			fping
%if 0%{?rhel} >= 7
Requires(post):		systemd
Requires(preun):	systemd
Requires(postun):	systemd
%else
Requires(post):		/sbin/chkconfig
Requires(preun):	/sbin/chkconfig
Requires(preun):	/sbin/service
Requires(postun):	/sbin/service
%endif
Provides:			zabbix-server = %{version}-%{release}
Provides:			zabbix-server-implementation = %{version}-%{release}
Obsoletes:			zabbix
Obsoletes:			zabbix-server

%description server-mysql
Zabbix server with MySQL or MariaDB database support.

%package server-pgsql
Summary:			Zabbix server for PostgresSQL database
Group:				Applications/Internet
Requires:			fping
%if 0%{?rhel} >= 7
Requires(post):		systemd
Requires(preun):	systemd
Requires(postun):	systemd
%else
Requires(post):		/sbin/chkconfig
Requires(preun):	/sbin/chkconfig
Requires(preun):	/sbin/service
Requires(postun):	/sbin/service
%endif
Provides:			zabbix-server = %{version}-%{release}
Provides:			zabbix-server-implementation = %{version}-%{release}
Obsoletes:			zabbix
Obsoletes:			zabbix-server
%description server-pgsql
Zabbix server with PostgresSQL database support.

%package web
Summary:			Zabbix web frontend common package
Group:				Application/Internet
BuildArch:			noarch
%if 0%{?rhel} >= 7
Requires:			httpd
Requires:			php >= 5.4
Requires:			php-gd
Requires:			php-bcmath
Requires:			php-mbstring
Requires:			php-xml
Requires:			php-ldap
%endif
Requires:			dejavu-sans-fonts
Requires:			zabbix-web-database = %{version}-%{release}
Requires(post):		%{_sbindir}/update-alternatives
Requires(preun):	%{_sbindir}/update-alternatives

%description web
Zabbix web frontend common package

%package web-mysql
Summary:			Zabbix web frontend for MySQL
Group:				Applications/Internet
BuildArch:			noarch
%if 0%{?rhel} == 7
Requires:			php-mysql
%endif
%if 0%{?rhel} == 8
Requires:			php-mysqlnd
%endif
Requires:			zabbix-web = %{version}-%{release}
Provides:			zabbix-web-database = %{version}-%{release}

%description web-mysql
Zabbix web frontend for MySQL

%package web-pgsql
Summary:			Zabbix web frontend for PostgreSQL
Group:				Applications/Internet
BuildArch:			noarch
%if 0%{?rhel} >= 7
Requires:			php-pgsql
%endif
Requires:			zabbix-web = %{version}-%{release}
Provides:			zabbix-web-database = %{version}-%{release}

%description web-pgsql
Zabbix web frontend for PostgreSQL

%package web-japanese
Summary:			Japanese font settings for frontend
Group:				Applications/Internet
BuildArch:			noarch
%if 0%{?rhel} >= 8
Requires:			google-noto-sans-cjk-ttc-fonts
%else
Requires:			vlgothic-p-fonts
%endif
Requires:			zabbix-web = %{version}-%{release}
Requires(post):		%{_sbindir}/update-alternatives
Requires(preun):	%{_sbindir}/update-alternatives

%description web-japanese
Japanese font configuration for Zabbix web frontend
%endif

%prep
%setup0 -q -n %{name}-%{version}%{?alphatag:%{alphatag}}
%patch0 -p1
%patch1 -p1
%if 0%{?rhel} >= 7
%patch2 -p1
%endif

## remove font file
rm -f frontends/php/fonts/DejaVuSans.ttf

# remove .htaccess files
rm -f frontends/php/app/.htaccess
rm -f frontends/php/conf/.htaccess
rm -f frontends/php/include/.htaccess
rm -f frontends/php/local/.htaccess

# remove translation source files and scripts
find frontends/php/locale -name '*.po' | xargs rm -f
find frontends/php/locale -name '*.sh' | xargs rm -f

# traceroute command path for global script
sed -i -e 's|/usr/bin/traceroute|/bin/traceroute|' database/mysql/data.sql
sed -i -e 's|/usr/bin/traceroute|/bin/traceroute|' database/postgresql/data.sql
sed -i -e 's|/usr/bin/traceroute|/bin/traceroute|' database/sqlite3/data.sql

# change log directory for Java Gateway
sed -i -e 's|/tmp/zabbix_java.log|/var/log/zabbix/zabbix_java_gateway.log|g' src/zabbix_java/lib/logback.xml

%if 0%{?build_server}
# copy sql files for servers
cat database/mysql/schema.sql > database/mysql/create.sql
cat database/mysql/images.sql >> database/mysql/create.sql
cat database/mysql/data.sql >> database/mysql/create.sql
gzip database/mysql/create.sql

cat database/postgresql/schema.sql > database/postgresql/create.sql
cat database/postgresql/images.sql >> database/postgresql/create.sql
cat database/postgresql/data.sql >> database/postgresql/create.sql
gzip database/postgresql/create.sql
%endif

# sql files for proxyes
gzip database/mysql/schema.sql
gzip database/postgresql/schema.sql
gzip database/sqlite3/schema.sql


%build

build_flags="
	--enable-dependency-tracking
	--sysconfdir=/etc/zabbix
	--libdir=%{_libdir}/zabbix
	--enable-agent
	--enable-proxy
	--enable-ipv6
	--enable-java
	--with-net-snmp
	--with-ldap
	--with-libcurl
	--with-openipmi
	--with-unixodbc
	--with-ssh2
	--with-libxml2
	--with-libevent
	--with-libpcre
"

%if 0%{?rhel} >=6
build_flags="$build_flags --with-openssl"
%endif

autoreconf -iv

%configure $build_flags --with-sqlite3
make %{?_smp_mflags}
mv src/zabbix_proxy/zabbix_proxy src/zabbix_proxy/zabbix_proxy_sqlite3

%if 0%{?build_server}
build_flags="$build_flags --enable-server"
%endif

%configure $build_flags --with-mysql
make %{?_smp_mflags}
%if 0%{?build_server}
mv src/zabbix_server/zabbix_server src/zabbix_server/zabbix_server_mysql
%endif
mv src/zabbix_proxy/zabbix_proxy src/zabbix_proxy/zabbix_proxy_mysql

%configure $build_flags --with-postgresql
make %{?_smp_mflags}
%if 0%{?build_server}
mv src/zabbix_server/zabbix_server src/zabbix_server/zabbix_server_pgsql
%endif
mv src/zabbix_proxy/zabbix_proxy src/zabbix_proxy/zabbix_proxy_pgsql

%if 0%{?build_server}
touch src/zabbix_server/zabbix_server
%endif
touch src/zabbix_proxy/zabbix_proxy



%install

rm -rf $RPM_BUILD_ROOT

# install
make DESTDIR=$RPM_BUILD_ROOT install

# install necessary directories
mkdir -p $RPM_BUILD_ROOT%{_localstatedir}/log/zabbix
mkdir -p $RPM_BUILD_ROOT%{_localstatedir}/run/zabbix

# install server and proxy binaries
%if 0%{?build_server}
install -m 0755 -p src/zabbix_server/zabbix_server_* $RPM_BUILD_ROOT%{_sbindir}/
rm $RPM_BUILD_ROOT%{_sbindir}/zabbix_server
%endif
install -m 0755 -p src/zabbix_proxy/zabbix_proxy_* $RPM_BUILD_ROOT%{_sbindir}/
rm $RPM_BUILD_ROOT%{_sbindir}/zabbix_proxy

# delete unnecessary files from java gateway
rm $RPM_BUILD_ROOT%{_sbindir}/zabbix_java/settings.sh
rm $RPM_BUILD_ROOT%{_sbindir}/zabbix_java/startup.sh
rm $RPM_BUILD_ROOT%{_sbindir}/zabbix_java/shutdown.sh

# install scripts and modules directories
mkdir -p $RPM_BUILD_ROOT/usr/lib/zabbix
%if 0%{?build_server}
mv $RPM_BUILD_ROOT%{_datadir}/zabbix/alertscripts $RPM_BUILD_ROOT/usr/lib/zabbix
%endif
mv $RPM_BUILD_ROOT%{_datadir}/zabbix/externalscripts $RPM_BUILD_ROOT/usr/lib/zabbix
mkdir $RPM_BUILD_ROOT%{_libdir}/zabbix/modules

%if 0%{?rhel} >=7
mv $RPM_BUILD_ROOT%{_sbindir}/zabbix_java/lib/logback.xml $RPM_BUILD_ROOT/%{_sysconfdir}/zabbix/zabbix_java_gateway_logback.xml
rm $RPM_BUILD_ROOT%{_sbindir}/zabbix_java/lib/logback-console.xml
mv $RPM_BUILD_ROOT%{_sbindir}/zabbix_java $RPM_BUILD_ROOT/%{_datadir}/zabbix-java-gateway
install -m 0755 -p %{SOURCE14} $RPM_BUILD_ROOT%{_sbindir}/zabbix_java_gateway
%endif

%if 0%{?build_server}
# install frontend files
find frontends/php -name '*.orig' | xargs rm -f
cp -a frontends/php/* $RPM_BUILD_ROOT%{_datadir}/zabbix

# install frontend configuration files
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/web
touch $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/web/zabbix.conf.php
mv $RPM_BUILD_ROOT%{_datadir}/zabbix/conf/maintenance.inc.php $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/web/

# drop config files in place
%if 0%{?rhel} >= 7
install -Dm 0644 -p %{SOURCE2} $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d/zabbix.conf
%else
install -Dm 0644 -p %{SOURCE1} conf/httpd22-example.conf
install -Dm 0644 -p %{SOURCE2} conf/httpd24-example.conf
%endif
%endif

# install configuration files
mv $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/zabbix_agentd.conf.d $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/zabbix_agentd.d
mv $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/zabbix_proxy.conf.d $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/zabbix_proxy.d
%if 0%{?build_server}
mv $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/zabbix_server.conf.d $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/zabbix_server.d
%endif

install -dm 755 $RPM_BUILD_ROOT%{_docdir}/zabbix-agent-%{version}

install -m 0644 conf/zabbix_agentd/userparameter_mysql.conf $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/zabbix_agentd.d

cat conf/zabbix_agentd.conf | sed \
	-e '/^# PidFile=/a \\nPidFile=%{_localstatedir}/run/zabbix/zabbix_agentd.pid' \
	-e 's|^LogFile=.*|LogFile=%{_localstatedir}/log/zabbix/zabbix_agentd.log|g' \
	-e '/^# LogFileSize=.*/a \\nLogFileSize=0' \
	-e '/^# Include=$/a \\nInclude=%{_sysconfdir}/zabbix/zabbix_agentd.d/*.conf' \
	> $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/zabbix_agentd.conf

%if 0%{?build_server}
cat conf/zabbix_server.conf | sed \
	-e '/^# PidFile=/a \\nPidFile=%{_localstatedir}/run/zabbix/zabbix_server.pid' \
	-e 's|^LogFile=.*|LogFile=%{_localstatedir}/log/zabbix/zabbix_server.log|g' \
	-e '/^# LogFileSize=/a \\nLogFileSize=0' \
	-e '/^# AlertScriptsPath=/a \\nAlertScriptsPath=/usr/lib/zabbix/alertscripts' \
	-e '/^# ExternalScripts=/a \\nExternalScripts=/usr/lib/zabbix/externalscripts' \
	-e 's|^DBUser=root|DBUser=zabbix|g' \
	-e '/^# SNMPTrapperFile=.*/a \\nSNMPTrapperFile=/var/log/snmptrap/snmptrap.log' \
	-e '/^# SocketDir=.*/a \\nSocketDir=/var/run/zabbix' \
	> $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/zabbix_server.conf
%endif

cat conf/zabbix_proxy.conf | sed \
	-e '/^# PidFile=/a \\nPidFile=%{_localstatedir}/run/zabbix/zabbix_proxy.pid' \
	-e 's|^LogFile=.*|LogFile=%{_localstatedir}/log/zabbix/zabbix_proxy.log|g' \
	-e '/^# LogFileSize=/a \\nLogFileSize=0' \
	-e '/^# ExternalScripts=/a \\nExternalScripts=/usr/lib/zabbix/externalscripts' \
	-e 's|^DBUser=root|DBUser=zabbix|g' \
	-e '/^# SNMPTrapperFile=.*/a \\nSNMPTrapperFile=/var/log/snmptrap/snmptrap.log' \
	-e '/^# SocketDir=.*/a \\nSocketDir=/var/run/zabbix' \
	> $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/zabbix_proxy.conf

cat src/zabbix_java/settings.sh | sed \
	-e 's|^PID_FILE=.*|PID_FILE="/var/run/zabbix/zabbix_java.pid"|g' \
	-e '/^# TIMEOUT=/a \\nTIMEOUT=3' \
	> $RPM_BUILD_ROOT%{_sysconfdir}/zabbix/zabbix_java_gateway.conf

# install logrotate configuration files
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/logrotate.d
%if 0%{?build_server}
cat %{SOURCE3} | sed \
	-e 's|COMPONENT|server|g' \
	> $RPM_BUILD_ROOT%{_sysconfdir}/logrotate.d/zabbix-server
%endif
cat %{SOURCE3} | sed \
	-e 's|COMPONENT|agentd|g' \
	> $RPM_BUILD_ROOT%{_sysconfdir}/logrotate.d/zabbix-agent
cat %{SOURCE3} | sed \
	-e 's|COMPONENT|proxy|g' \
	> $RPM_BUILD_ROOT%{_sysconfdir}/logrotate.d/zabbix-proxy

# install startup scripts
%if 0%{?rhel} >= 7
install -Dm 0644 -p %{SOURCE10} $RPM_BUILD_ROOT%{_unitdir}/zabbix-agent.service
%if 0%{?build_server}
install -Dm 0644 -p %{SOURCE11} $RPM_BUILD_ROOT%{_unitdir}/zabbix-server.service
%endif
install -Dm 0644 -p %{SOURCE12} $RPM_BUILD_ROOT%{_unitdir}/zabbix-proxy.service
install -Dm 0644 -p %{SOURCE13} $RPM_BUILD_ROOT%{_unitdir}/zabbix-java-gateway.service
%else
install -Dm 0755 -p %{SOURCE4} $RPM_BUILD_ROOT%{_sysconfdir}/init.d/zabbix-java-gateway
install -Dm 0755 -p %{SOURCE5} $RPM_BUILD_ROOT%{_sysconfdir}/init.d/zabbix-agent
%if 0%{?build_server}
install -Dm 0755 -p %{SOURCE6} $RPM_BUILD_ROOT%{_sysconfdir}/init.d/zabbix-server
%endif
install -Dm 0755 -p %{SOURCE7} $RPM_BUILD_ROOT%{_sysconfdir}/init.d/zabbix-proxy
%endif

# install systemd-tmpfiles conf
%if 0%{?rhel} >= 7
install -Dm 0644 -p %{SOURCE15} $RPM_BUILD_ROOT%{_prefix}/lib/tmpfiles.d/zabbix-agent.conf
%if 0%{?build_server}
install -Dm 0644 -p %{SOURCE15} $RPM_BUILD_ROOT%{_prefix}/lib/tmpfiles.d/zabbix-server.conf
%endif
install -Dm 0644 -p %{SOURCE15} $RPM_BUILD_ROOT%{_prefix}/lib/tmpfiles.d/zabbix-proxy.conf
install -Dm 0644 -p %{SOURCE15} $RPM_BUILD_ROOT%{_prefix}/lib/tmpfiles.d/zabbix-java-gateway.conf
%endif


%clean
rm -rf $RPM_BUILD_ROOT


%pre agent
getent group zabbix > /dev/null || groupadd -r zabbix
getent passwd zabbix > /dev/null || \
	useradd -r -g zabbix -d %{_localstatedir}/lib/zabbix -s /sbin/nologin \
	-c "Zabbix Monitoring System" zabbix
:

%post agent
%if 0%{?rhel} >= 7
%systemd_post zabbix-agent.service
%else
/sbin/chkconfig --add zabbix-agent || :
%endif

%pre proxy-mysql
getent group zabbix > /dev/null || groupadd -r zabbix
getent passwd zabbix > /dev/null || \
	useradd -r -g zabbix -d %{_localstatedir}/lib/zabbix -s /sbin/nologin \
	-c "Zabbix Monitoring System" zabbix
:

%pre proxy-pgsql
getent group zabbix > /dev/null || groupadd -r zabbix
getent passwd zabbix > /dev/null || \
	useradd -r -g zabbix -d %{_localstatedir}/lib/zabbix -s /sbin/nologin \
	-c "Zabbix Monitoring System" zabbix
:

%pre proxy-sqlite3
getent group zabbix > /dev/null || groupadd -r zabbix
getent passwd zabbix > /dev/null || \
	useradd -r -g zabbix -d %{_localstatedir}/lib/zabbix -s /sbin/nologin \
	-c "Zabbix Monitoring System" zabbix
:

%pre java-gateway
getent group zabbix > /dev/null || groupadd -r zabbix
getent passwd zabbix > /dev/null || \
	useradd -r -g zabbix -d %{_localstatedir}/lib/zabbix -s /sbin/nologin \
	-c "Zabbix Monitoring System" zabbix
:

%if 0%{?build_server}
%pre server-mysql
getent group zabbix > /dev/null || groupadd -r zabbix
getent passwd zabbix > /dev/null || \
	useradd -r -g zabbix -d %{_localstatedir}/lib/zabbix -s /sbin/nologin \
	-c "Zabbix Monitoring System" zabbix
:

%pre server-pgsql
getent group zabbix > /dev/null || groupadd -r zabbix
getent passwd zabbix > /dev/null || \
	useradd -r -g zabbix -d %{_localstatedir}/lib/zabbix -s /sbin/nologin \
	-c "Zabbix Monitoring System" zabbix
:
%endif


%post proxy-mysql
%if 0%{?rhel} >= 7
%systemd_post zabbix-proxy.service
%else
/sbin/chkconfig --add zabbix-proxy
%endif
/usr/sbin/update-alternatives --install %{_sbindir}/zabbix_proxy \
	zabbix-proxy %{_sbindir}/zabbix_proxy_mysql 10
:

%post proxy-pgsql
%if 0%{?rhel} >= 7
%systemd_post zabbix-proxy.service
%else
/sbin/chkconfig --add zabbix-proxy
%endif
/usr/sbin/update-alternatives --install %{_sbindir}/zabbix_proxy \
	zabbix-proxy %{_sbindir}/zabbix_proxy_pgsql 10
:

%post proxy-sqlite3
%if 0%{?rhel} >= 7
%systemd_post zabbix-proxy.service
%else
/sbin/chkconfig --add zabbix-proxy
%endif
/usr/sbin/update-alternatives --install %{_sbindir}/zabbix_proxy \
	zabbix-proxy %{_sbindir}/zabbix_proxy_sqlite3 10
:

%post java-gateway
%if 0%{?rhel} >= 7
%systemd_post zabbix-java-gateway.service
%else
/sbin/chkconfig --add zabbix-java-gateway
%endif
:

%if 0%{?build_server}
%post server-mysql
%if 0%{?rhel} >= 7
%systemd_post zabbix-server.service
%else
/sbin/chkconfig --add zabbix-server
%endif
/usr/sbin/update-alternatives --install %{_sbindir}/zabbix_server \
	zabbix-server %{_sbindir}/zabbix_server_mysql 10
:

%post server-pgsql
%if 0%{?rhel} >= 7
%systemd_post zabbix-server.service
%else
/sbin/chkconfig --add zabbix-server
%endif
/usr/sbin/update-alternatives --install %{_sbindir}/zabbix_server \
	zabbix-server %{_sbindir}/zabbix_server_pgsql 10
:

%post web
/usr/sbin/update-alternatives --install %{_datadir}/zabbix/fonts/graphfont.ttf \
	zabbix-web-font %{_datadir}/fonts/dejavu/DejaVuSans.ttf 10
:

%post web-japanese
/usr/sbin/update-alternatives --install %{_datadir}/zabbix/fonts/graphfont.ttf zabbix-web-font \
%if 0%{?rhel} >= 8
	%{_datadir}/fonts/google-noto-cjk/NotoSansCJK-Regular.ttc 20
%else
	%{_datadir}/fonts/vlgothic/VL-PGothic-Regular.ttf 20
%endif
:
%endif

%preun agent
if [ "$1" = 0 ]; then
%if 0%{?rhel} >= 7
%systemd_preun zabbix-agent.service
%else
/sbin/service zabbix-agent stop >/dev/null 2>&1
/sbin/chkconfig --del zabbix-agent
%endif
fi
:


%preun proxy-mysql
if [ "$1" = 0 ]; then
%if 0%{?rhel} >= 7
%systemd_preun zabbix-proxy.service
%else
/sbin/service zabbix-proxy stop >/dev/null 2>&1
/sbin/chkconfig --del zabbix-proxy
%endif
/usr/sbin/update-alternatives --remove zabbix-proxy \
%{_sbindir}/zabbix_proxy_mysql
fi
:

%preun proxy-pgsql
if [ "$1" = 0 ]; then
%if 0%{?rhel} >= 7
%systemd_preun zabbix-proxy.service
%else
/sbin/service zabbix-proxy stop >/dev/null 2>&1
/sbin/chkconfig --del zabbix-proxy
%endif
/usr/sbin/update-alternatives --remove zabbix-proxy \
	%{_sbindir}/zabbix_proxy_pgsql
fi
:

%preun proxy-sqlite3
if [ "$1" = 0 ]; then
%if 0%{?rhel} >= 7
%systemd_preun zabbix-proxy.service
%else
/sbin/service zabbix-proxy stop >/dev/null 2>&1
/sbin/chkconfig --del zabbix-proxy
%endif
/usr/sbin/update-alternatives --remove zabbix-proxy \
	%{_sbindir}/zabbix_proxy_sqlite3
fi
:

%preun java-gateway
if [ $1 -eq 0 ]; then
%if 0%{?rhel} >= 7
%systemd_preun zabbix-java-gateway.service
%else
/sbin/service zabbix-java-gateway stop >/dev/null 2>&1
/sbin/chkconfig --del zabbix-java-gateway
%endif
fi
:

%if 0%{?build_server}
%preun server-mysql
if [ "$1" = 0 ]; then
%if 0%{?rhel} >= 7
%systemd_preun zabbix-server.service
%else
/sbin/service zabbix-server stop >/dev/null 2>&1
/sbin/chkconfig --del zabbix-server
%endif
/usr/sbin/update-alternatives --remove zabbix-server \
	%{_sbindir}/zabbix_server_mysql
fi
:

%preun server-pgsql
if [ "$1" = 0 ]; then
%if 0%{?rhel} >= 7
%systemd_preun zabbix-server.service
%else
/sbin/service zabbix-server stop >/dev/null 2>&1
/sbin/chkconfig --del zabbix-server
%endif
/usr/sbin/update-alternatives --remove zabbix-server \
	%{_sbindir}/zabbix_server_pgsql
fi
:

%preun web
if [ "$1" = 0 ]; then
/usr/sbin/update-alternatives --remove zabbix-web-font \
%if 0%{?rhel} >= 8
	%{_datadir}/fonts/google-noto-cjk/NotoSansCJK-Regular.ttc
%else
	%{_datadir}/fonts/dejavu/DejaVuSans.ttf
%endif
fi
:

%preun web-japanese
if [ "$1" = 0 ]; then
/usr/sbin/update-alternatives --remove zabbix-web-font \
	%{_datadir}/fonts/vlgothic/VL-PGothic-Regular.ttf
fi
:
%endif

%postun agent
%if 0%{?rhel} >= 7
%systemd_postun_with_restart zabbix-agent.service
%else
if [ $1 -ge 1 ]; then
/sbin/service zabbix-agent try-restart >/dev/null 2>&1 || :
fi
%endif

%postun proxy-mysql
%if 0%{?rhel} >= 7
%systemd_postun_with_restart zabbix-proxy.service
%else
if [ $1 -ge 1 ]; then
/sbin/service zabbix-proxy try-restart >/dev/null 2>&1 || :
fi
%endif

%postun proxy-pgsql
%if 0%{?rhel} >= 7
%systemd_postun_with_restart zabbix-proxy.service
%else
if [ $1 -ge 1 ]; then
/sbin/service zabbix-proxy try-restart >/dev/null 2>&1 || :
fi
%endif

%postun proxy-sqlite3
%if 0%{?rhel} >= 7
%systemd_postun_with_restart zabbix-proxy.service
%else
if [ $1 -ge 1 ]; then
/sbin/service zabbix-proxy try-restart >/dev/null 2>&1 || :
fi
%endif

%postun java-gateway
%if 0%{?rhel} >= 7
%systemd_postun_with_restart zabbix-java-gateway.service
%else
if [ $1 -gt 1 ]; then
/sbin/service zabbix-java-gateway condrestart >/dev/null 2>&1 || :
fi
%endif

%if 0%{?build_server}
%postun server-mysql
%if 0%{?rhel} >= 7
%systemd_postun_with_restart zabbix-server.service
%else
if [ $1 -ge 1 ]; then
/sbin/service zabbix-server try-restart >/dev/null 2>&1 || :
fi
%endif

%postun server-pgsql
%if 0%{?rhel} >= 7
%systemd_postun_with_restart zabbix-server.service
%else
if [ $1 -ge 1 ]; then
/sbin/service zabbix-server try-restart >/dev/null 2>&1 || :
fi
%endif
%endif

%files agent
%defattr(-,root,root,-)
%doc AUTHORS ChangeLog COPYING NEWS README
%config(noreplace) %{_sysconfdir}/zabbix/zabbix_agentd.conf
%config(noreplace) %{_sysconfdir}/logrotate.d/zabbix-agent
%dir %{_sysconfdir}/zabbix/zabbix_agentd.d
%config(noreplace) %{_sysconfdir}/zabbix/zabbix_agentd.d/userparameter_mysql.conf
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/log/zabbix
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/run/zabbix
%{_sbindir}/zabbix_agentd
%{_mandir}/man8/zabbix_agentd.8*
%if 0%{?rhel} >= 7
%{_unitdir}/zabbix-agent.service
%{_prefix}/lib/tmpfiles.d/zabbix-agent.conf
%else
%{_sysconfdir}/init.d/zabbix-agent
%endif


%files get
%defattr(-,root,root,-)
%doc AUTHORS ChangeLog COPYING NEWS README
%{_bindir}/zabbix_get
%{_mandir}/man1/zabbix_get.1*

%files sender
%defattr(-,root,root,-)
%doc AUTHORS ChangeLog COPYING NEWS README
%{_bindir}/zabbix_sender
%{_mandir}/man1/zabbix_sender.1*

%files proxy-mysql
%defattr(-,root,root,-)
%doc AUTHORS ChangeLog COPYING NEWS README
%doc database/mysql/schema.sql.gz
%attr(0640,root,zabbix) %config(noreplace) %{_sysconfdir}/zabbix/zabbix_proxy.conf
%dir /usr/lib/zabbix/externalscripts
%config(noreplace) %{_sysconfdir}/logrotate.d/zabbix-proxy
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/log/zabbix
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/run/zabbix
%{_mandir}/man8/zabbix_proxy.8*
%if 0%{?rhel} >= 7
%{_unitdir}/zabbix-proxy.service
%{_prefix}/lib/tmpfiles.d/zabbix-proxy.conf
%else
%{_sysconfdir}/init.d/zabbix-proxy
%endif
%{_sbindir}/zabbix_proxy_mysql

%files proxy-pgsql
%defattr(-,root,root,-)
%doc AUTHORS ChangeLog COPYING NEWS README
%doc database/postgresql/schema.sql.gz
%attr(0640,root,zabbix) %config(noreplace) %{_sysconfdir}/zabbix/zabbix_proxy.conf
%dir /usr/lib/zabbix/externalscripts
%config(noreplace) %{_sysconfdir}/logrotate.d/zabbix-proxy
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/log/zabbix
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/run/zabbix
%{_mandir}/man8/zabbix_proxy.8*
%if 0%{?rhel} >= 7
%{_unitdir}/zabbix-proxy.service
%{_prefix}/lib/tmpfiles.d/zabbix-proxy.conf
%else
%{_sysconfdir}/init.d/zabbix-proxy
%endif
%{_sbindir}/zabbix_proxy_pgsql

%files proxy-sqlite3
%defattr(-,root,root,-)
%doc AUTHORS ChangeLog COPYING NEWS README
%doc database/sqlite3/schema.sql.gz
%attr(0640,root,zabbix) %config(noreplace) %{_sysconfdir}/zabbix/zabbix_proxy.conf
%dir /usr/lib/zabbix/externalscripts
%config(noreplace) %{_sysconfdir}/logrotate.d/zabbix-proxy
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/log/zabbix
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/run/zabbix
%{_mandir}/man8/zabbix_proxy.8*
%if 0%{?rhel} >= 7
%{_unitdir}/zabbix-proxy.service
%{_prefix}/lib/tmpfiles.d/zabbix-proxy.conf
%else
%{_sysconfdir}/init.d/zabbix-proxy
%endif
%{_sbindir}/zabbix_proxy_sqlite3

%files java-gateway
%defattr(-,root,root,-)
%doc AUTHORS ChangeLog COPYING NEWS README
%config(noreplace) %{_sysconfdir}/zabbix/zabbix_java_gateway.conf
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/log/zabbix
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/run/zabbix
%if 0%{?rhel} >= 7
%{_datadir}/zabbix-java-gateway
%{_sbindir}/zabbix_java_gateway
%{_unitdir}/zabbix-java-gateway.service
%{_prefix}/lib/tmpfiles.d/zabbix-java-gateway.conf
%config(noreplace) %{_sysconfdir}/zabbix/zabbix_java_gateway_logback.xml
%else
%{_sbindir}/zabbix_java
%{_sysconfdir}/init.d/zabbix-java-gateway
%endif

%if 0%{?build_server}
%files server-mysql
%defattr(-,root,root,-)
%doc AUTHORS ChangeLog COPYING NEWS README
%doc database/mysql/create.sql.gz
%attr(0640,root,zabbix) %config(noreplace) %{_sysconfdir}/zabbix/zabbix_server.conf
%dir /usr/lib/zabbix/alertscripts
%dir /usr/lib/zabbix/externalscripts
%config(noreplace) %{_sysconfdir}/logrotate.d/zabbix-server
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/log/zabbix
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/run/zabbix
%{_mandir}/man8/zabbix_server.8*
%if 0%{?rhel} >= 7
%{_unitdir}/zabbix-server.service
%{_prefix}/lib/tmpfiles.d/zabbix-server.conf
%else
%{_sysconfdir}/init.d/zabbix-server
%endif
%{_sbindir}/zabbix_server_mysql

%files server-pgsql
%defattr(-,root,root,-)
%doc AUTHORS ChangeLog COPYING NEWS README
%doc database/postgresql/create.sql.gz
%attr(0640,root,zabbix) %config(noreplace) %{_sysconfdir}/zabbix/zabbix_server.conf
%dir /usr/lib/zabbix/alertscripts
%dir /usr/lib/zabbix/externalscripts
%config(noreplace) %{_sysconfdir}/logrotate.d/zabbix-server
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/log/zabbix
%attr(0755,zabbix,zabbix) %dir %{_localstatedir}/run/zabbix
%{_mandir}/man8/zabbix_server.8*
%if 0%{?rhel} >= 7
%{_unitdir}/zabbix-server.service
%{_prefix}/lib/tmpfiles.d/zabbix-server.conf
%else
%{_sysconfdir}/init.d/zabbix-server
%endif
%{_sbindir}/zabbix_server_pgsql

%files web
%defattr(-,root,root,-)
%doc AUTHORS ChangeLog COPYING NEWS README
%dir %attr(0750,apache,apache) %{_sysconfdir}/zabbix/web
%ghost %attr(0644,apache,apache) %config(noreplace) %{_sysconfdir}/zabbix/web/zabbix.conf.php
%config(noreplace) %{_sysconfdir}/zabbix/web/maintenance.inc.php
%if 0%{?rhel} >= 7
%config(noreplace) %{_sysconfdir}/httpd/conf.d/zabbix.conf
%else
%doc conf/httpd22-example.conf conf/httpd24-example.conf
%endif
%{_datadir}/zabbix

%files web-mysql
%defattr(-,root,root,-)

%files web-pgsql
%defattr(-,root,root,-)

%files web-japanese
%defattr(-,root,root,-)
%endif


%changelog
* Mon Oct 07 2019 Zabbix Packager <admin@programs74.ru> - 3.4.16-1
- update to 3.4.16
- removed jabber notifications support and dependency on iksemel library
- using google-noto-sans-cjk-ttc-fonts for graphfont in web-japanese package on rhel-8

* Mon Nov 12 2018 Zabbix Packager <info@zabbix.com> - 3.4.15-1
- update to 3.4.15

* Fri Sep 14 2018 Zabbix Packager <info@zabbix.com> - 3.4.14-1
- update to 3.4.14

* Mon Aug 27 2018 Zabbix Packager <info@zabbix.com> - 3.4.13-1
- update to 3.4.13

* Mon Jul 30 2018 Zabbix Packager <info@zabbix.com> - 3.4.12-1
- update to 3.4.12

* Mon Jun 25 2018 Zabbix Packager <info@zabbix.com> - 3.4.11-1
- update to 3.4.11

* Mon Jun 04 2018 Zabbix Packager <info@zabbix.com> - 3.4.10-1
- update to 3.4.10

* Wed May 09 2018 Zabbix Packager <info@zabbix.com> - 3.4.9-1
- update to 3.4.9

* Tue Apr 03 2018 Vladimir Levijev <vladimir.levijev@zabbix.com> - 3.4.8-1
- update to 3.4.8

* Mon Feb 19 2018 Vladimir Levijev <vladimir.levijev@zabbix.com> - 3.4.7-1
- update to 3.4.7

* Mon Jan 15 2018 Vladimir Levijev <vladimir.levijev@zabbix.com> - 3.4.6-1
- update to 3.4.6

* Wed Dec 27 2017 Vladimir Levijev <vladimir.levijev@zabbix.com> - 3.4.5-1
- update to 3.4.5

* Thu Nov 09 2017 Vladimir Levijev <vladimir.levijev@zabbix.com> - 3.4.4-2
- add missing translation (.mo) files

* Tue Nov 07 2017 Vladimir Levijev <vladimir.levijev@zabbix.com> - 3.4.4-1
- update to 3.4.4
- fix issue with new line character in pid file that resulted in failure when shutting down daemons on RHEL 5

* Tue Oct 17 2017 Vladimir Levijev <vladimir.levijev@zabbix.com> - 3.4.3-1
- update to 3.4.3

* Mon Sep 25 2017 Vladimir Levijev <vladimir.levijev@zabbix.com> - 3.4.2-1
- update to 3.4.2

* Mon Aug 28 2017 Vladimir Levijev <vladimir.levijev@zabbix.com> - 3.4.1-1
- update to 3.4.1
- change SocketDir to /var/run/zabbix

* Mon Aug 21 2017 Vladimir Levijev <vladimir.levijev@zabbix.com> - 3.4.0-1
- update to 3.4.0

* Wed Apr 26 2017 Kodai Terashima <kodai.terashima@zabbix.com> - 3.4.0-1alpha1
- update to 3.4.0alpla1 r68116
- add libpcre and libevent for compile option

* Sun Apr 23 2017 Kodai Terashima <kodai.terashima@zabbix.com> - 3.2.5-1
- update to 3.2.5
- add TimeoutSec=0 to systemd service file

* Thu Mar 02 2017 Kodai Terashima <kodai.terashima@zabbix.com> - 3.2.4-2
- remove TimeoutSec for systemd

* Mon Feb 27 2017 Kodai Terashima <kodai.terashima@zabbix.com> - 3.2.4-1
- update to 3.2.4
- add TimeoutSec for systemd service file

* Wed Dec 21 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.2.3-1
- update to 3.2.3

* Thu Dec 08 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.2.2-1
- update to 3.2.2

* Sun Oct 02 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.2.1-1
- update to 3.2.1
- use zabbix user and group for Java Gateway
- add SuccessExitStatus=143 for Java Gateway servie file

* Tue Sep 13 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.2.0-1
- update to 3.2.0
- add *.conf for Include parameter in agent configuration file

* Mon Sep 12 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.2.0rc2-1
- update to 3.2.0rc2

* Fri Sep 09 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.2.0rc1-1
- update to 3.2.0rc1

* Thu Sep 01 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.2.0beta2-1
- update to 3.2.0beta2

* Fri Aug 26 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.2.0beta1-1
- update to 3.2.0beta1

* Fri Aug 12 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.2.0alpha1-1
- update to 3.2.0alpha1

* Sun Jul 24 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.4-1
- update to 3.0.4

* Sun May 22 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.3-1
- update to 3.0.3
- fix java gateway systemd script to use java options

* Wed Apr 20 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.2-1
- update to 3.0.2
- remove ZBX-10459.patch

* Sat Apr 02 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.1-2
- fix proxy packges doesn't have schema.sql.gz
- add server and web packages for RHEL6
- add ZBX-10459.patch

* Sun Feb 28 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.1-1
- update to 3.0.1
- remove DBSocker parameter

* Sat Feb 20 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0-2
- agent, proxy and java-gateway for RHEL 5 and 6

* Mon Feb 15 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0-1
- update to 3.0.0

* Thu Feb 11 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0rc2
- update to 3.0.0rc2
- add TIMEOUT parameter for java gateway conf

* Thu Feb 04 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0rc1
- update to 3.0.0rc1

* Sat Jan 30 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0beta2
- update to 3.0.0beta2

* Thu Jan 21 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0beta1
- update to 3.0.0beta1

* Thu Jan 14 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0alpha6
- update to 3.0.0alpla6
- remove zabbix_agent conf and binary

* Wed Jan 13 2016 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0alpha5
- update to 3.0.0alpha5

* Fri Nov 13 2015 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0alpha4-1
- update to 3.0.0alpha4

* Thu Oct 29 2015 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0alpha3-2
- fix web-pgsql package dependency
- add --with-openssl option

* Mon Oct 19 2015 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0alpha3-1
- update to 3.0.0alpha3

* Tue Sep 29 2015 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0alpha2-3
- add IfModule for mod_php5 in apache configuration file
- fix missing proxy_mysql alternatives symlink
- chagne snmptrap log filename
- remove include dir from server and proxy conf

* Fri Sep 18 2015 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0alpha2-2
- fix create.sql doesn't contain schema.sql & images.sql

* Tue Sep 15 2015 Kodai Terashima <kodai.terashima@zabbix.com> - 3.0.0alpha2-1
- update to 3.0.0alpha2

* Sat Aug 22 2015 Kodai Terashima <kodai.terashima@zabbix.com> - 2.5.0-1
- create spec file from scratch
- update to 2.5.0
