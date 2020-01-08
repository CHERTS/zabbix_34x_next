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

#include "common.h"
#include "zbxjson.h"
#include "json_parser.h"

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_strerror                                                *
 *                                                                            *
 * Purpose: return string describing json error                               *
 *                                                                            *
 * Return value: pointer to the null terminated string                        *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 ******************************************************************************/
#define ZBX_JSON_MAX_STRERROR	255

static char	zbx_json_strerror_message[ZBX_JSON_MAX_STRERROR];

const char	*zbx_json_strerror(void)
{
	return zbx_json_strerror_message;
}

#ifdef HAVE___VA_ARGS__
#	define zbx_set_json_strerror(fmt, ...) __zbx_zbx_set_json_strerror(ZBX_CONST_STRING(fmt), ##__VA_ARGS__)
#else
#	define zbx_set_json_strerror __zbx_zbx_set_json_strerror
#endif
static void	__zbx_zbx_set_json_strerror(const char *fmt, ...)
{
	va_list	args;

	va_start(args, fmt);

	zbx_vsnprintf(zbx_json_strerror_message, sizeof(zbx_json_strerror_message), fmt, args);

	va_end(args);
}

/******************************************************************************
 *                                                                            *
 * Function: __zbx_json_realloc                                               *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
static void	__zbx_json_realloc(struct zbx_json *j, size_t need)
{
	int	realloc = 0;

	if (NULL == j->buffer)
	{
		if (need > sizeof(j->buf_stat))
		{
			j->buffer_allocated = need;
			j->buffer = (char *)zbx_malloc(j->buffer, j->buffer_allocated);
		}
		else
		{
			j->buffer_allocated = sizeof(j->buf_stat);
			j->buffer = j->buf_stat;
		}
		return;
	}

	while (need > j->buffer_allocated)
	{
		if (0 == j->buffer_allocated)
			j->buffer_allocated = 1024;
		else
			j->buffer_allocated *= 2;
		realloc = 1;
	}

	if (1 == realloc)
	{
		if (j->buffer == j->buf_stat)
		{
			j->buffer = NULL;
			j->buffer = (char *)zbx_malloc(j->buffer, j->buffer_allocated);
			memcpy(j->buffer, j->buf_stat, sizeof(j->buf_stat));
		}
		else
			j->buffer = (char *)zbx_realloc(j->buffer, j->buffer_allocated);
	}
}

void	zbx_json_init(struct zbx_json *j, size_t allocate)
{
	assert(j);

	j->buffer = NULL;
	j->buffer_allocated = 0;
	j->buffer_offset = 0;
	j->buffer_size = 0;
	j->status = ZBX_JSON_EMPTY;
	j->level = 0;
	__zbx_json_realloc(j, allocate);
	*j->buffer = '\0';

	zbx_json_addobject(j, NULL);
}

void	zbx_json_initarray(struct zbx_json *j, size_t allocate)
{
	assert(j);

	j->buffer = NULL;
	j->buffer_allocated = 0;
	j->buffer_offset = 0;
	j->buffer_size = 0;
	j->status = ZBX_JSON_EMPTY;
	j->level = 0;
	__zbx_json_realloc(j, allocate);
	*j->buffer = '\0';

	zbx_json_addarray(j, NULL);
}

void	zbx_json_clean(struct zbx_json *j)
{
	assert(j);

	j->buffer_offset = 0;
	j->buffer_size = 0;
	j->status = ZBX_JSON_EMPTY;
	j->level = 0;
	*j->buffer = '\0';

	zbx_json_addobject(j, NULL);
}

void	zbx_json_free(struct zbx_json *j)
{
	assert(j);

	if (j->buffer != j->buf_stat)
		zbx_free(j->buffer);
}

static size_t	__zbx_json_stringsize(const char *string, zbx_json_type_t type)
{
	size_t		len = 0;
	const char	*sptr;
	char		buffer[] = {"null"};

	for (sptr = (NULL != string ? string : buffer); '\0' != *sptr; sptr++)
	{
		switch (*sptr)
		{
			case '"':  /* quotation mark */
			case '\\': /* reverse solidus */
			case '\b': /* backspace */
			case '\f': /* formfeed */
			case '\n': /* newline */
			case '\r': /* carriage return */
			case '\t': /* horizontal tab */
				len += 2;
				break;
			default:
				if (0 != iscntrl(*sptr))
					len += 6;
				else
					len++;
		}
	}

	if (NULL != string && ZBX_JSON_TYPE_STRING == type)
		len += 2; /* "" */

	return len;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_num2hex                                                      *
 *                                                                            *
 * Purpose: convert parameter c (0-15) to hexadecimal value ('0'-'f')         *
 *                                                                            *
 * Parameters:                                                                *
 *      c - number 0-15                                                       *
 *                                                                            *
 * Return value:                                                              *
 *      '0'-'f'                                                               *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
