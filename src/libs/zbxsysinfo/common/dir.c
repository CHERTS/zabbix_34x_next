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
#include "sysinfo.h"
#include "dir.h"
#include "zbxregexp.h"
#include "log.h"

#ifdef _WINDOWS
#	include "disk.h"
#endif

/******************************************************************************
 *                                                                            *
 * Function: filename_matches                                                 *
 *                                                                            *
 * Purpose: checks if filename matches the include-regexp and doesn't match   *
 *          the exclude-regexp                                                *
 *                                                                            *
 * Parameters: fname      - [IN] filename to be checked                       *
 *             regex_incl - [IN] regexp for filenames to include (NULL means  *
 *                               include any file)                            *
 *             regex_excl - [IN] regexp for filenames to exclude (NULL means  *
 *                               exclude none)                                *
 *                                                                            *
 * Return value: If filename passes both checks, nonzero value is returned.   *
 *               If filename fails to pass, 0 is returned.                    *
 *                                                                            *
 ******************************************************************************/
static int	filename_matches(const char *fname, const zbx_regexp_t *regex_incl, const zbx_regexp_t *regex_excl)
{
	return ((NULL == regex_incl || 0 == zbx_regexp_match_precompiled(fname, regex_incl)) &&
			(NULL == regex_excl || 0 != zbx_regexp_match_precompiled(fname, regex_excl)));
}

/******************************************************************************
 *                                                                            *
 * Function: queue_directory                                                  *
 *                                                                            *
 * Purpose: adds directory to processing queue after checking if current      *
 *          depth is less than 'max_depth'                                    *
 *                                                                            *
 * Parameters: list      - [IN/OUT] vector used to replace reccursion         *
 *                                  with iterative approach		      *
 *	       path      - [IN] directory path		                      *
 *             depth     - [IN] current traversal depth of directory          *
 *             max_depth - [IN] maximal traversal depth allowed (use -1       *
 *                              for unlimited directory traversal)            *
 *                                                                            *
 * Return value: SUCCEED - directory is queued,                               *
 *               FAIL - directory depth is more than allowed traversal depth. *
 *                                                                            *
 ******************************************************************************/
static int	queue_directory(zbx_vector_ptr_t *list, char *path, int depth, int max_depth)
{
	zbx_directory_item_t	*item;

	if (TRAVERSAL_DEPTH_UNLIMITED == max_depth || depth < max_depth)
	{
		item = (zbx_directory_item_t*)zbx_malloc(NULL, sizeof(zbx_directory_item_t));
		item->depth = depth + 1;
		item->path = path;	/* 'path' changes ownership. Do not free 'path' in the caller. */

		zbx_vector_ptr_append(list, item);

		return SUCCEED;
	}

	return FAIL;	/* 'path' did not go into 'list' - don't forget to free 'path' in the caller */
}

/******************************************************************************
 *                                                                            *
 * Function: compare_descriptors                                              *
 *                                                                            *
 * Purpose: compares two zbx_file_descriptor_t values to perform search       *
 *          within descriptor vector                                          *
 *                                                                            *
 * Parameters: file_a - [IN] file descriptor A                                *
 *             file_b - [IN] file descriptor B                                *
 *                                                                            *
 * Return value: If file descriptor values are the same, 0 is returned        *
 *               otherwise nonzero value is returned.                         *
 *                                                                            *
 ******************************************************************************/
static int	compare_descriptors(const void *file_a, const void *file_b)
{
	const zbx_file_descriptor_t	*fa, *fb;

	fa = *((zbx_file_descriptor_t **)file_a);
	fb = *((zbx_file_descriptor_t **)file_b);

	return (fa->st_ino != fb->st_ino || fa->st_dev != fb->st_dev);
}

