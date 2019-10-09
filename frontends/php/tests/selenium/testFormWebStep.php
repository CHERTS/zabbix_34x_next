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
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

require_once dirname(__FILE__).'/../include/class.cwebtest.php';

/**
 * @backup httptest
 */
class testFormWebStep extends CWebTest {

	public static function steps() {
		return [
			// Empty step name
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Empty step name',
					'url' => 'http://www.zabbix.com',
					'error_msg' => 'Page received incorrect data',
					'errors' => [
						'Incorrect value for field "Name": cannot be empty.'
					]
				]
			],
			// Step name - max length 64
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Max step length',
					'step_name' => 'In mollis eros vel tempor bibendum. Maecenas nec fringilla felis',
					'url' => 'http://www.zabbix.com'
				]
			],
			// Empty step url
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Empty step url',
					'step_name' => 'Step with empty step url',
					'error_msg' => 'Page received incorrect data',
					'errors' => [
						'Incorrect value for field "URL": cannot be empty.'
					]
				]
			],
			// Query - empty name
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Empty query name',
					'step_name' => 'Step with empty query name',
					'url' => 'http://www.zabbix.com',
					'error_webform' => true,
					'query' => [
						['value' => 'test']
					],
					'error_msg' => 'Cannot add web scenario',
					'errors' => [
						'Invalid parameter "/1/steps/1/query_fields/1/name": cannot be empty.'
					]
				]
			],
			// Parse url
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Parse url',
					'step_name' => 'Step parse url',
					'url' => 'https://intranet.zabbix.com/secure/admin.jspa?login=admin&password=s00p3r%24ecr3%26',
					'parse' => true,
					'parse_query' => [
						['name' => 'login', 'value' => 'admin'],
						['name' => 'password', 'value' => 's00p3r$ecr3&'],
					],
					'check_url' => 'https://intranet.zabbix.com/secure/admin.jspa'
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Existing query fields merge',
					'step_name' => 'Step existing query fields merge',
					'url' => 'https://intranet.zabbix.com/secure/admin.jspa?password=s00p3r%24ecr3%26',
					'query' => [
						['name' => 'login', 'value' => 'admin'],
					],
					'parse' => true,
					'parse_query' => [
						['name' => 'login', 'value' => 'admin'],
						['name' => 'password', 'value' => 's00p3r$ecr3&'],
					],
					'check_url' => 'https://intranet.zabbix.com/secure/admin.jspa'
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'After parse query fields remain unchanged',
					'step_name' => 'Step after parse query fields remain unchanged',
					'url' => 'https://intranet.zabbix.com/secure/admin.jspa?login=admin&password=s00p3r%24ecr3%26',
					'query' => [
						['name' => 'login', 'value' => 'admin'],
						['name' => 'password', 'value' => 's00p3r$ecr3&']
					],
					'parse' => true,
					'parse_query' => [
						['name' => 'login', 'value' => 'admin'],
						['name' => 'password', 'value' => 's00p3r$ecr3&'],
					],
					'check_url' => 'https://intranet.zabbix.com/secure/admin.jspa'
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Query field duplicates replaced',
					'step_name' => 'Step query field duplicates replaced',
					'url' => 'https://intranet.zabbix.com/secure/admin.jspa?login=user&password=a123%24bcd4%26',
					'query' => [
						['name' => 'login', 'value' => 'admin'],
						['name' => 'password', 'value' => 'password']
					],
					'parse' => true,
					'parse_query' => [
						['name' => 'login', 'value' => 'user'],
						['name' => 'password', 'value' => 'a123$bcd4&'],
					],
					'check_url' => 'https://intranet.zabbix.com/secure/admin.jspa'
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'URL fragment part ignored',
					'step_name' => 'Step URL fragment part ignored',
					'url' => 'http://www.zabbix.com/enterprise_ready#test',
					'parse' => true,
					'parse_query' => [
						['name' => '', 'value' => '']
					],
					'check_url' => 'http://www.zabbix.com/enterprise_ready'
				]
			],
			[
				[
					'expected' => TEST_ERROR,
					'name' => 'URL parse validation',
					'step_name' => 'Step URL parse validation',
					'url' => 'http://localhost/zabbix/index.php?test=%11',
					'parse' => true,
					'errors' => 'Failed to parse URL. URL is not properly encoded.'
				]
			],
			// Post - empty name
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Empty post name',
					'step_name' => 'Step with empty post name',
					'url' => 'http://www.zabbix.com',
					'error_webform' => true,
					'post' => [
						['value' => 'test']
					],
					'error_msg' => 'Cannot add web scenario',
					'errors' => [
						'Invalid parameter "/1/steps/1/posts/1/name": cannot be empty.'
					]
				]
			],
			// Post name - max length 255
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Post name - max length',
					'step_name' => 'Step post name - max length',
					'url' => 'http://www.zabbix.com',
					'post' => [
						['name' => 'qwertyuiopqwertyuiopqwertyuiopqwertyui'.
							'opqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwe.'.
							'rtyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqw'.
							'ertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwer'.
							'tyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiop12345678901234',
							'value' => 'qwertyuiopqwertyuiopqwertyuiopqwertyui'.
							'opqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwe.'.
							'rtyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqw'.
							'ertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwer'.
							'tyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiop12345678901234']
					]
				]
			],
			// Post - from form data to raw data
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'From form data to raw data',
					'step_name' => 'Step from form data to raw data',
					'url' => 'http://www.zabbix.com',
					'post' => [
						['name' => 'zab bix', 'value' => 'tes&t']
					],
					'raw' => true,
					'check_raw' => 'zab%20bix=tes%26t',
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'From form data to raw data cyrillic',
					'step_name' => 'Step from form data to raw data, cyrillic',
					'url' => 'http://www.zabbix.com',
					'post' => [
						['name' => 'тест', 'value' => 'тест']
					],
					'raw' => true,
					'check_raw' => '%D1%82%D0%B5%D1%81%D1%82=%D1%82%D0%B5%D1%81%D1%82'
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'From form data to raw data symbols',
					'step_name' => 'Step from form data to raw data, symbols',
					'url' => 'http://www.zabbix.com',
					'post' => [
						['name' => '!@#$%^&*()', 'value' => '!@#$%^&*()']
					],
					'raw' => true,
					'check_raw' => '!%40%23%24%25%5E%26*()=!%40%23%24%25%5E%26*()'
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'From form to raw with two post data',
					'step_name' => 'Step from form to raw with two post data',
					'url' => 'http://www.zabbix.com',
					'post' => [
						['name' => 'zabbix', 'value' => 'test'],
						['name' => '&Günter']
					],
					'raw' => true,
					'check_raw' => 'zabbix=test&%26G%C3%BCnter'
				]
			],
			// Post - from raw data to form data
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'From raw data to form data',
					'step_name' => 'Step from raw data to form data',
					'url' => 'http://www.zabbix.com',
					'raw_data' => 'login=Admin&password={{password}.urlencode()}',
					'check_post' => [
						['name' => 'login', 'value' => 'Admin'],
						['name' => 'password', 'value' => '{{password}.urlencode()}']
					],
					'raw' => true,
					'to_form' => true
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'From raw data to form data automatic encoding',
					'step_name' => 'Step from raw data to form data automatic encoding',
					'url' => 'http://www.zabbix.com',
					'raw_data' => 'login&enter=Sign+in%26',
					'check_post' => [
						['name' => 'login'],
						['name' => 'enter', 'value' => 'Sign in&']
					],
					'raw' => true,
					'to_form' => true
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'From raw data to form data, cyrillic',
					'step_name' => 'Step from raw data to form data, cyrillic',
					'url' => 'http://www.zabbix.com',
					'raw_data' => '%D1%82%D0%B5%D1%81%D1%82=%D1%82%D0%B5%D1%81%D1%82',
					'check_post' => [
						['name' => 'тест', 'value' => 'тест']
					],
					'raw' => true,
					'to_form' => true
				]
			],
			[
				[
					'expected' => TEST_ERROR,
					'name' => 'Post data validation value without name',
					'step_name' => 'Step post data validation value without name',
					'url' => 'http://www.zabbix.com',
					'raw_data' => '=value',
					'raw' => true,
					'to_form' => true,
					'errors' => 'Cannot convert POST data from raw data format to form field data format. Values without names are not allowed in form fields.'
				]
			],
			[
				[
					'expected' => TEST_ERROR,
					'name' => 'Post data validation percent encoding pair is malformed',
					'step_name' => 'Step post data validation percent encoding pair is malformed',
					'url' => 'http://www.zabbix.com',
					'raw_data' => 'test=%11',
					'raw' => true,
					'to_form' => true,
					'errors' => 'Cannot convert POST data from raw data format to form field data format. Data is not properly encoded.'
				]
			],
			[
				[
					'expected' => TEST_ERROR,
					'name' => 'Post data validation non-printable characters',
					'step_name' => 'Step post data validation non-printable characters',
					'url' => 'http://www.zabbix.com',
					'raw_data' => 'value=%00',
					'raw' => true,
					'to_form' => true,
					'errors' => 'Cannot convert POST data from raw data format to form field data format. Data is not properly encoded.'
				]
			],
			[
				[
					'expected' => TEST_ERROR,
					'name' => 'Post data validation pair value contains unencoded “=” character',
					'step_name' => 'Step post data validation pair value contains unencoded “=” character',
					'url' => 'http://www.zabbix.com',
					'raw_data' => 'name=val=ue',
					'raw' => true,
					'to_form' => true,
					'errors' => 'Cannot convert POST data from raw data format to form field data format. Data is not properly encoded.'
				]
			],
			[
				[
					'expected' => TEST_ERROR,
					'name' => 'Post data validation non-unicode encodings',
					'step_name' => 'Step post data validation non-unicode encodings',
					'url' => 'http://www.zabbix.com',
					'raw_data' => 'value=%EA%EE%EB%E1%E0%F1%EA%E8',
					'raw' => true,
					'to_form' => true,
					'errors' => 'Cannot convert POST data from raw data format to form field data format. Data is not properly encoded.'
				]
			],
			[
				[
					'expected' => TEST_ERROR,
					'name' => 'Post data validation field name exceeds 255 characters',
					'step_name' => 'Step post data validation field name exceeds 255 characters',
					'url' => 'http://www.zabbix.com',
					'raw_data' => 'qwertyuiopqwertyuiopqwertyuiopqwertyui'.
							'opqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwe.'.
							'rtyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqw'.
							'ertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwer'.
							'tyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiop123456789012345',
					'raw' => true,
					'to_form' => true,
					'errors' => 'Cannot convert POST data from raw data format to form field data format. Name of the form field should not exceed 255 characters.'
				]
			],
			// Variables -just numbers
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Variables -just numbers',
					'step_name' => 'Step variables -just numbers',
					'url' => 'http://www.zabbix.com',
					'variables' => [
						['name' => '{1234567890}', 'value' => '123456']
					]
				]
			],
			// Variables -symbols
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Variables -just symbols',
					'step_name' => 'Step variables -just symbols',
					'url' => 'http://www.zabbix.com',
					'name' => 'Variables -symbols',
					'variables' => [
						['name' => '{!@#$%^&*()_+:"|<>?,./}']
					]
				]
			],
			// Variables -255 max allowed
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Variables -255 length',
					'step_name' => 'Step variables -255 length',
					'url' => 'http://www.zabbix.com',
					'variables' => [
						['name' => '{qwertyuiopqwertyuiopqwertyuiopqwertyui'.
							'opqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwe.'.
							'rtyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqw'.
							'ertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwer'.
							'tyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiop123456789012}']
					]
				]
			],
			// Variables -without {}
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Variables -without {}',
					'step_name' => 'Step variables -without {}',
					'url' => 'http://www.zabbix.com',
					'variables' => [
						['name' => 'test']
					],
					'error_webform' => true,
					'error_msg' => 'Cannot add web scenario',
					'errors' => [
						'Invalid parameter "/1/steps/1/variables/1/name": is not enclosed in {} or is malformed.'
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Variables -without {}',
					'step_name' => 'Step variables -without {}',
					'url' => 'http://www.zabbix.com',
					'variables' => [
						['name' => '{test']
					],
					'error_webform' => true,
					'error_msg' => 'Cannot add web scenario',
					'errors' => [
						'Invalid parameter "/1/steps/1/variables/1/name": is not enclosed in {} or is malformed.'
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Variables -without {}',
					'step_name' => 'Step variables -without {}',
					'url' => 'http://www.zabbix.com',
					'variables' => [
						['name' => 'test}']
					],
					'error_webform' => true,
					'error_msg' => 'Cannot add web scenario',
					'errors' => [
						'Invalid parameter "/1/steps/1/variables/1/name": is not enclosed in {} or is malformed.'
					]
				]
			],
			// Variables -with the same names
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Variables -with the same names',
					'step_name' => 'Step variables -with the same names',
					'url' => 'http://www.zabbix.com',
					'variables' => [
						['name' => '{test}'],
						['name' => '{test}']
					],
					'error_webform' => true,
					'error_msg' => 'Cannot add web scenario',
					'errors' => [
						'Invalid parameter "/1/steps/1/variables/2": value (name)=({test}) already exists.'
					]
				]
			],
			// Variables -two different
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Variables -two different',
					'step_name' => 'Step variables -two different',
					'url' => 'http://www.zabbix.com',
					'variables' => [
						['name' => '{test1}', 'value' => 'test1'],
						['name' => '{test2}', 'value' => 'test1']
					]
				]
			],
			// Variables -empty name
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Variables -empty name',
					'step_name' => 'Step variables -empty name',
					'url' => 'http://www.zabbix.com',
					'variables' => [
						['value' => 'test']
					],
					'error_webform' => true,
					'error_msg' => 'Cannot add web scenario',
					'errors' => [
						'Invalid parameter "/1/steps/1/variables/1/name": cannot be empty.'
					]
				]
			],
			// Headers -just numbers
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Headers -just numbers',
					'step_name' => 'Step headers -just numbers',
					'url' => 'http://www.zabbix.com',
					'name' => 'Headers -just numbers',
					'headers' => [
						['name' => '1234567890', 'value' => '123456']
					]
				]
			],
			// Headers -just symbols
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Headers -just symbols',
					'step_name' => 'Step headers -just symbols',
					'url' => 'http://www.zabbix.com',
					'headers' => [
						['name' => '!@#$%^&*()_+:"{}|<>?,./', 'value' => '!@#$%^&*()_+:"{}|<>?,./']
					]
				]
			],
			// Headers -255 length
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Headers -255 length',
					'step_name' => 'Step headers -255 length',
					'url' => 'http://www.zabbix.com',
					'headers' => [
						['name' => 'qwertyuiopqwertyuiopqwertyuiopqwertyui'.
							'opqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwe.'.
							'rtyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqw'.
							'ertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwer'.
							'tyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiop12345678901234',
							'value' => 'qwertyuiopqwertyuiopqwertyuiopqwertyui'.
							'opqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwe.'.
							'rtyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqw'.
							'ertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwer'.
							'tyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiop12345678901234']
					]
				]
			],
			// Headers -the same names and values
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Headers -the same names and values',
					'step_name' => 'Step headers -the same names and values',
					'url' => 'http://www.zabbix.com',
					'headers' => [
						['name' => 'test', 'value' => 'test_value'],
						['name' => 'test', 'value' => 'test_value']
					]
				]
			],
			// Headers -empty value
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Headers -empty value',
					'step_name' => 'Step headers -empty value',
					'url' => 'http://www.zabbix.com',
					'headers' => [
						['name' => 'test'],
					],
					'error_webform' => true,
					'error_msg' => 'Cannot add web scenario',
					'errors' => [
						'Invalid parameter "/1/steps/1/headers/1/value": cannot be empty.'
					]
				]
			],
			// Headers -empty name
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Headers -empty name',
					'step_name' => 'Step headers -empty name',
					'url' => 'http://www.zabbix.com',
					'headers' => [
						['value' => 'test'],
					],
					'error_webform' => true,
					'error_msg' => 'Cannot add web scenario',
					'errors' => [
						'Invalid parameter "/1/steps/1/headers/1/name": cannot be empty.'
					]
				]
			],
			// Retrieve only headers
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Retrieve only headers',
					'step_name' => 'Step retrieve only headers',
					'url' => 'http://www.zabbix.com',
					'retrieve' => true
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Retrieve only headers with post value',
					'step_name' => 'Step retrieve only headers with post value',
					'url' => 'http://www.zabbix.com',
					'post' => [
						['value' => 'test'],
					],
					'retrieve' => true
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Retrieve only headers with post name',
					'step_name' => 'Step retrieve only headers with post name',
					'url' => 'http://www.zabbix.com',
					'post' => [
						['value' => 'test'],
					],
					'retrieve' => true
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'Retrieve only headers with post value and name',
					'step_name' => 'Step retrieve only headers with post value and name',
					'url' => 'http://www.zabbix.com',
					'post' => [
						['value' => 'test'],
					],
					'retrieve' => true
				]
			],
			// Timeout
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Timeout -1',
					'step_name' => 'Step timeout -1',
					'url' => 'http://www.zabbix.com',
					'timeout' => '-1',
					'error_msg' => 'Page received incorrect data',
					'errors' => [
						'Field "Timeout" is not correct: a time unit is expected'
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Timeout 65536',
					'step_name' => 'Step timeout 65536',
					'url' => 'http://www.zabbix.com',
					'timeout' => 3601,
					'error_webform' => true,
					'error_msg' => 'Cannot add web scenario',
					'errors' => [
						'Invalid parameter "/1/steps/1/timeout": value must be one of 0-3600'
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Timeout string',
					'step_name' => 'Step timeout string',
					'url' => 'http://www.zabbix.com',
					'timeout' => 'abc',
					'error_msg' => 'Page received incorrect data',
					'errors' => [
						'Field "Timeout" is not correct: a time unit is expected'
					]
				]
			],
			// Required status codes
			[
				[
					'expected' => TEST_BAD,
					'name' => 'Required status codes - symbols',
					'step_name' => 'Step required status codes - symbols',
					'url' => 'http://www.zabbix.com',
					'code' => 'abcd',
					'error_webform' => true,
					'error_msg' => 'Cannot add web scenario',
					'errors' => [
						'Invalid response code "abcd".'
					]
				]
			],
			// Fill all form
			[
					[
					'expected' => TEST_GOOD,
					'name' => 'Fill all step form',
					'step_name' => 'Fill all step form',
					'url' => 'http://www.zabbix.com',
					'parse' => true,
					'post' => [
						['name' => 'post', 'value' => 'test_post']
					],
					'query' => [
						['name' => 'query', 'value' => 'test_query']
					],
					'variables' => [
						['name' => '{variable}', 'value' => 'test_variable'],
					],
					'headers' => [
						['name' => 'header', 'value' => 'test_header'],
					],
					'timeout' => 3600,
					'string' => 'Zabbix',
					'code' => 200,
					'dbCheck' => true
				]
			],
			[
					[
					'expected' => TEST_GOOD,
					'name' => 'Fill all step form and raw data',
					'step_name' => 'Fill all step form and raw data',
					'url' => 'http://www.zabbix.com',
					'parse' => true,
					'post' => [
						['name' => 'zab bix', 'value' => 'tes&t']
					],
					'raw' => true,
					'check_raw' => 'zab%20bix=tes%26t',
					'query' => [
						['name' => 'query', 'value' => 'test_query']
					],
					'variables' => [
						['name' => '{variable}', 'value' => 'test_variable'],
					],
					'headers' => [
						['name' => 'header', 'value' => 'test_header'],
					],
					'timeout' => 0,
					'string' => 'Zabbix',
					'code' => 404,
					'dbCheck' => true
				]
			]
		];
	}

	/*
	 * Type text into input field and fire onchange event.
	 */
	protected function typeAndEscalateChangeEvent($id, $text) {
		$this->zbxTestInputType($id, $text);

		$this->webDriver->executeScript('var event = document.createEvent("HTMLEvents");'.
				'event.initEvent("change", false, true);'.
				'arguments[0].dispatchEvent(event);',
				[$this->webDriver->findElement(WebDriverBy::id($id))]
		);
	}

	/**
	 * @dataProvider steps
	 */
	public function testFormWebStep_CreateSteps($data) {
		$this->zbxTestLogin('httpconf.php?groupid=0&hostid=40001&form=Create+web+scenario');
		$this->zbxTestCheckTitle('Configuration of web monitoring');
		$this->zbxTestCheckHeader('Web monitoring');

		$this->zbxTestInputTypeWait('name', $data['name']);
		$this->zbxTestTabSwitchById('tab_stepTab' ,'Steps');
		$this->zbxTestClickWait('add_step');
		$this->zbxTestSwitchToWindow('zbx_popup');

		if (array_key_exists('step_name', $data)) {
			$this->zbxTestInputTypeWait('name',$data['step_name']);
		}

		if (array_key_exists('url', $data)) {
			$this->zbxTestInputTypeWait('url',$data['url']);
		}

		if (array_key_exists('query', $data)) {
			$i = 1;
			foreach($data['query'] as $item) {
				if (array_key_exists('name', $item)) {
					$this->typeAndEscalateChangeEvent('pairs_'.$i.'_name', $item['name']);
				}
				if (array_key_exists('value', $item)) {
					$this->typeAndEscalateChangeEvent('pair_value_'.$i, $item['value']);
				}
				$this->zbxTestClickXpath("//tr[@id='query_fields_footer']//button");
				$i = 5;
			}
		}

		if (array_key_exists('parse', $data)) {
			$this->zbxTestClick('parse');
		}

		if (array_key_exists('post', $data)) {
			$i = 2;
			foreach($data['post'] as $item) {
				if (array_key_exists('name', $item)) {
					$this->typeAndEscalateChangeEvent('pairs_'.$i.'_name', $item['name']);
				}
				if (array_key_exists('value', $item)) {
					$this->typeAndEscalateChangeEvent('pair_value_'.$i, $item['value']);
				}
				$this->zbxTestClickXpath("//tr[@id='post_fields_footer']//button");
				$i = 5;
			}
		}

		if (array_key_exists('variables', $data)) {
			$i = 3;
			foreach($data['variables'] as $item) {
				if (array_key_exists('name', $item)) {
					$this->typeAndEscalateChangeEvent('pairs_'.$i.'_name', $item['name']);
				}
				if (array_key_exists('value', $item)) {
					$this->typeAndEscalateChangeEvent('pair_value_'.$i, $item['value']);
				}
				$this->zbxTestClickXpath("//tr[@id='variables_footer']//button");
				$i = 5;
			}
		}

		if (array_key_exists('headers', $data)) {
			$i = 4;
			foreach($data['headers'] as $item) {
				if (array_key_exists('name', $item)) {
					$this->typeAndEscalateChangeEvent('pairs_'.$i.'_name', $item['name']);
				}
				if (array_key_exists('value', $item)) {
					$this->typeAndEscalateChangeEvent('pair_value_'.$i, $item['value']);
				}
				$this->zbxTestClickXpath("//tr[@id='headers_footer']//button");
				$i = 5;
			}
		}

		if (array_key_exists('raw', $data)) {
			$this->zbxTestClickXpath("//label[@for='post_type_1']");
		}

		if (array_key_exists('to_form', $data)) {
			$this->zbxTestInputType('posts', $data['raw_data']);
			$this->zbxTestClickXpath("//label[@for='post_type_0']");
		}

		if (array_key_exists('retrieve', $data)) {
			$this->zbxTestCheckboxSelect('retrieve_mode');
			$this->zbxTestAssertElementPresentXpath("//input[@id='post_type_0'][@disabled]");
			$this->zbxTestAssertElementPresentXpath("//input[@id='post_type_1'][@disabled]");
			$this->zbxTestAssertElementPresentXpath("//input[@id='pairs_2_name'][@disabled]");
			$this->zbxTestAssertElementPresentXpath("//input[@id='pair_value_2'][@disabled]");
			$this->zbxTestAssertElementPresentXpath("//input[@id='required'][@disabled]");
		}

		if (array_key_exists('timeout', $data)) {
			$this->zbxTestInputTypeOverwrite('timeout',$data['timeout']);
		}

		if (array_key_exists('string', $data)) {
			$this->zbxTestInputType('required',$data['string']);
		}

		if (array_key_exists('code', $data)) {
			$this->zbxTestInputType('status_codes',$data['code']);
		}

		if ($data['expected'] != TEST_ERROR) {
			$this->zbxTestClick('add');
		}

		if (array_key_exists('check_raw', $data)) {
			$this->zbxTestWaitWindowClose();
			$this->zbxTestClickLinkText($data['step_name']);
			$this->zbxTestSwitchToWindow('zbx_popup');
			$this->zbxTestWaitUntilElementPresent(webDriverBy::id('posts'));
			$raw = $this->zbxTestGetText("//textarea[@id='posts']");
			$this->assertEquals($data['check_raw'], $raw);
			$this->zbxTestClick('cancel');
		}

		if (array_key_exists('parse_query', $data)) {
			$this->zbxTestWaitWindowClose();
			$this->zbxTestClickLinkText($data['step_name']);
			$this->zbxTestSwitchToWindow('zbx_popup');
			$this->zbxTestWaitUntilElementPresent(webDriverBy::id('posts'));
			$i = 1;
			foreach($data['parse_query'] as $item) {
				$name = $this->zbxTestGetValue("//input[@id='pairs_".$i."_name']");
				$this->assertEquals($item['name'], $name);
				if (array_key_exists('value', $item)) {
					$value = $this->zbxTestGetValue("//input[@id='pair_value_".$i."']");
					$this->assertEquals($item['value'], $value);
				}
				$i = 5;
			}

			if (array_key_exists('check_url', $data)) {
				$url = $this->zbxTestGetValue("//input[@id='url']");
				$this->assertEquals($data['check_url'], $url);
			}
			$this->zbxTestClick('cancel');
		}

		if (array_key_exists('check_post', $data)) {
			$this->zbxTestWaitWindowClose();
			$this->zbxTestClickLinkText($data['step_name']);
			$this->zbxTestSwitchToWindow('zbx_popup');
			$this->zbxTestWaitUntilElementPresent(webDriverBy::id('posts'));
			$i = 2;
			foreach($data['check_post'] as $item) {
				$name = $this->zbxTestGetValue("//input[@id='pairs_".$i."_name']");
				$this->assertEquals($item['name'], $name);
				if (array_key_exists('value', $item)) {
					$value = $this->zbxTestGetValue("//input[@id='pair_value_".$i."']");
					$this->assertEquals($item['value'], $value);
				}
				$i = 5;
			}

			if (array_key_exists('check_url', $data)) {
				$url = $this->zbxTestGetValue("//input[@id='url']");
				$this->assertEquals($data['check_url'], $url);
			}
			$this->zbxTestClick('cancel');
		}

		if (array_key_exists('error_webform', $data)) {
			$this->zbxTestWaitWindowClose();
			$this->zbxTestClickWait('add');
		}

		switch ($data['expected']) {
			case TEST_GOOD:
				$this->zbxTestWaitWindowClose();
				$this->zbxTestClickWait('add');
				$this->zbxTestWaitUntilMessageTextPresent('msg-good', 'Web scenario added');
				$this->zbxTestCheckFatalErrors();
				break;
			case TEST_BAD:
				$this->zbxTestWaitUntilMessageTextPresent('msg-bad', $data['error_msg']);
				$this->zbxTestCheckFatalErrors();
				foreach ($data['errors'] as $msg) {
					$this->zbxTestTextPresent($msg);
				}
				break;
			case TEST_ERROR:
				$get_text = $this->zbxTestGetText("//div[@class='overlay-dialogue-body']/span");
				$result = trim(preg_replace('/\s\s+/', ' ', $get_text));
				$this->assertEquals($data['errors'], $result);
				break;
		}

		if (array_key_exists('dbCheck', $data)) {
			$result = DBselect("SELECT * FROM httpstep step LEFT JOIN httpstep_field test ON ".
				"step.httpstepid = test.httpstepid WHERE step.name = '".$data['step_name']."'");
			while ($row = DBfetch($result)) {
				$this->assertEquals($row['url'], $data['url']);
				$this->assertEquals($row['timeout'], $data['timeout']);
				$this->assertEquals($row['required'], $data['string']);
				$this->assertEquals($row['status_codes'], $data['code']);

				if (array_key_exists('check_raw', $data)) {
					$this->assertEquals($row['posts'], $data['check_raw']);
				}

				switch ($row['type']) {
					case 0:
						$this->assertEquals($row['name'], $data['headers'][0]['name']);
						$this->assertEquals($row['value'], $data['headers'][0]['value']);
						break;
					case 1:
						$this->assertEquals($row['name'], $data['variables'][0]['name']);
						$this->assertEquals($row['value'], $data['variables'][0]['value']);
						break;
					case 3:
						$this->assertEquals($row['name'], $data['query'][0]['name']);
						$this->assertEquals($row['value'], $data['query'][0]['value']);
						break;
					case 2:
						$this->assertEquals($row['name'], $data['post'][0]['name']);
						$this->assertEquals($row['value'], $data['post'][0]['value']);
						break;
				}
			}
		}
	}
}