static char	zbx_num2hex(unsigned char c)
{
	if (c >= 10)
		return (char)(c + 0x57);	/* a-f */
	else
		return (char)(c + 0x30);	/* 0-9 */
}

static char	*__zbx_json_insstring(char *p, const char *string, zbx_json_type_t type)
{
	const char	*sptr;
	char		buffer[] = {"null"};

	if (NULL != string && ZBX_JSON_TYPE_STRING == type)
		*p++ = '"';

	for (sptr = (NULL != string ? string : buffer); '\0' != *sptr; sptr++)
	{
		switch (*sptr)
		{
			case '"':		/* quotation mark */
				*p++ = '\\';
				*p++ = '"';
				break;
			case '\\':		/* reverse solidus */
				*p++ = '\\';
				*p++ = '\\';
				break;
			case '\b':		/* backspace */
				*p++ = '\\';
				*p++ = 'b';
				break;
			case '\f':		/* formfeed */
				*p++ = '\\';
				*p++ = 'f';
				break;
			case '\n':		/* newline */
				*p++ = '\\';
				*p++ = 'n';
				break;
			case '\r':		/* carriage return */
				*p++ = '\\';
				*p++ = 'r';
				break;
			case '\t':		/* horizontal tab */
				*p++ = '\\';
				*p++ = 't';
				break;
			default:
				if (0 != iscntrl(*sptr))
				{
					*p++ = '\\';
					*p++ = 'u';
					*p++ = '0';
					*p++ = '0';
					*p++ = zbx_num2hex((*sptr >> 4) & 0xf);
					*p++ = zbx_num2hex(*sptr & 0xf);
				}
				else
					*p++ = *sptr;
		}
	}

	if (NULL != string && ZBX_JSON_TYPE_STRING == type)
		*p++ = '"';

	return p;
}

void	zbx_json_escape(char **string)
{
	size_t	size;
	char	*buffer;

	if (0 == (size = __zbx_json_stringsize(*string, ZBX_JSON_TYPE_UNKNOWN)))
		return;

	buffer = zbx_malloc(NULL, size + 1);
	buffer[size] = '\0';
	__zbx_json_insstring(buffer, *string, ZBX_JSON_TYPE_UNKNOWN);
	zbx_free(*string);
	*string = buffer;
}

static void	__zbx_json_addobject(struct zbx_json *j, const char *name, int object)
{
	size_t	len = 2; /* brackets */
	char	*p, *psrc, *pdst;

	assert(j);

	if (ZBX_JSON_COMMA == j->status)
		len++; /* , */

	if (NULL != name)
	{
		len += __zbx_json_stringsize(name, ZBX_JSON_TYPE_STRING);
		len += 1; /* : */
	}

	__zbx_json_realloc(j, j->buffer_size + len + 1/*'\0'*/);

	psrc = j->buffer + j->buffer_offset;
	pdst = j->buffer + j->buffer_offset + len;

	memmove(pdst, psrc, j->buffer_size - j->buffer_offset + 1/*'\0'*/);

	p = psrc;

	if (ZBX_JSON_COMMA == j->status)
		*p++ = ',';

	if (NULL != name)
	{
		p = __zbx_json_insstring(p, name, ZBX_JSON_TYPE_STRING);
		*p++ = ':';
	}

	*p++ = object ? '{' : '[';
	*p = object ? '}' : ']';

	j->buffer_offset = p - j->buffer;
	j->buffer_size += len;
	j->level++;
	j->status = ZBX_JSON_EMPTY;
}

void	zbx_json_addobject(struct zbx_json *j, const char *name)
{
	__zbx_json_addobject(j, name, 1);
}

void	zbx_json_addarray(struct zbx_json *j, const char *name)
{
	__zbx_json_addobject(j, name, 0);
}