static int	prepare_parameters(AGENT_REQUEST *request, AGENT_RESULT *result, zbx_regexp_t **regex_incl,
		zbx_regexp_t **regex_excl, int *mode, int *max_depth, char **dir, zbx_stat_t *status)
{
	char		*dir_param, *regex_incl_str, *regex_excl_str, *mode_str, *max_depth_str;
	const char	*error = NULL;

	if (5 < request->nparam)
	{
		SET_MSG_RESULT(result, zbx_strdup(NULL, "Too many parameters."));
		return FAIL;
	}

	dir_param = get_rparam(request, 0);
	regex_incl_str = get_rparam(request, 1);
	regex_excl_str = get_rparam(request, 2);
	mode_str = get_rparam(request, 3);
	max_depth_str = get_rparam(request, 4);

	if (NULL == dir_param || '\0' == *dir_param)
	{
		SET_MSG_RESULT(result, zbx_strdup(NULL, "Invalid first parameter."));
		return FAIL;
	}

	if (NULL != regex_incl_str && '\0' != *regex_incl_str)
	{
		if (SUCCEED != zbx_regexp_compile(regex_incl_str, regex_incl, &error))
		{
			SET_MSG_RESULT(result, zbx_dsprintf(NULL,
					"Invalid regular expression in second parameter: %s", error));
			return FAIL;
		}
	}

	if (NULL != regex_excl_str && '\0' != *regex_excl_str)
	{
		if (SUCCEED != zbx_regexp_compile(regex_excl_str, regex_excl, &error))
		{
			SET_MSG_RESULT(result, zbx_dsprintf(NULL,
					"Invalid regular expression in third parameter: %s", error));
			return FAIL;
		}
	}

	if (NULL == mode_str || '\0' == *mode_str || 0 == strcmp(mode_str, "apparent"))	/* <mode> default value */
	{
		*mode = SIZE_MODE_APPARENT;
	}
	else if (0 == strcmp(mode_str, "disk"))
	{
		*mode = SIZE_MODE_DISK;
	}
	else
	{
		SET_MSG_RESULT(result, zbx_strdup(NULL, "Invalid fourth parameter."));
		return FAIL;
	}

	if (NULL == max_depth_str || '\0' == *max_depth_str || 0 == strcmp(max_depth_str, "-1"))
	{
		*max_depth = TRAVERSAL_DEPTH_UNLIMITED; /* <max_depth> default value */
	}
	else if (SUCCEED != is_uint31(max_depth_str, max_depth))
	{
		SET_MSG_RESULT(result, zbx_strdup(NULL, "Invalid fifth parameter."));
		return FAIL;
	}

	*dir = zbx_strdup(*dir, dir_param);

	/* remove directory suffix '/' or '\\' (if any, except for paths like "/" or "C:\\") as stat() fails on */
	/* Windows for directories ending with slash */
	if ('\0' != *(*dir + 1) && ':' != *(*dir + strlen(*dir) - 2))
		zbx_rtrim(*dir, "/\\");

#ifdef _WINDOWS
	if (0 != zbx_stat(*dir, status))
#else
	if (0 != lstat(*dir, status))
#endif
	{
		SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Cannot obtain directory information: %s",
				zbx_strerror(errno)));
		zbx_free(*dir);
		return FAIL;
	}

	if (0 == S_ISDIR(status->st_mode))
	{
		SET_MSG_RESULT(result, zbx_strdup(NULL, "First parameter is not a directory."));
		zbx_free(*dir);
		return FAIL;
	}

	return SUCCEED;
}

static void	regex_incl_excl_free(zbx_regexp_t *regex_incl, zbx_regexp_t *regex_excl)
{
	if (NULL != regex_incl)
		zbx_regexp_free(regex_incl);

	if (NULL != regex_excl)
		zbx_regexp_free(regex_excl);
}

static void	list_vector_destroy(zbx_vector_ptr_t *list)
{
	zbx_directory_item_t	*item;

	while (0 < list->values_num)
	{
		item = list->values[--list->values_num];
		zbx_free(item->path);
		zbx_free(item);
	}
	zbx_vector_ptr_destroy(list);
}

static void	descriptors_vector_destroy(zbx_vector_ptr_t *descriptors)
{
	zbx_file_descriptor_t	*file;

	while (0 < descriptors->values_num)
	{
		file = descriptors->values[--descriptors->values_num];
		zbx_free(file);
	}
	zbx_vector_ptr_destroy(descriptors);
}

