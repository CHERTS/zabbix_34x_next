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


require_once dirname(__FILE__).'/include/config.inc.php';
require_once dirname(__FILE__).'/include/users.inc.php';

$page['title'] = _('Condition');
$page['file'] = 'popup_trexpr.php';

define('ZBX_PAGE_NO_MENU', 1);

require_once dirname(__FILE__).'/include/page_header.php';

$metrics = [
	PARAM_TYPE_TIME => _('Time'),
	PARAM_TYPE_COUNTS => _('Count')
];
/*
 * C - caption
 * T - type
 * M - metrics
 */
$param1SecCount = [
	'last' => [
		'C' => _('Last of').' (T)',
		'T' => T_ZBX_INT,
		'M' => $metrics
	],
	'shift' => [
		'C' => _('Time shift'),
		'T' => T_ZBX_INT
	]
];
$param1Sec = [
	'last' => [
		'C' => _('Last of').' (T)',
		'T' => T_ZBX_INT
	]
];
$param1Str = [
	'pattern' => [
		'C' => 'T',
		'T' => T_ZBX_STR
	]
];
$param2SecCount = [
	'pattern' => [
		'C' => 'V',
		'T' => T_ZBX_STR
	],
	'last' => [
		'C' => _('Last of').' (T)',
		'T' => T_ZBX_INT,
		'M' => $metrics
	]
];
$param3SecVal = [
	'last' => [
		'C' => _('Last of').' (T)',
		'T' => T_ZBX_INT,
		'M' => $metrics
	],
	'v' => [
		'C' => 'V',
		'T' => T_ZBX_STR
	],
	'o' => [
		'C' => 'O',
		'T' => T_ZBX_STR
	],
	'shift' => [
		'C' => _('Time shift'),
		'T' => T_ZBX_INT
	]
];
$param3SecPercent = [
	'last' => [
		'C' => _('Last of').' (T)',
		'T' => T_ZBX_INT,
		'M' => $metrics
	],
	'shift' => [
		'C' => _('Time shift'),
		'T' => T_ZBX_INT
	],
	'p' => [
		'C' => _('Percentage').' (P)',
		'T' => T_ZBX_DBL
	]
];
$paramSecIntCount = [
	'last' => [
		'C' => _('Last of').' (T)',
		'T' => T_ZBX_INT,
		'M' => $metrics
	],
	'mask' => [
		'C' => _('Mask'),
		'T' => T_ZBX_STR
	],
	'shift' => [
		'C' => _('Time shift'),
		'T' => T_ZBX_INT
	]
];
$paramForecast = [
	'last' => [
		'C' => _('Last of').' (T)',
		'T' => T_ZBX_INT,
		'M' => $metrics
	],
	'shift' => [
		'C' => _('Time shift'),
		'T' => T_ZBX_INT
	],
	'time' => [
		'C' => _('Time').' (t)',
		'T' => T_ZBX_INT
	],
	'fit' => [
		'C' => _('Fit'),
		'T' => T_ZBX_STR
	],
	'mode' => [
		'C' => _('Mode'),
		'T' => T_ZBX_STR
	]
];
$paramTimeleft = [
	'last' => [
		'C' => _('Last of').' (T)',
		'T' => T_ZBX_INT,
		'M' => $metrics
	],
	'shift' => [
		'C' => _('Time shift'),
		'T' => T_ZBX_INT
	],
	't' => [
		'C' => _('Threshold'),
		'T' => T_ZBX_DBL
	],
	'fit' => [
		'C' => _('Fit'),
		'T' => T_ZBX_STR
	]
];
$allowedTypesAny = [
	ITEM_VALUE_TYPE_FLOAT => 1,
	ITEM_VALUE_TYPE_STR => 1,
	ITEM_VALUE_TYPE_LOG => 1,
	ITEM_VALUE_TYPE_UINT64 => 1,
	ITEM_VALUE_TYPE_TEXT => 1
];
$allowedTypesNumeric = [
	ITEM_VALUE_TYPE_FLOAT => 1,
	ITEM_VALUE_TYPE_UINT64 => 1
];
$allowedTypesStr = [
	ITEM_VALUE_TYPE_STR => 1,
	ITEM_VALUE_TYPE_LOG => 1,
	ITEM_VALUE_TYPE_TEXT => 1
];
$allowedTypesLog = [
	ITEM_VALUE_TYPE_LOG => 1
];
$allowedTypesInt = [
	ITEM_VALUE_TYPE_UINT64 => 1
];

