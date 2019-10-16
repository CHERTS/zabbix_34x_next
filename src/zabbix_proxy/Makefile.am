## Process this file with automake to produce Makefile.in

SUBDIRS = \
	heart \
	housekeeper \
	proxyconfig \
	datasender \
	taskmanager

sbin_PROGRAMS = zabbix_proxy

zabbix_proxy_SOURCES = \
	servercomms.c \
	servercomms.h \
	events.c \
	preprocessing.c \
	proxy.c
	
zabbix_proxy_LDADD = \
	heart/libzbxheart.a \
	$(top_builddir)/src/zabbix_server/dbsyncer/libzbxdbsyncer.a \
	$(top_builddir)/src/zabbix_server/discoverer/libzbxdiscoverer.a \
	housekeeper/libzbxhousekeeper.a \
	$(top_builddir)/src/zabbix_server/httppoller/libzbxhttppoller.a \
	proxyconfig/libzbxproxyconfig.a \
	$(top_builddir)/src/zabbix_server/pinger/libzbxpinger.a \
	$(top_builddir)/src/zabbix_server/poller/libzbxpoller.a \
	$(top_builddir)/src/zabbix_server/trapper/libzbxtrapper.a \
	$(top_builddir)/src/zabbix_server/snmptrapper/libzbxsnmptrapper.a \
	datasender/libzbxdatasender.a \
	taskmanager/libzbxtaskmanager.a \
	$(top_builddir)/src/zabbix_server/selfmon/libzbxselfmon.a \
	$(top_builddir)/src/zabbix_server/vmware/libzbxvmware.a \
	$(top_builddir)/src/zabbix_server/scripts/libzbxscripts.a \
	$(top_builddir)/src/libs/zbxsysinfo/libzbxproxysysinfo.a \
	$(top_builddir)/src/libs/zbxsysinfo/common/libcommonsysinfo.a \
	$(top_builddir)/src/libs/zbxsysinfo/simple/libsimplesysinfo.a \
	$(top_builddir)/src/libs/zbxsysinfo/$(ARCH)/libspechostnamesysinfo.a \
	$(top_builddir)/src/libs/zbxlog/libzbxlog.a \
	$(top_builddir)/src/libs/zbxserver/libzbxserver.a \
	$(top_builddir)/src/libs/zbxdbcache/libzbxdbcache.a \
	$(top_builddir)/src/libs/zbxhistory/libzbxhistory.a \
	$(top_builddir)/src/libs/zbxmemory/libzbxmemory.a \
	$(top_builddir)/src/libs/zbxregexp/libzbxregexp.a \
	$(top_builddir)/src/libs/zbxself/libzbxself.a \
	$(top_builddir)/src/libs/zbxnix/libzbxnix.a \
	$(top_builddir)/src/libs/zbxsys/libzbxsys.a \
	$(top_builddir)/src/libs/zbxconf/libzbxconf.a \
	$(top_builddir)/src/libs/zbxcommon/libzbxcommon.a \
	$(top_builddir)/src/libs/zbxcrypto/libzbxcrypto.a \
	$(top_builddir)/src/libs/zbxcomms/libzbxcomms.a \
	$(top_builddir)/src/libs/zbxcommshigh/libzbxcommshigh.a \
	$(top_builddir)/src/libs/zbxjson/libzbxjson.a \
	$(top_builddir)/src/libs/zbxhttp/libzbxhttp.a \
	$(top_builddir)/src/libs/zbxexec/libzbxexec.a \
	$(top_builddir)/src/libs/zbxicmpping/libzbxicmpping.a \
	$(top_builddir)/src/libs/zbxdbupgrade/libzbxdbupgrade.a \
	$(top_builddir)/src/libs/zbxdbhigh/libzbxdbhigh.a \
	$(top_builddir)/src/libs/zbxdb/libzbxdb.a \
	$(top_builddir)/src/libs/zbxmodules/libzbxmodules.a \
	$(top_builddir)/src/libs/zbxtasks/libzbxtasks.a 

if PROXY_IPCSERVICE
zabbix_proxy_LDADD += $(top_builddir)/src/libs/zbxipcservice/libzbxipcservice.a
endif

zabbix_proxy_LDADD += $(top_builddir)/src/libs/zbxalgo/libzbxalgo.a

if HAVE_IPMI
zabbix_proxy_LDADD += $(top_builddir)/src/zabbix_server/ipmi/libipmi.a
endif
	
zabbix_proxy_LDADD += $(PROXY_LIBS)

zabbix_proxy_LDFLAGS = $(PROXY_LDFLAGS)

zabbix_proxy_CFLAGS = \
	-DZABBIX_DAEMON \
	-DSYSCONFDIR="\"$(sysconfdir)\"" \
	-DDATADIR="\"$(datadir)\"" \
	-DLIBDIR="\"$(libdir)\""

PROXY_CONFIG = zabbix_proxy.conf
INCLUDE_DIR = $(DESTDIR)$(sysconfdir)/$(PROXY_CONFIG).d
EXSCRIPTS_DIR = $(DESTDIR)$(datadir)/zabbix/externalscripts
MODULES_DIR = $(DESTDIR)$(libdir)

install-data-hook:
	$(MKDIR_P) "$(INCLUDE_DIR)"
	$(MKDIR_P) "$(EXSCRIPTS_DIR)"
	$(MKDIR_P) "$(MODULES_DIR)"
	test -f "$(DESTDIR)$(sysconfdir)/$(PROXY_CONFIG)" || cp "../../conf/$(PROXY_CONFIG)" "$(DESTDIR)$(sysconfdir)"