/******************************************************************************
 *                                                                            *
 * Different approach is used for Windows implementation as Windows is not    *
 * taking size of a directory record in account when calculating size of      *
 * directory contents.                                                        *
 *                                                                            *
 * Current implementation ignores special file types (symlinks, pipes,        *
 * sockets, etc.).                                                            *
 *                                                                            *
 *****************************************************************************/
#ifdef _WINDOWS
#define	DW2UI64(h,l) ((zbx_uint64_t)h << 32 | l)
static int	get_file_info_by_handle(wchar_t *wpath, BY_HANDLE_FILE_INFORMATION *link_info, char **error)
{
	HANDLE	file_handle;

	file_handle = CreateFile(wpath, GENERIC_READ, FILE_SHARE_READ | FILE_SHARE_WRITE, NULL, OPEN_EXISTING,
			FILE_FLAG_BACKUP_SEMANTICS | FILE_FLAG_OPEN_REPARSE_POINT, NULL);

	if (INVALID_HANDLE_VALUE == file_handle)
	{
		*error = zbx_strdup(NULL, strerror_from_system(GetLastError()));
		return FAIL;
	}

	if (0 == GetFileInformationByHandle(file_handle, link_info))
	{
		CloseHandle(file_handle);
		*error = zbx_strdup(NULL, strerror_from_system(GetLastError()));
		return FAIL;
	}

	CloseHandle(file_handle);

	return SUCCEED;
}

static int	link_processed(DWORD attrib, wchar_t *wpath, zbx_vector_ptr_t *descriptors, char *path)
{
	const char			*__function_name = "link_processed";
	BY_HANDLE_FILE_INFORMATION	link_info;
	zbx_file_descriptor_t		*file;
	char 				*error;

	/* Behavior like MS file explorer */
	if (0 != (attrib & FILE_ATTRIBUTE_REPARSE_POINT))
		return SUCCEED;

	if (0 != (attrib & FILE_ATTRIBUTE_DIRECTORY))
		return FAIL;

	if (FAIL == get_file_info_by_handle(wpath, &link_info, &error))
	{
		zabbix_log(LOG_LEVEL_DEBUG, "%s() cannot get file information '%s': %s",
				__function_name, path, error);
		zbx_free(error);
		return SUCCEED;
	}

	/* A file is a hard link only */
	if (1 < link_info.nNumberOfLinks)
	{
		/* skip file if inode was already processed (multiple hardlinks) */
		file = (zbx_file_descriptor_t*)zbx_malloc(NULL, sizeof(zbx_file_descriptor_t));

		file->st_dev = link_info.dwVolumeSerialNumber;
		file->st_ino = DW2UI64(link_info.nFileIndexHigh, link_info.nFileIndexLow);

		if (FAIL != zbx_vector_ptr_search(descriptors, file, compare_descriptors))
		{
			zbx_free(file);
			return SUCCEED;
		}

		zbx_vector_ptr_append(descriptors, file);
	}

	return FAIL;
}

