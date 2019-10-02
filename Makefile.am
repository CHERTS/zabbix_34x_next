## Process this file with automake to produce Makefile.in

ACLOCAL_AMFLAGS = -I m4

SUBDIRS = \
	src \
	database \
	man \
	misc \
	upgrades

EXTRA_DIST = \
	bin \
	build \
	frontends \
	include \
	conf \
	sass

## "dist-hook" run after the distribution directory is filled, but before the actual tar (or shar) file is created.
dist-hook:
# File revision number must be numeric (Git commit hash cannot be used).
# Therefore to make it numeric and meaningful it is artificially composed from:
#    - branch (development or release),
#    - type (alpha, beta, rc or release),
#    - number of alpha, beta or rc.
# 'branch' expression tries to find out is it a development branch or release branch.
#      Result is encoded as: 1 - dev branch, release branch or error occurred, 2 - tag.
# 'type_name' expression tries to find out what type of release it is.
#      Expected result is: "alpha", "beta", "rc" or "" (empty string).
# 'type_num' expression encodes 'type_name' as numeric value:
#      1 - alpha, 2 - beta, 3 - rc, 4 - release, 0 - unknown.
# 'type_count' expression tries to find out number of "alpha", "beta" or "rc" (e.g. 1 from "rc1").
	@branch=`(git symbolic-ref -q HEAD > /dev/null && echo 1) || (git tag -l --points-at HEAD| grep "."| grep -q -v "-" && echo 2) || echo 1`; \
	type_name=`cat $(top_distdir)/include/version.h| sed -n -e '/ZABBIX_VERSION_RC/s/.*"\([a-z]*\)[0-9]*"/\1/p'`; \
	type_num=`(test "x$$type_name" = "xalpha" && echo "1") || echo ""`; \
	type_num=`(test -z $$type_num && test "x$$type_name" = "xbeta" && echo "2") || echo "$$type_num"`; \
	type_num=`(test -z $$type_num && test "x$$type_name" = "xrc" && echo "3") || echo "$$type_num"`; \
	type_num=`(test -z $$type_num && test -z $$type_name && echo "4") || echo "$$type_num"`; \
	type_num=`(test -z $$type_num && echo "0") || echo "$$type_num"`; \
	type_count=`cat $(top_distdir)/include/version.h|sed -n -e '/ZABBIX_VERSION_RC/s/.*"[a-z]*\([0-9]*\)"/\1/p'`; \
	type_count=`printf '%02d' $$type_count`; \
	zabbix_revision=`git rev-parse --short HEAD`; \
	cat $(top_distdir)/include/version.h|sed "s/{ZABBIX_RC_NUM}/$$branch$$type_num$$type_count/g"| \
	sed "s/{ZABBIX_REVISION}/$$zabbix_revision/g" > $(top_distdir)/include/version.h.new; \
	mv $(top_distdir)/include/version.h.new $(top_distdir)/include/version.h; \
	cat $(top_distdir)/src/zabbix_java/src/com/zabbix/gateway/GeneralInformation.java|sed "s/{ZABBIX_REVISION}/$$zabbix_revision/g" > \
		$(top_distdir)/src/zabbix_java/src/com/zabbix/gateway/GeneralInformation.java.new; \
	mv $(top_distdir)/src/zabbix_java/src/com/zabbix/gateway/GeneralInformation.java.new \
		$(top_distdir)/src/zabbix_java/src/com/zabbix/gateway/GeneralInformation.java

	rm -rf `find $(top_distdir) -name ".gitignore" -o -name ".gitattributes"`
	rm -f $(top_distdir)/include/config.h
	rm -f $(top_distdir)/include/config.h.in~
	rm -f $(top_distdir)/frontends/php/conf/zabbix.conf.php
	rm -rf $(top_distdir)/frontends/php/tests
	rm -f $(top_distdir)/sass/*.html
	rm -rf $(top_distdir)/sass/img_source

if DBSCHEMA
dbschema_ibm_db2:
	cd database/ibm_db2 && \
	$(MAKE) $(AM_MAKEFLAGS) data.sql && \
	$(MAKE) $(AM_MAKEFLAGS) schema.sql

dbschema_mysql:
	cd database/mysql && \
	$(MAKE) $(AM_MAKEFLAGS) data.sql && \
	$(MAKE) $(AM_MAKEFLAGS) schema.sql

dbschema_oracle:
	cd database/oracle && \
	$(MAKE) $(AM_MAKEFLAGS) data.sql && \
	$(MAKE) $(AM_MAKEFLAGS) schema.sql

dbschema_postgresql:
	cd database/postgresql && \
	$(MAKE) $(AM_MAKEFLAGS) data.sql && \
	$(MAKE) $(AM_MAKEFLAGS) schema.sql

dbschema_sqlite3:
	cd database/sqlite3 && \
	$(MAKE) $(AM_MAKEFLAGS) data.sql && \
	$(MAKE) $(AM_MAKEFLAGS) schema.sql

dbschema_c:
	cd src/libs/zbxdbhigh && \
	$(MAKE) $(AM_MAKEFLAGS) dbschema.c

dbschema: dbschema_ibm_db2 dbschema_mysql dbschema_oracle dbschema_postgresql dbschema_sqlite3 dbschema_c
endif

gettext:
	frontends/php/locale/make_mo.sh

css_theme_blue:
	sass --no-cache --sourcemap=none sass/stylesheets/sass/screen.scss frontends/php/styles/blue-theme.css
	cp frontends/php/styles/blue-theme.css sass/stylesheets/screen.css

css_theme_dark:
	sass --no-cache --sourcemap=none sass/stylesheets/sass/dark-theme.scss frontends/php/styles/dark-theme.css
	cp frontends/php/styles/dark-theme.css sass/stylesheets/dark-theme.css

css_icons:
	cp sass/img/icon-sprite.svg frontends/php/img/
	cp sass/img/browser-sprite.png frontends/php/img/
	cp sass/apple-touch-icon-120x120-precomposed.png frontends/php/img/
	cp sass/apple-touch-icon-152x152-precomposed.png frontends/php/img/
	cp sass/apple-touch-icon-180x180-precomposed.png frontends/php/img/
	cp sass/apple-touch-icon-76x76-precomposed.png frontends/php/img/
	cp sass/ms-tile-144x144.png frontends/php/img/
	cp sass/touch-icon-192x192.png frontends/php/img/
	cp sass/favicon.ico frontends/php/

css: css_theme_blue css_theme_dark css_icons

## CUnit test support
TESTDIR = src

test:
	cd $(TESTDIR) && $(MAKE) $(AM_MAKEFLAGS) test

.PHONY: test