void	zbx_json_addstring(struct zbx_json *j, const char *name, const char *string, zbx_json_type_t type)
{
	size_t	len = 0;
	char	*p, *psrc, *pdst;

	assert(j);

	if (ZBX_JSON_COMMA == j->status)
		len++; /* , */

	if (NULL != name)
	{
		len += __zbx_json_stringsize(name, ZBX_JSON_TYPE_STRING);
		len += 1; /* : */
	}
	len += __zbx_json_stringsize(string, type);

	__zbx_json_realloc(j, j->buffer_size + len + 1/*'\0'*/);

	psrc = j->buffer + j->buffer_offset;
	pdst = j->buffer + j->buffer_offset + len;

	memmove(pdst, psrc, j->buffer_size - j->buffer_offset + 1/*'\0'*/);

	p = psrc;

	if (ZBX_JSON_COMMA == j->status)
		*p++ = ',';

	if (NULL != name)
	{
		p = __zbx_json_insstring(p, name, ZBX_JSON_TYPE_STRING);
		*p++ = ':';
	}
	p = __zbx_json_insstring(p, string, type);

	j->buffer_offset = p - j->buffer;
	j->buffer_size += len;
	j->status = ZBX_JSON_COMMA;
}

void	zbx_json_adduint64(struct zbx_json *j, const char *name, zbx_uint64_t value)
{
	char	buffer[MAX_ID_LEN];

	zbx_snprintf(buffer, sizeof(buffer), ZBX_FS_UI64, value);
	zbx_json_addstring(j, name, buffer, ZBX_JSON_TYPE_INT);
}

void	zbx_json_addint64(struct zbx_json *j, const char *name, zbx_int64_t value)
{
	char	buffer[MAX_ID_LEN];

	zbx_snprintf(buffer, sizeof(buffer), ZBX_FS_I64, value);
	zbx_json_addstring(j, name, buffer, ZBX_JSON_TYPE_INT);
}

void	zbx_json_addfloat(struct zbx_json *j, const char *name, double value)
{
	char	buffer[MAX_ID_LEN];

	zbx_snprintf(buffer, sizeof(buffer), ZBX_FS_DBL, value);
	zbx_json_addstring(j, name, buffer, ZBX_JSON_TYPE_INT);
}

int	zbx_json_close(struct zbx_json *j)
{
	if (1 == j->level)
	{
		zbx_set_json_strerror("cannot close top level object");
		return FAIL;
	}

	j->level--;
	j->buffer_offset++;
	j->status = ZBX_JSON_COMMA;

	return SUCCEED;
}

/******************************************************************************
 *                                                                            *
 * Function: __zbx_json_type                                                  *
 *                                                                            *
 * Purpose: return type of pointed value                                      *
 *                                                                            *
 * Return value: type of pointed value                                        *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
static zbx_json_type_t	__zbx_json_type(const char *p)
{
	if ('"' == *p)
		return ZBX_JSON_TYPE_STRING;
	if (('0' <= *p && *p <= '9') || '-' == *p)
		return ZBX_JSON_TYPE_INT;
	if ('[' == *p)
		return ZBX_JSON_TYPE_ARRAY;
	if ('{' == *p)
		return ZBX_JSON_TYPE_OBJECT;
	if ('n' == p[0] && 'u' == p[1] && 'l' == p[2] && 'l' == p[3])
		return ZBX_JSON_TYPE_NULL;

	zbx_set_json_strerror("invalid type of JSON value \"%.64s\"", p);

	return ZBX_JSON_TYPE_UNKNOWN;
}

/******************************************************************************
 *                                                                            *
 * Function: __zbx_json_rbracket                                              *
 *                                                                            *
 * Purpose: return position of right bracket                                  *
 *                                                                            *
 * Return value: position of right bracket                                    *
 *               NULL - an error occurred                                     *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
static const char	*__zbx_json_rbracket(const char *p)
{
	int	level = 0;
	int	state = 0; /* 0 - outside string; 1 - inside string */
	char	lbracket, rbracket;

	assert(p);

	lbracket = *p;

	if ('{' != lbracket && '[' != lbracket)
		return NULL;

	rbracket = ('{' == lbracket ? '}' : ']');

	while ('\0' != *p)
	{
		switch (*p)
		{
			case '"':
				state = (0 == state ? 1 : 0);
				break;
			case '\\':
				if (1 == state)
					if ('\0' == *++p)
						return NULL;
				break;
			case '[':
			case '{':
				if (0 == state)
					level++;
				break;
			case ']':
			case '}':
				if (0 == state)
				{
					level--;
					if (0 == level)
						return (rbracket == *p ? p : NULL);
				}
				break;
		}
		p++;
	}

	return NULL;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_open                                                    *
 *                                                                            *
 * Purpose: open json buffer and check for brackets                           *
 *                                                                            *
 * Return value: SUCCESS - processed successfully                             *
 *               FAIL - an error occurred                                     *
 *                                                                            *
 ******************************************************************************/
