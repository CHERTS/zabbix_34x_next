<?php
/*
** Zabbix
** Copyright (C) 2001-2018 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

require_once dirname(__FILE__).'/../include/class.cwebtest.php';

class testPagePopup extends CWebTest {
	private $urlPopupProxies =
			'popup.php?srctbl=proxies&srcfld1=hostid&srcfld2=host&dstfrm=form&dstfld1=fld1&dstfld2=fld2';
	private $urlPopupApplications =
			'popup.php?srctbl=applications&srcfld1=name&dstfrm=form&dstfld1=fld1';

	public function testPagePopupProxies_CheckLayout() {
		$this->authenticate();
		$this->zbxTestOpen($this->urlPopupProxies);
		$this->zbxTestCheckTitle('Proxies');
		$this->zbxTestCheckHeader('Proxies');
		$this->zbxTestTextPresent(['Name']);

		$result = DBselect(
			'SELECT host'.
			' FROM hosts'.
			' WHERE status IN ('.HOST_STATUS_PROXY_ACTIVE.','.HOST_STATUS_PROXY_PASSIVE.')'
		);
		while ($row = DBfetch($result)) {
			$this->zbxTestTextPresent($row['host']);
		}
	}

	public function testPagePopupApplications_CheckLayout() {
		$this->authenticate();
		$this->zbxTestOpen($this->urlPopupApplications);
		$this->zbxTestCheckTitle('Applications');
		$this->zbxTestCheckHeader('Applications');
		$this->zbxTestTextPresent(['Group', 'Host']);
		$this->zbxTestTextPresent('Name');
		$this->zbxTestAssertElementPresentId('groupid');
		$this->zbxTestAssertElementPresentId('hostid');
		$this->zbxTestDropdownSelect('groupid', 'Templates');
		$this->zbxTestDropdownSelectWait('hostid', 'Template OS Linux');

		$ddGroups = $this->zbxTestGetDropDownElements('groupid');
		$dbGroups = [];

		// checking order of dropdown entries

		$result = DBselect(
			'SELECT g.groupid,g.name'.
			' FROM groups g'.
			' WHERE g.groupid IN ('.
				'SELECT hg.groupid'.
				' FROM hosts_groups hg,hosts h'.
				' WHERE hg.hostid=h.hostid'.
					' AND h.status IN ('.HOST_STATUS_MONITORED.','.HOST_STATUS_NOT_MONITORED.','.HOST_STATUS_TEMPLATE.')'.
			')'
		);

		while ($row = DBfetch($result)) {
			$dbGroups[] = $row;
		}

		order_result($dbGroups, 'name');
		$dbGroups = array_values($dbGroups);

		$countDdGroups = count($ddGroups);
		$countDbGroups = count($dbGroups);

		$this->assertEquals($countDdGroups, $countDbGroups);

		for ($i = 0; $i < $countDbGroups; $i++) {
			$this->assertEquals($dbGroups[$i]['groupid'], $ddGroups[$i]['id']);
			$this->assertEquals($dbGroups[$i]['name'], $ddGroups[$i]['content']);
		}

		// checking window content

		// TODO checkind windows content and hosts dropdown
	}
}