static int	vfs_dir_size(AGENT_REQUEST *request, AGENT_RESULT *result)
{
	const char		*__function_name = "vfs_dir_size";
	char			*dir = NULL;
	int			mode, max_depth, ret = SYSINFO_RET_FAIL;
	zbx_uint64_t		size = 0;
	zbx_vector_ptr_t	list;
	zbx_stat_t		status;
	zbx_regexp_t		*regex_incl = NULL, *regex_excl = NULL;
	zbx_directory_item_t	*item;
	zbx_vector_ptr_t	descriptors;

	if (SUCCEED != prepare_parameters(request, result, &regex_incl, &regex_excl, &mode, &max_depth, &dir, &status))
		goto err1;

	zbx_vector_ptr_create(&descriptors);
	zbx_vector_ptr_create(&list);

	if (SUCCEED == queue_directory(&list, dir, -1, max_depth))	/* put top directory into list */
		dir = NULL;
	else
		goto err2;

	while (0 < list.values_num)
	{
		char		*name, *error = NULL;
		wchar_t		*wpath;
		zbx_uint64_t	cluster_size = 0;
		HANDLE		handle;
		WIN32_FIND_DATA	data;

		item = list.values[--list.values_num];

		name = zbx_dsprintf(NULL, "%s\\*", item->path);

		if (NULL == (wpath = zbx_utf8_to_unicode(name)))
		{
			zbx_free(name);

			if (0 < item->depth)
			{
				zabbix_log(LOG_LEVEL_DEBUG, "%s() cannot convert directory name to UTF-16: '%s'",
						__function_name, item->path);
				goto skip;
			}

			SET_MSG_RESULT(result, zbx_strdup(NULL, "Cannot convert directory name to UTF-16."));
			list.values_num++;
			goto err2;
		}

		zbx_free(name);

		handle = FindFirstFile(wpath, &data);
		zbx_free(wpath);

		if (INVALID_HANDLE_VALUE == handle)
		{
			if (0 < item->depth)
			{
				zabbix_log(LOG_LEVEL_DEBUG, "%s() cannot open directory listing '%s': %s",
						__function_name, item->path, zbx_strerror(errno));
				goto skip;
			}

			SET_MSG_RESULT(result, zbx_strdup(NULL, "Cannot obtain directory listing."));
			list.values_num++;
			goto err2;
		}

		if (SIZE_MODE_DISK == mode && 0 == (cluster_size = get_cluster_size(item->path, &error)))
		{
			SET_MSG_RESULT(result, error);
			list.values_num++;
			goto err2;
		}

		do
		{
			char	*path;

			if (0 == wcscmp(data.cFileName, L".") || 0 == wcscmp(data.cFileName, L".."))
				continue;

			name = zbx_unicode_to_utf8(data.cFileName);
			path = zbx_dsprintf(NULL, "%s/%s", item->path, name);
			wpath = zbx_utf8_to_unicode(path);

			if (SUCCEED == link_processed(data.dwFileAttributes, wpath, &descriptors, path))
			{
				zbx_free(wpath);
				zbx_free(path);
				zbx_free(name);
				continue;
			}

			if (0 == (data.dwFileAttributes & FILE_ATTRIBUTE_DIRECTORY))	/* not a directory */
			{
				if (0 != filename_matches(name, regex_incl, regex_excl))
				{
					DWORD	size_high, size_low;

					/* GetCompressedFileSize gives more accurate result than zbx_stat for */
					/* compressed files */
					size_low = GetCompressedFileSize(wpath, &size_high);

					if (size_low != INVALID_FILE_SIZE || NO_ERROR == GetLastError())
					{
						zbx_uint64_t	file_size, mod;

						file_size = DW2UI64(size_high, size_low);

						if (SIZE_MODE_DISK == mode && 0 != (mod = file_size % cluster_size))
							file_size += cluster_size - mod;

						size += file_size;
					}
				}

				zbx_free(path);
			}
			else if (SUCCEED != queue_directory(&list, path, item->depth, max_depth))
			{
				zbx_free(path);
			}

			zbx_free(wpath);
			zbx_free(name);

		}
		while (0 != FindNextFile(handle, &data));

		if (0 == FindClose(handle))
		{
			zabbix_log(LOG_LEVEL_DEBUG, "%s() cannot close directory listing '%s': %s", __function_name,
					item->path, zbx_strerror(errno));
		}
skip:
		zbx_free(item->path);
		zbx_free(item);
	}

	SET_UI64_RESULT(result, size);
	ret = SYSINFO_RET_OK;
err2:
	list_vector_destroy(&list);
	descriptors_vector_destroy(&descriptors);
err1:
	regex_incl_excl_free(regex_incl, regex_excl);
	zbx_free(dir);

	return ret;
}
#else /* not _WINDOWS */
static int	vfs_dir_size(AGENT_REQUEST *request, AGENT_RESULT *result)
{
	const char		*__function_name = "vfs_dir_size";
	char			*dir = NULL;
	int			mode, max_depth, ret = SYSINFO_RET_FAIL;
	zbx_uint64_t		size = 0;
	zbx_vector_ptr_t	list, descriptors;
	zbx_directory_item_t	*item;
	zbx_stat_t		status;
	zbx_regexp_t		*regex_incl = NULL, *regex_excl = NULL;
	DIR 			*directory;
	struct dirent 		*entry;
	zbx_file_descriptor_t	*file;

	if (SUCCEED != prepare_parameters(request, result, &regex_incl, &regex_excl, &mode, &max_depth, &dir, &status))
		goto err1;

	zbx_vector_ptr_create(&descriptors);
	zbx_vector_ptr_create(&list);

	if (SUCCEED != queue_directory(&list, dir, -1, max_depth))	/* put top directory into list */
		goto err2;

	/* on UNIX count top directory size */

	if (0 != filename_matches(dir, regex_incl, regex_excl))
	{
		if (SIZE_MODE_APPARENT == mode)
			size += (zbx_uint64_t)status.st_size;
		else	/* must be SIZE_MODE_DISK */
			size += (zbx_uint64_t)status.st_blocks * DISK_BLOCK_SIZE;
	}

	dir = NULL;

	while (0 < list.values_num)
	{
		item = list.values[--list.values_num];

		if (NULL == (directory = opendir(item->path)))
		{
			if (0 < item->depth)	/* unreadable subdirectory - skip */
			{
				zabbix_log(LOG_LEVEL_DEBUG, "%s() cannot open directory listing '%s': %s",
						__function_name, item->path, zbx_strerror(errno));
				goto skip;
			}

			/* unreadable top directory - stop */

			SET_MSG_RESULT(result, zbx_strdup(NULL, "Cannot obtain directory listing."));
			list.values_num++;
			goto err2;
		}

		while (NULL != (entry = readdir(directory)))
		{
			char	*path;

			if (0 == strcmp(entry->d_name, ".") || 0 == strcmp(entry->d_name, ".."))
				continue;

			path = zbx_dsprintf(NULL, "%s/%s", item->path, entry->d_name);

			if (0 == lstat(path, &status))
			{
				if ((0 != S_ISREG(status.st_mode) || 0 != S_ISLNK(status.st_mode) ||
						0 != S_ISDIR(status.st_mode)) &&
						0 != filename_matches(entry->d_name, regex_incl, regex_excl))
				{
					if (0 != S_ISREG(status.st_mode) && 1 < status.st_nlink)
					{
						/* skip file if inode was already processed (multiple hardlinks) */
						file = (zbx_file_descriptor_t*)zbx_malloc(NULL,
								sizeof(zbx_file_descriptor_t));

						file->st_dev = status.st_dev;
						file->st_ino = status.st_ino;

						if (FAIL != zbx_vector_ptr_search(&descriptors, file,
								compare_descriptors) )
						{
							zbx_free(file);
							zbx_free(path);
							continue;
						}

						zbx_vector_ptr_append(&descriptors, file);
					}

					if (SIZE_MODE_APPARENT == mode)
						size += (zbx_uint64_t)status.st_size;
					else	/* must be SIZE_MODE_DISK */
						size += (zbx_uint64_t)status.st_blocks * DISK_BLOCK_SIZE;
				}

				if (!(0 != S_ISDIR(status.st_mode) && SUCCEED == queue_directory(&list, path,
						item->depth, max_depth)))
				{
					zbx_free(path);
				}
			}
			else
			{
				zabbix_log(LOG_LEVEL_DEBUG, "%s() cannot process directory entry '%s': %s",
						__function_name, path, zbx_strerror(errno));
				zbx_free(path);
			}
		}

		closedir(directory);
skip:
		zbx_free(item->path);
		zbx_free(item);
	}

	SET_UI64_RESULT(result, size);
	ret = SYSINFO_RET_OK;
err2:
	list_vector_destroy(&list);
	descriptors_vector_destroy(&descriptors);
err1:
	regex_incl_excl_free(regex_incl, regex_excl);
	zbx_free(dir);

	return ret;
}
#endif

int	VFS_DIR_SIZE(AGENT_REQUEST *request, AGENT_RESULT *result)
{
	return zbx_execute_threaded_metric(vfs_dir_size, request, result);
}