int	zbx_json_open(const char *buffer, struct zbx_json_parse *jp)
{
	char	*error = NULL;
	int	len;

	SKIP_WHITESPACE(buffer);

	/* return immediate failure without logging when opening empty string */
	if ('\0' == *buffer)
		return FAIL;

	jp->start = buffer;
	jp->end = NULL;

	if (0 == (len = zbx_json_validate(jp->start, &error)))
	{
		if (NULL != error)
		{
			zbx_set_json_strerror("cannot parse as a valid JSON object: %s", error);
			zbx_free(error);
		}
		else
		{
			zbx_set_json_strerror("cannot parse as a valid JSON object \"%.64s\"", buffer);
		}

		return FAIL;
	}

	jp->end = jp->start + len - 1;

	return SUCCEED;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_next                                                    *
 *                                                                            *
 * Purpose: locate next pair or element                                       *
 *                                                                            *
 * Return value: NULL - no more values                                        *
 *               NOT NULL - pointer to pair or element                        *
 *      {"name",...    or  "array":["name", ... ,1,null]                      *
 * p =   ^                                         ^                          *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
const char	*zbx_json_next(const struct zbx_json_parse *jp, const char *p)
{
	int	level = 0;
	int	state = 0;	/* 0 - outside string; 1 - inside string */

	if (1 == jp->end - jp->start)	/* empty object or array */
		return NULL;

	if (NULL == p)
	{
		p = jp->start + 1;
		SKIP_WHITESPACE(p);
		return p;
	}

	while (p <= jp->end)
	{
		switch (*p)
		{
			case '"':
				state = (0 == state) ? 1 : 0;
				break;
			case '\\':
				if (1 == state)
					p++;
				break;
			case '[':
			case '{':
				if (0 == state)
					level++;
				break;
			case ']':
			case '}':
				if (0 == state)
				{
					if (0 == level)
						return NULL;
					level--;
				}
				break;
			case ',':
				if (0 == state && 0 == level)
				{
					p++;
					SKIP_WHITESPACE(p);
					return p;
				}
				break;
		}
		p++;
	}

	return NULL;
}

static const char	*zbx_json_decodenull(const char *p)
{
	if ('n' == p[0] && 'u' == p[1] && 'l' == p[2] && 'l' == p[3])
		return p + 4;

	return NULL;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_is_valid_json_hex                                            *
 *                                                                            *
 * Purpose: check if a 4 character sequence is a valid hex number 0000 - FFFF *
 *                                                                            *
 * Parameters:                                                                *
 *      p - pointer to the 1st character                                      *
 *                                                                            *
 * Return value: SUCCEED or FAIL                                              *
 *                                                                            *
 ******************************************************************************/
static int	zbx_is_valid_json_hex(const char *p)
{
	int	i;

	for (i = 0; i < 4; ++i, ++p)
	{
		if (0 == isxdigit(*p))
			return FAIL;
	}

	return SUCCEED;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_hex2num                                                      *
 *                                                                            *
 * Purpose: convert hexit c ('0'-'9''a'-'f''A'-'F') to number (0-15)          *
 *                                                                            *
 * Parameters:                                                                *
 *      c - char ('0'-'9''a'-'f''A'-'F')                                      *
 *                                                                            *
 * Return value:                                                              *
 *      0-15                                                                  *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
static unsigned int	zbx_hex2num(char c)
{
	int	res;

	if (c >= 'a')
		res = c - 'a' + 10;	/* a-f */
	else if (c >= 'A')
		res = c - 'A' + 10;	/* A-F */
	else
		res = c - '0';		/* 0-9 */

	return (unsigned int)res;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_decode_character                                        *
 *                                                                            *
 * Purpose: decodes JSON escape character into UTF-8                          *
 *                                                                            *
 * Parameters: p - [IN/OUT] a pointer to the first character in string        *
 *             bytes - [OUT] a 4-element array where 1 - 4 bytes of character *
 *                     UTF-8 representation are written                       *
 *                                                                            *
 * Return value: number of UTF-8 bytes written into 'bytes' array or          *
 *               0 on error (invalid escape sequence)                         *
 *                                                                            *
 ******************************************************************************/
static unsigned int	zbx_json_decode_character(const char **p, unsigned char *bytes)
{
	bytes[0] = '\0';

	switch (**p)
	{
		case '"':
			bytes[0] = '"';
			break;
		case '\\':
			bytes[0] = '\\';
			break;
		case '/':
			bytes[0] = '/';
			break;
		case 'b':
			bytes[0] = '\b';
			break;
		case 'f':
			bytes[0] = '\f';
			break;
		case 'n':
			bytes[0] = '\n';
			break;
		case 'r':
			bytes[0] = '\r';
			break;
		case 't':
			bytes[0] = '\t';
			break;
		default:
			break;
	}

	if ('\0' != bytes[0])
	{
		++*p;
		return 1;
	}

	if ('u' == **p)		/* \u0000 - \uffff */
	{
		unsigned int	num;

		if (FAIL == zbx_is_valid_json_hex(++*p))
			return 0;

		num = zbx_hex2num(**p) << 12;
		num += zbx_hex2num(*(++*p)) << 8;
		num += zbx_hex2num(*(++*p)) << 4;
		num += zbx_hex2num(*(++*p));
		++*p;

		if (0x007f >= num)	/* 0000 - 007f */
		{
			bytes[0] = (unsigned char)num;
			return 1;
		}
		else if (0x07ff >= num)	/* 0080 - 07ff */
		{
			bytes[0] = (unsigned char)(0xc0 | ((num >> 6) & 0x1f));
			bytes[1] = (unsigned char)(0x80 | (num & 0x3f));
			return 2;
		}
		else if (0xd7ff >= num || 0xe000 <= num)	/* 0800 - d7ff or e000 - ffff */
		{
			bytes[0] = (unsigned char)(0xe0 | ((num >> 12) & 0x0f));
			bytes[1] = (unsigned char)(0x80 | ((num >> 6) & 0x3f));
			bytes[2] = (unsigned char)(0x80 | (num & 0x3f));
			return 3;
		}
		else if (0xd800 <= num && num <= 0xdbff)	/* high surrogate d800 - dbff */
		{
			unsigned int	num_lo, uc;

			/* collect the low surrogate */

			if ('\\' != **p || 'u' != *(++*p) || FAIL == zbx_is_valid_json_hex(++*p))
				return 0;

			num_lo = zbx_hex2num(**p) << 12;
			num_lo += zbx_hex2num(*(++*p)) << 8;
			num_lo += zbx_hex2num(*(++*p)) << 4;
			num_lo += zbx_hex2num(*(++*p));
			++*p;

			if (num_lo < 0xdc00 || 0xdfff < num_lo)		/* low surrogate range is dc00 - dfff */
				return 0;

			/* decode surrogate pair */

			uc = 0x010000 + ((num & 0x03ff) << 10) + (num_lo & 0x03ff);

			bytes[0] = (unsigned char)(0xf0 | ((uc >> 18) & 0x07));
			bytes[1] = (unsigned char)(0x80 | ((uc >> 12) & 0x3f));
			bytes[2] = (unsigned char)(0x80 | ((uc >> 6) & 0x3f));
			bytes[3] = (unsigned char)(0x80 | (uc & 0x3f));
			return 4;
		}
		/* error - low surrogate without high surrogate */
	}

	return 0;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_copy_string                                             *
 *                                                                            *
 * Purpose: copies json name/string value by omitting leading/trailing " and  *
 *          converting escape sequences                                       *
 *                                                                            *
 * Parameters: p     - [IN] a pointer to the next character in string         *
 *             out   - [OUT] the output buffer                                *
 *             size  - [IN] the output buffer size                            *
 *                                                                            *
 * Return value: A pointer to the next character in input string or NULL if   *
 *               string copying failed.                                       *
 *                                                                            *
 ******************************************************************************/
static const char	*zbx_json_copy_string(const char *p, char *out, size_t size)
{
	char	*start = out;

	p++;

	while ('\0' != *p)
	{
		switch (*p)
		{
			unsigned int	nbytes, i;
			unsigned char	uc[4];	/* decoded Unicode character takes 1-4 bytes in UTF-8 */

			case '\\':
				++p;
				if (0 == (nbytes = zbx_json_decode_character(&p, uc)))
					return NULL;

				if ((size_t)(out - start) + nbytes >= size)
					return NULL;

				for (i = 0; i < nbytes; ++i)
					*out++ = (char)uc[i];

				break;
			case '"':
				*out = '\0';
				return ++p;
			default:
				*out++ = *p++;
		}

		if ((size_t)(out - start) == size)
			break;
	}

	return NULL;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_copy_value                                              *
 *                                                                            *
 * Purpose: copies json value                                                 *
 *                                                                            *
 * Parameters: p     - [IN] a pointer to the next character in string         *
 *             len   - [IN] the value length                                  *
 *             out   - [OUT] the output buffer                                *
 *             size  - [IN] the output buffer size                            *
 *                                                                            *
 * Return value: A pointer to the next character in input string or NULL if   *
 *               string copying failed.                                       *
 *                                                                            *
 * Comments: String values are converted (leading/trailing " dropped and      *
 *           escape sequences translated) while other values are simply       *
 *           copied.                                                          *
 *                                                                            *
 ******************************************************************************/
static const char	*zbx_json_copy_value(const char *p, size_t len, char *out, size_t size)
{
	if (ZBX_JSON_TYPE_STRING == __zbx_json_type(p))
	{
		if (NULL == zbx_json_copy_string(p, out, size))
			return NULL;
	}
	else
		zbx_strlcpy(out, p, MIN(size, len + 1));

	return p + len;
}

static const char	*zbx_json_decodevalue(const char *p, char *string, size_t size, int *is_null)
{
	size_t	len;

	switch (__zbx_json_type(p))
	{
		case ZBX_JSON_TYPE_STRING:
		case ZBX_JSON_TYPE_INT:
			if (NULL != is_null)
				*is_null = 0;

			if (0 == (len = json_parse_value(p, NULL)))
				return NULL;

			return zbx_json_copy_value(p, len, string, size);
		case ZBX_JSON_TYPE_NULL:
			if (NULL != is_null)
				*is_null = 1;
			*string = '\0';
			return zbx_json_decodenull(p);
		default:
			return NULL;
	}
}

static const char	*zbx_json_decodevalue_dyn(const char *p, char **string, size_t *string_alloc, int *is_null)
{
	size_t	len;

	switch (__zbx_json_type(p))
	{
		case ZBX_JSON_TYPE_STRING:
		case ZBX_JSON_TYPE_INT:
			if (NULL != is_null)
				*is_null = 0;

			if (0 == (len = json_parse_value(p, NULL)))
				return NULL;

			if (*string_alloc <= len)
			{
				*string_alloc = len + 1;
				*string = (char *)zbx_realloc(*string, *string_alloc);
			}

			return zbx_json_copy_value(p, len, *string, *string_alloc);
		case ZBX_JSON_TYPE_NULL:
			if (NULL != is_null)
				*is_null = 1;

			if (*string_alloc < 1)
			{
				*string_alloc = 1;
				*string = (char *)zbx_realloc(*string, *string_alloc);
			}

			**string = '\0';

			return zbx_json_decodenull(p);
		default:
			return NULL;
	}
}

const char	*zbx_json_pair_next(const struct zbx_json_parse *jp, const char *p, char *name, size_t len)
{
	if (NULL == (p = zbx_json_next(jp, p)))
		return NULL;

	if (ZBX_JSON_TYPE_STRING != __zbx_json_type(p))
		return NULL;

	if (NULL == (p = zbx_json_copy_string(p, name, len)))
		return NULL;

	SKIP_WHITESPACE(p);

	if (':' != *p++)
		return NULL;

	SKIP_WHITESPACE(p);

	return p;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_pair_by_name                                            *
 *                                                                            *
 * Purpose: find pair by name and return pointer to value                     *
 *                                                                            *
 * Return value: pointer to value                                             *
 *        {"name":["a","b",...]}                                              *
 *                ^ - returned pointer                                        *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
const char	*zbx_json_pair_by_name(const struct zbx_json_parse *jp, const char *name)
{
	char		buffer[MAX_STRING_LEN];
	const char	*p = NULL;

	while (NULL != (p = zbx_json_pair_next(jp, p, buffer, sizeof(buffer))))
		if (0 == strcmp(name, buffer))
			return p;

	zbx_set_json_strerror("cannot find pair with name \"%s\"", name);

	return NULL;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_next_value                                              *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
const char	*zbx_json_next_value(const struct zbx_json_parse *jp, const char *p, char *string, size_t len, int *is_null)
{
	if (NULL == (p = zbx_json_next(jp, p)))
		return NULL;

	return zbx_json_decodevalue(p, string, len, is_null);
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_next_value_dyn                                          *
 *                                                                            *
 ******************************************************************************/
const char	*zbx_json_next_value_dyn(const struct zbx_json_parse *jp, const char *p, char **string,
		size_t *string_alloc, int *is_null)
{
	if (NULL == (p = zbx_json_next(jp, p)))
		return NULL;

	return zbx_json_decodevalue_dyn(p, string, string_alloc, is_null);
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_value_by_name                                           *
 *                                                                            *
 * Purpose: return value by pair name                                         *
 *                                                                            *
 * Return value: SUCCEED - if value successfully parsed, FAIL - otherwise     *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
int	zbx_json_value_by_name(const struct zbx_json_parse *jp, const char *name, char *string, size_t len)
{
	const char	*p;

	if (NULL == (p = zbx_json_pair_by_name(jp, name)))
		return FAIL;

	if (NULL == zbx_json_decodevalue(p, string, len, NULL))
		return FAIL;

	return SUCCEED;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_value_by_name_dyn                                       *
 *                                                                            *
 * Purpose: return value by pair name                                         *
 *                                                                            *
 * Return value: SUCCEED - if value successfully parsed, FAIL - otherwise     *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
int	zbx_json_value_by_name_dyn(const struct zbx_json_parse *jp, const char *name, char **string, size_t *string_alloc)
{
	const char	*p;

	if (NULL == (p = zbx_json_pair_by_name(jp, name)))
		return FAIL;

	if (NULL == zbx_json_decodevalue_dyn(p, string, string_alloc, NULL))
		return FAIL;

	return SUCCEED;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_brackets_open                                           *
 *                                                                            *
 * Return value: SUCCESS - processed successfully                             *
 *               FAIL - an error occurred                                     *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
int	zbx_json_brackets_open(const char *p, struct zbx_json_parse *jp)
{
	if (NULL == (jp->end = __zbx_json_rbracket(p)))
	{
		zbx_set_json_strerror("cannot open JSON object or array \"%.64s\"", p);
		return FAIL;
	}

	SKIP_WHITESPACE(p);

	jp->start = p;

	return SUCCEED;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_brackets_by_name                                        *
 *                                                                            *
 * Return value: SUCCESS - processed successfully                             *
 *               FAIL - an error occurred                                     *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
int	zbx_json_brackets_by_name(const struct zbx_json_parse *jp, const char *name, struct zbx_json_parse *out)
{
	const char	*p;

	if (NULL == (p = zbx_json_pair_by_name(jp, name)))
		return FAIL;

	if (FAIL == zbx_json_brackets_open(p, out))
		return FAIL;

	return SUCCEED;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_object_is_empty                                         *
 *                                                                            *
 * Return value: SUCCESS - if object is empty                                 *
 *               FAIL - if object contains data                               *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
int	zbx_json_object_is_empty(const struct zbx_json_parse *jp)
{
	return jp->end - jp->start > 1 ? FAIL : SUCCEED;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_count                                                   *
 *                                                                            *
 * Return value: number of elements in zbx_json_parse object                  *
 *                                                                            *
 * Author: Alexander Vladishev                                                *
 *                                                                            *
 ******************************************************************************/
int	zbx_json_count(const struct zbx_json_parse *jp)
{
	int		num = 0;
	const char	*p = NULL;

	while (NULL != (p = zbx_json_next(jp, p)))
		num++;

	return num;
}


/*
 * limited JSONPath support
 */

#define ZBX_JSONPATH_COMPONENT_DOT	0
#define ZBX_JSONPATH_COMPONENT_BRACKET	1
#define ZBX_JSONPATH_ARRAY_INDEX	2

/******************************************************************************
 *                                                                            *
 * Function: zbx_jsonpath_error                                               *
 *                                                                            *
 * Purpose: sets json error message and returns FAIL                          *
 *                                                                            *
 * Comments: This function is used to return from json path parsing functions *
 *           in the case of failure.                                          *
 *                                                                            *
 ******************************************************************************/
static int	zbx_jsonpath_error(const char *path)
{
	zbx_set_json_strerror("unsupported character in json path starting with: \"%s\"", path);
	return FAIL;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_jsonpath_next                                                *
 *                                                                            *
 * Purpose: returns next component of json path                               *
 *                                                                            *
 * Parameters: path  - [IN] the json path                                     *
 *             pnext - [IN/OUT] the reference to the next path component      *
 *             loc   - [OUT] the location of the path component               *
 *             type  - [OUT] json path component type, see ZBX_JSONPATH_      *
 *                     defines                                                *
 *                                                                            *
 * Return value: SUCCEED - the json path component was parsed successfully    *
 *               FAIL    - json path parsing error                            *
 *                                                                            *
 ******************************************************************************/
static int	zbx_jsonpath_next(const char *path, const char **pnext, zbx_strloc_t *loc, int *type)
{
	const char	*next = *pnext;
	size_t		pos;
	char		quotes;

	if (NULL == next)
	{
		if ('$' != *path)
			return zbx_jsonpath_error(path);

		next = path + 1;
		*pnext = next;
	}

	/* process dot notation component */
	if (*next == '.')
	{
		if ('\0' == *(++next))
			return zbx_jsonpath_error(*pnext);

		loc->l = next - path;

		while (0 != isalnum(*next) || '_' == *next)
			next++;

		if ((pos = next - path) == loc->l)
			return zbx_jsonpath_error(*pnext);

		loc->r = pos - 1;
		*pnext = next;
		*type = ZBX_JSONPATH_COMPONENT_DOT;

		return SUCCEED;
	}

	if ('[' != *next)
		return zbx_jsonpath_error(*pnext);

	while (*(++next) == ' ')
		;

	/* process array index component */
	if (0 != isdigit(*next))
	{
		for (pos = 0; 0 != isdigit(next[pos]); pos++)
			;

		if (0 == pos)
			return zbx_jsonpath_error(*pnext);

		loc->l = next - path;
		loc->r = loc->l + pos - 1;

		next += pos;

		while (*next == ' ')
			next++;

		if (']' != *next++)
			return zbx_jsonpath_error(*pnext);

		*pnext = next;
		*type = ZBX_JSONPATH_ARRAY_INDEX;

		return SUCCEED;
	}

	loc->l = next - path + 1;

	for (quotes = *next++; quotes != *next; next++)
	{
		if ('\0' == *next)
			return zbx_jsonpath_error(*pnext);
	}

	if ((pos = next - path) == loc->l)
		return zbx_jsonpath_error(*pnext);

	loc->r = pos - 1;

	while (*(++next) == ' ')
		;

	if (']' != *next++)
		return zbx_jsonpath_error(*pnext);

	*pnext = next;
	*type = ZBX_JSONPATH_COMPONENT_BRACKET;

	return SUCCEED;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_path_open                                               *
 *                                                                            *
 * Purpose: opens an object by json path                                      *
 *                                                                            *
 * Return value: SUCCESS - processed successfully                             *
 *               FAIL - an error occurred                                     *
 *                                                                            *
 * Comments: Only direct path to single object in dot or bracket notation     *
 *           is supported.                                                    *
 *                                                                            *
 ******************************************************************************/
int	zbx_json_path_open(const struct zbx_json_parse *jp, const char *path, struct zbx_json_parse *out)
{
	const char		*p, *next = 0;
	char			buffer[MAX_STRING_LEN];
	zbx_strloc_t		loc;
	int			type, index;
	struct zbx_json_parse	object;

	object = *jp;

	do
	{
		if (FAIL == zbx_jsonpath_next(path, &next, &loc, &type))
			return FAIL;

		if (ZBX_JSONPATH_ARRAY_INDEX == type)
		{
			if ('[' != *object.start)
				return FAIL;

			if (FAIL == is_uint_n_range(path + loc.l, loc.r - loc.l + 1, &index, sizeof(index), 0,
					0xFFFFFFFF))
			{
				return FAIL;
			}

			for (p = NULL; NULL != (p = zbx_json_next(&object, p)) && 0 != index; index--)
				;

			if (0 != index || NULL == p)
			{
				zbx_set_json_strerror("array index out of bounds starting with json path: \"%s\"",
						path + loc.l);
				return FAIL;
			}
		}
		else
		{
			zbx_strlcpy(buffer, path + loc.l, loc.r - loc.l + 2);

			if (NULL == (p = zbx_json_pair_by_name(&object, buffer)))
			{
				zbx_set_json_strerror("object not found starting with json path: \"%s\"", path + loc.l);
				return FAIL;
			}
		}

		object.start = p;

		if (NULL == (object.end = __zbx_json_rbracket(p)))
			object.end = p + json_parse_value(p, NULL) - 1;
	}
	while ('\0' != *next);

	*out = object;

	return SUCCEED;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_json_value_dyn                                               *
 *                                                                            *
 * Purpose: return json fragment or value located at json parse location      *
 *                                                                            *
 ******************************************************************************/
void	zbx_json_value_dyn(const struct zbx_json_parse *jp, char **string, size_t *string_alloc)
{
	if (NULL == zbx_json_decodevalue_dyn(jp->start, string, string_alloc, NULL))
	{
		size_t	len = jp->end - jp->start + 2;

		if (*string_alloc < len)
			*string = (char *)zbx_realloc(*string, len);

		zbx_strlcpy(*string, jp->start, len);
	}
}