$functions = [
	'abschange' => [
		'description' => _('abschange() - Absolute difference between last and previous value'),
		'allowed_types' => $allowedTypesAny,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'avg' => [
		'description' => _('avg() - Average value of a period T'),
		'params' => $param1SecCount,
		'allowed_types' => $allowedTypesNumeric,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'delta' => [
		'description' => _('delta() - Difference between MAX and MIN value of a period T'),
		'params' => $param1SecCount,
		'allowed_types' => $allowedTypesNumeric,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'change' => [
		'description' => _('change() - Difference between last and previous value'),
		'allowed_types' => $allowedTypesAny,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'count' => [
		'description' => _('count() - Number of successfully retrieved values V (which fulfill operator O) for period T'),
		'params' => $param3SecVal,
		'allowed_types' => $allowedTypesAny,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'diff' => [
		'description' => _('diff() - Difference between last and preceding values (1 - true, 0 - false)'),
		'allowed_types' => $allowedTypesAny,
		'operators' => ['=', '<>']
	],
	'last' => [
		'description' => _('last() - Last (most recent) T value'),
		'params' => $param1SecCount,
		'allowed_types' => $allowedTypesAny,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'max' => [
		'description' => _('max() - Maximum value for period T'),
		'params' => $param1SecCount,
		'allowed_types' => $allowedTypesNumeric,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'min' => [
		'description' => _('min() - Minimum value for period T'),
		'params' => $param1SecCount,
		'allowed_types' => $allowedTypesNumeric,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'percentile' => [
		'description' => _('percentile() - Percentile P of a period T'),
		'params' => $param3SecPercent,
		'allowed_types' => $allowedTypesNumeric,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'prev' => [
		'description' => _('prev() - Previous value'),
		'allowed_types' => $allowedTypesAny,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'str' => [
		'description' => _('str() - Find string V in last (most recent) value (1 - found, 0 - not found)'),
		'params' => $param2SecCount,
		'allowed_types' => $allowedTypesStr,
		'operators' => ['=', '<>']
	],
	'strlen' => [
		'description' => _('strlen() - Length of last (most recent) T value in characters'),
		'params' => $param1SecCount,
		'allowed_types' => $allowedTypesStr,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'sum' => [
		'description' => _('sum() - Sum of values of a period T'),
		'params' => $param1SecCount,
		'allowed_types' => $allowedTypesNumeric,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'date' => [
		'description' => _('date() - Current date'),
		'allowed_types' => $allowedTypesAny,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'dayofweek' => [
		'description' => _('dayofweek() - Day of week'),
		'allowed_types' => $allowedTypesAny,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'dayofmonth' => [
		'description' => _('dayofmonth() - Day of month'),
		'allowed_types' => $allowedTypesAny,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'fuzzytime' => [
		'description' => _('fuzzytime() - Difference between item timestamp value and Zabbix server timestamp is over T seconds (1 - true, 0 - false)'),
		'params' => $param1Sec,
		'allowed_types' => $allowedTypesNumeric,
		'operators' => ['=', '<>']
	],
	'regexp' => [
		'description' => _('regexp() - Regular expression V matching last value in period T (1 - match, 0 - no match)'),
		'params' => $param2SecCount,
		'allowed_types' => $allowedTypesStr,
		'operators' => ['=', '<>']
	],
	'iregexp' => [
		'description' => _('iregexp() - Regular expression V matching last value in period T (non case-sensitive; 1 - match, 0 - no match)'),
		'params' => $param2SecCount,
		'allowed_types' => $allowedTypesStr,
		'operators' => ['=', '<>']
	],
	'logeventid' => [
		'description' => _('logeventid() - Event ID of last log entry matching regular expression T (1 - match, 0 - no match)'),
		'params' => $param1Str,
		'allowed_types' => $allowedTypesLog,
		'operators' => ['=', '<>']
	],
	'logseverity' => [
		'description' => _('logseverity() - Log severity of the last log entry'),
		'allowed_types' => $allowedTypesLog,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'logsource' => [
		'description' => _('logsource() - Log source of the last log entry matching parameter T (1 - match, 0 - no match)'),
		'params' => $param1Str,
		'allowed_types' => $allowedTypesLog,
		'operators' => ['=', '<>']
	],
	'now' => [
		'description' => _('now() - Number of seconds since the Epoch'),
		'allowed_types' => $allowedTypesAny,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'time' => [
		'description' => _('time() - Current time'),
		'allowed_types' => $allowedTypesAny,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'nodata' => [
		'description' => _('nodata() - No data received during period of time T (1 - true, 0 - false)'),
		'params' => $param1Sec,
		'allowed_types' => $allowedTypesAny,
		'operators' => ['=', '<>']
	],
	'band' => [
		'description' => _('band() - Bitwise AND of last (most recent) T value and mask'),
		'params' => $paramSecIntCount,
		'allowed_types' => $allowedTypesInt,
		'operators' => ['=', '<>']
	],
	'forecast' => [
		'description' => _('forecast() - Forecast for next t seconds based on period T'),
		'params' => $paramForecast,
		'allowed_types' => $allowedTypesNumeric,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	],
	'timeleft' => [
		'description' => _('timeleft() - Time to reach threshold estimated based on period T'),
		'params' => $paramTimeleft,
		'allowed_types' => $allowedTypesNumeric,
		'operators' => ['=', '<>', '>', '<', '>=', '<=']
	]
];
order_result($functions, 'description');

$operators = [];
foreach ($functions as $function) {
	foreach ($function['operators'] as $operator) {
		$operators[$operator] = true;
	}
}

//	VAR		TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
$fields = [
	'dstfrm' =>				[T_ZBX_STR, O_MAND, P_SYS, NOT_EMPTY,	null],
	'dstfld1' =>			[T_ZBX_STR, O_MAND, P_SYS, NOT_EMPTY,	null],
	'expression' =>			[T_ZBX_STR, O_OPT, null,	null,		null],
	'itemid' =>				[T_ZBX_INT, O_OPT, null,	null,		'isset({insert})'],
	'parent_discoveryid' =>	[T_ZBX_INT, O_OPT, null,	null,		null],
	'function' =>			[T_ZBX_STR, O_OPT, null,	IN('"'.implode('","', array_keys($functions)).'"'),
								'isset({insert})'
							],
	'operator' =>			[T_ZBX_STR, O_OPT, null,	IN('"'.implode('","', array_keys($operators)).'"'),
								'isset({insert})'
							],
	'params' =>				[T_ZBX_STR, O_OPT, null,	0,			null],
	'paramtype' =>			[T_ZBX_INT, O_OPT, null,	IN(PARAM_TYPE_TIME.','.PARAM_TYPE_COUNTS), 'isset({insert})'],
	'value' =>				[T_ZBX_STR, O_OPT, null,	NOT_EMPTY,	'isset({insert})'],
	// action
	'insert' =>				[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'cancel' =>				[T_ZBX_STR, O_OPT, P_SYS,	null,		null],
	'hostid' =>				[T_ZBX_INT, O_OPT, null,	null,		null],
	'groupid' =>			[T_ZBX_INT, O_OPT, null,	null,		null]
];

$is_valid = check_fields($fields);

$dstfrm = getRequest('dstfrm', 0);
$dstfld1 = getRequest('dstfld1', '');
$itemId = getRequest('itemid', 0);
$value = getRequest('value', 0);
$params = getRequest('params', []);
$paramType = getRequest('paramtype');
$function = getRequest('function', 'last');
$operator = getRequest('operator', '=');

// opening the popup when editing an expression in the trigger constructor
if ((getRequest('dstfld1') === 'expr_temp' || getRequest('dstfld1') === 'recovery_expr_temp')
		&& hasRequest('expression')) {
	$_REQUEST['expression'] = utf8RawUrlDecode($_REQUEST['expression']);

	$expressionData = new CTriggerExpression();
	$result = $expressionData->parse(getRequest('expression'));

	if ($result) {
		// only one item function macro is supported in an expression
		$functionMacroTokens = $result->getTokensByType(CTriggerExpressionParserResult::TOKEN_TYPE_FUNCTION_MACRO);
		if (count($functionMacroTokens) == 1) {
			$functionMacroToken = $functionMacroTokens[0];

			// function
			$function = $functionMacroToken['data']['functionName'];

			// determine param type
			$params = $functionMacroToken['data']['functionParams'];
			$paramNumber = in_array($function, ['regexp', 'iregexp', 'str']) ? 1 : 0;
			if (isset($params[$paramNumber][0]) && $params[$paramNumber][0] == '#'
					&& !in_array($function, ['fuzzytime', 'nodata'])) {
				$paramType = PARAM_TYPE_COUNTS;
				$params[$paramNumber] = substr($params[$paramNumber], 1);
			}
			else {
				$paramType = PARAM_TYPE_TIME;
			}

			// try to find an operator and a numeric value
			// the value and operator can be extracted only if the immediately follow the item function macro
			$tokens = $result->getTokens();
			foreach ($tokens as $key => $token) {
				if ($token['type'] == CTriggerExpressionParserResult::TOKEN_TYPE_FUNCTION_MACRO) {
					if (isset($tokens[$key + 2])
							&& $tokens[$key + 1]['type'] == CTriggerExpressionParserResult::TOKEN_TYPE_OPERATOR
							&& array_key_exists($function, $functions)
							&& in_array($tokens[$key + 1]['value'], $functions[$function]['operators'])
							&& $tokens[$key + 2]['type'] == CTriggerExpressionParserResult::TOKEN_TYPE_NUMBER) {

						$operator = $tokens[$key + 1]['value'];
						$value = $tokens[$key + 2]['value'];
					}
					else {
						break;
					}
				}
			}

			// find the item
			$item = API::Item()->get([
				'output' => ['itemid', 'hostid', 'name', 'key_', 'value_type'],
				'selectHosts' => ['name'],
				'webitems' => true,
				'filter' => [
					'host' => $functionMacroToken['data']['host'],
					'key_' => $functionMacroToken['data']['item'],
					'flags' => null
				]
			]);
			$item = reset($item);

			if ($item) {
				$itemId = $item['itemid'];
			}
			else {
				error(_('Unknown host item, no such item in selected host'));
			}
		}
	}
}
// opening an empty form or switching a function
else {
	$item = API::Item()->get([
		'output' => ['itemid', 'hostid', 'name', 'key_', 'value_type'],
		'selectHosts' => ['host', 'name'],
		'itemids' => $itemId,
		'webitems' => true,
		'filter' => ['flags' => null]
	]);
	$item = reset($item);
}

if ($itemId) {
	$items = CMacrosResolverHelper::resolveItemNames([$item]);
	$item = $items[0];

	$itemValueType = $item['value_type'];
	$itemKey = $item['key_'];
	$itemHostData = reset($item['hosts']);
	$description = $itemHostData['name'].NAME_DELIMITER.$item['name_expanded'];
}
else {
	$itemKey = '';
	$description = '';
	$itemValueType = null;
}

if (is_null($paramType) && isset($functions[$function]['params']['M'])) {
	$paramType = is_array($functions[$function]['params']['M'])
		? reset($functions[$function]['params']['M'])
		: $functions[$function]['params']['M'];
}
elseif (is_null($paramType)) {
	$paramType = PARAM_TYPE_TIME;
}

/*
 * Display
 */
$data = [
	'parent_discoveryid' => getRequest('parent_discoveryid'),
	'dstfrm' => $dstfrm,
	'dstfld1' => $dstfld1,
	'itemid' => $itemId,
	'value' => $value,
	'params' => $params,
	'paramtype' => $paramType,
	'description' => $description,
	'functions' => $functions,
	'function' => $function,
	'operator' => $operator,
	'item_key' => $itemKey,
	'itemValueType' => $itemValueType,
	'selectedFunction' => null,
	'insert' => getRequest('insert'),
	'cancel' => getRequest('cancel'),
	'groupid' => getRequest('groupid'),
	'hostid' => getRequest('hostid'),
	'is_valid' => $is_valid
];

// check if submitted function is usable with selected item
foreach ($data['functions'] as $id => $f) {
	if (($data['itemValueType'] === null || isset($f['allowed_types'][$data['itemValueType']])) && $id === $function) {
		$data['selectedFunction'] = $id;
		break;
	}
}

if ($data['selectedFunction'] === null) {
	$data['selectedFunction'] = 'last';
	$data['function'] = 'last';
}

// remove functions that not correspond to chosen item
foreach ($data['functions'] as $id => $f) {
	if ($data['itemValueType'] !== null && !isset($f['allowed_types'][$data['itemValueType']])) {
		unset($data['functions'][$id]);

		// Take first available function from list and change to first available operator for that function.
		if ($id === $data['function']) {
			$data['function'] = key($data['functions']);
			$data['operator'] = reset($data['functions'][$data['function']]['operators']);
		}
	}
}

// create and validate trigger expression
if (isset($data['insert'])) {
	try {
		if ($data['description']) {
			if ($data['paramtype'] == PARAM_TYPE_COUNTS
					&& array_key_exists('last', $data['params'])
					&& $data['params']['last'] !== '') {
				$data['params']['last'] = '#'.$data['params']['last'];
			}
			elseif ($data['paramtype'] == PARAM_TYPE_TIME && in_array($function, ['last', 'band', 'strlen'])) {
				$data['params']['last'] = '';
			}

			// quote function param
			$quotedParams = [];
			foreach ($data['params'] as $param) {
				$quotedParams[] = quoteFunctionParam($param);
			}

			$data['expression'] = sprintf('{%s:%s.%s(%s)}%s%s',
				$itemHostData['host'],
				$data['item_key'],
				$function,
				rtrim(implode(',', $quotedParams), ','),
				$operator,
				$data['value']
			);

			// validate trigger expression
			$triggerExpression = new CTriggerExpression();

			if ($triggerExpression->parse($data['expression'])) {
				$expressionData = reset($triggerExpression->expressions);

				// validate trigger function
				$triggerFunctionValidator = new CFunctionValidator();
				$isValid = $triggerFunctionValidator->validate([
					'function' => $expressionData['function'],
					'functionName' => $expressionData['functionName'],
					'functionParamList' => $expressionData['functionParamList'],
					'valueType' => $data['itemValueType']
				]);
				if (!$isValid) {
					unset($data['insert']);
					throw new Exception($triggerFunctionValidator->getError());
				}
			}
			else {
				unset($data['insert']);
				throw new Exception($triggerExpression->error);
			}

			// quote function param
			if (isset($data['insert'])) {
				foreach ($data['params'] as $pnum => $param) {
					$data['params'][$pnum] = quoteFunctionParam($param);
				}
			}
		}
		else {
			unset($data['insert']);
			throw new Exception(_('Item not selected'));
		}
	}
	catch (Exception $e) {
		error($e->getMessage());
		show_error_message(_('Cannot insert trigger expression'));
	}
}
elseif (hasErrorMesssages()) {
	show_messages();
}

// render view
$expressionView = new CView('configuration.triggers.expression', $data);
$expressionView->render();
$expressionView->show();

require_once dirname(__FILE__).'/include/page_footer.php';
