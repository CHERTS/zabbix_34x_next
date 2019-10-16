## Process this file with automake to produce Makefile.in

EXTRA_DIST = \
	eventlog.c \
	eventlog.h \
	perfstat.c \
	perfstat.h

sbin_PROGRAMS = zabbix_agentd

zabbix_agentd_SOURCES = \
	active.c \
	active.h \
	stats.c \
	stats.h \
	cpustat.c \
	cpustat.h \
	diskdevices.c \
	diskdevices.h \
	vmstats.c \
	vmstats.h \
	logfiles.c \
	logfiles.h \
	zbxconf.c \
	zbxconf.h \
	listener.c \
	listener.h \
	procstat.c \
	procstat.h \
	zabbix_agentd.c

zabbix_agentd_LDADD = \
	$(top_builddir)/src/libs/zbxsysinfo/libzbxagentsysinfo.a \
	$(top_builddir)/src/libs/zbxsysinfo/$(ARCH)/libspecsysinfo.a \
	$(top_builddir)/src/libs/zbxsysinfo/$(ARCH)/libspechostnamesysinfo.a \
	$(top_builddir)/src/libs/zbxsysinfo/agent/libagentsysinfo.a \
	$(top_builddir)/src/libs/zbxsysinfo/common/libcommonsysinfo.a \
	$(top_builddir)/src/libs/zbxsysinfo/simple/libsimplesysinfo.a \
	$(top_builddir)/src/libs/zbxlog/libzbxlog.a \
	$(top_builddir)/src/libs/zbxregexp/libzbxregexp.a \
	$(top_builddir)/src/libs/zbxalgo/libzbxalgo.a \
	$(top_builddir)/src/libs/zbxsys/libzbxsys.a \
	$(top_builddir)/src/libs/zbxnix/libzbxnix.a \
	$(top_builddir)/src/libs/zbxcomms/libzbxcomms.a \
	$(top_builddir)/src/libs/zbxconf/libzbxconf.a \
	$(top_builddir)/src/libs/zbxcommon/libzbxcommon.a \
	$(top_builddir)/src/libs/zbxcrypto/libzbxcrypto.a \
	$(top_builddir)/src/libs/zbxjson/libzbxjson.a \
	$(top_builddir)/src/libs/zbxexec/libzbxexec.a \
	$(top_builddir)/src/libs/zbxmodules/libzbxmodules.a \
	$(top_builddir)/src/libs/zbxself/libzbxself.a \
	$(AGENT_LIBS)

zabbix_agentd_LDFLAGS = $(AGENT_LDFLAGS)

zabbix_agentd_CFLAGS = \
	-DZABBIX_DAEMON \
	-DSYSCONFDIR="\"$(sysconfdir)\"" \
	-DLIBDIR="\"$(libdir)\""

AGENTD_CONFIG = zabbix_agentd.conf
INCLUDE_DIR = $(DESTDIR)$(sysconfdir)/$(AGENTD_CONFIG).d
MODULES_DIR = $(DESTDIR)$(libdir)

install-data-hook:
	$(MKDIR_P) "$(INCLUDE_DIR)"
	$(MKDIR_P) "$(MODULES_DIR)"
	test -f "$(DESTDIR)$(sysconfdir)/$(AGENTD_CONFIG)" || cp "../../conf/$(AGENTD_CONFIG)" "$(DESTDIR)$(sysconfdir)"
