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


require_once dirname(__FILE__).'/js/configuration.triggers.expression.js.php';

$expressionWidget = new CWidget();

// create form
$expressionForm = (new CForm())
	->setName('expression')
	->addVar('dstfrm', $this->data['dstfrm'])
	->addVar('dstfld1', $this->data['dstfld1'])
	->addVar('itemid', $this->data['itemid']);

if (!empty($this->data['parent_discoveryid'])) {
	$expressionForm->addVar('parent_discoveryid', $this->data['parent_discoveryid']);
}

// create form list
$expressionFormList = new CFormList();

// append item to form list
$item = [
	(new CTextBox('description', $this->data['description'], true))->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH),
	(new CDiv())->addClass(ZBX_STYLE_FORM_INPUT_MARGIN),
	(new CButton('select', _('Select')))
		->addClass(ZBX_STYLE_BTN_GREY)
		->onClick('return PopUp(\'popup.php?writeonly=1&dstfrm='.$expressionForm->getName().
			(($data['groupid'] && $data['hostid'])
				? '&groupid='.$data['groupid'].'&hostid='.$data['hostid']
				: ''
			).
			'&dstfld1=itemid&dstfld2=description&submitParent=1'.(!empty($this->data['parent_discoveryid']) ? '&normal_only=1' : '').
			'&srctbl=items&srcfld1=itemid&srcfld2=name\', 0, 0, \'zbx_popup_item\');')
];

if (!empty($this->data['parent_discoveryid'])) {
	$item[] = (new CDiv())->addClass(ZBX_STYLE_FORM_INPUT_MARGIN);
	$item[] = (new CButton('select', _('Select prototype')))
		->addClass(ZBX_STYLE_BTN_GREY)
		->onClick('return PopUp(\'popup.php?dstfrm='.$expressionForm->getName().
			'&dstfld1=itemid&dstfld2=description&submitParent=1'.url_param('parent_discoveryid', true).
			'&srctbl=item_prototypes&srcfld1=itemid&srcfld2=name\', 0, 0, \'zbx_popup_item\');');
}

$expressionFormList->addRow(_('Item'), $item);

$function_combobox = new CComboBox('function', $data['function']);

foreach ($data['functions'] as $id => $function) {
	$function_combobox->addItem($id, $function['description']);
}

$expressionFormList->addRow(_('Function'), $function_combobox);

if (array_key_exists('params', $data['functions'][$data['selectedFunction']])) {
	$paramid = 0;

	foreach ($data['functions'][$data['selectedFunction']]['params'] as $param_name => $param_function) {
		if (array_key_exists($param_name, $data['params'])) {
			$param_value = $data['params'][$param_name];
		}
		else {
			$param_value = array_key_exists($paramid, $data['params']) ? $data['params'][$paramid] : null;
		}

		if ($param_function['T'] == T_ZBX_INT) {
			$param_type_element = null;

			if (in_array($param_name, ['last'])) {
				if ($data['paramtype'] == PARAM_TYPE_COUNTS && $param_value !== null && $param_value !== ''
						&& $param_value[0] === '#' && $data['is_valid']) {
					$param_value = substr($param_value, 1);
				}
				if (array_key_exists('M', $param_function)) {
					if (in_array($data['selectedFunction'], ['last', 'band', 'strlen'])) {
						$param_type_element = $param_function['M'][PARAM_TYPE_COUNTS];
						$expressionFormList->addItem(new CVar('paramtype', PARAM_TYPE_COUNTS, 'spec_paramtype'));
					}
					else {
						$param_type_element = new CComboBox('paramtype', $data['paramtype'], null,
							$param_function['M']
						);
					}
				}
				else {
					$expressionForm->addItem((new CVar('paramtype', PARAM_TYPE_TIME))->removeId());
					$param_type_element = _('Time');
				}
			}
			elseif (in_array($param_name, ['shift'])) {
				$param_type_element = _('Time');
			}

			$param_field = (new CTextBox('params['.$param_name.']', $param_value))->setWidth(ZBX_TEXTAREA_SMALL_WIDTH);

			$expressionFormList->addRow($param_function['C'], [
				$param_field,
				(new CDiv())->addClass(ZBX_STYLE_FORM_INPUT_MARGIN),
				$param_type_element
			]);
		}
		else {
			$expressionFormList->addRow($param_function['C'],
				(new CTextBox('params['.$param_name.']', $param_value))->setWidth(ZBX_TEXTAREA_SMALL_WIDTH)
			);
			if ($paramid === 0) {
				$expressionForm->addItem((new CVar('paramtype', PARAM_TYPE_TIME))->removeId());
			}
		}

		$paramid++;
	}
}
else {
	$expressionForm->addVar('paramtype', PARAM_TYPE_TIME);
}

$expressionFormList->addRow(_('Result'), [
		new CComboBox('operator', $data['operator'], null,
			array_combine($data['functions'][$data['function']]['operators'],
				$data['functions'][$data['function']]['operators']
			)
		),
		' ',
		(new CTextBox('value', $data['value']))->setWidth(ZBX_TEXTAREA_SMALL_WIDTH)
	]
);

// append tabs to form
$expressionTab = (new CTabView())->addTab('expressionTab', _('Trigger expression condition'), $expressionFormList);

// append buttons to form
$expressionTab->setFooter(makeFormFooter(
	new CSubmit('insert', _('Insert')),
	[new CButtonCancel(url_params(['parent_discoveryid', 'dstfrm', 'dstfld1']))
]));

$expressionForm->addItem($expressionTab);
$expressionWidget->addItem($expressionForm);

return $expressionWidget;
