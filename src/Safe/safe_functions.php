<?php

declare(strict_types=1);

/**
 * These functions have been absorbed from "thecodingmachine/safe" to prevent a dependency.
 *
 * See: https://github.com/thecodingmachine/safe
 */

namespace Sabberworm\CSS\Safe;

use Sabberworm\CSS\Safe\Exceptions\DirException;
use Sabberworm\CSS\Safe\Exceptions\FilesystemException;
use Sabberworm\CSS\Safe\Exceptions\IconvException;
use Sabberworm\CSS\Safe\Exceptions\PcreException;

use const PREG_NO_ERROR;

/**
 * Searches subject for a match to the regular expression given in pattern.
 *
 * @param string $pattern The pattern to search for, as a string.
 * @param string $subject The input string.
 * @param null|string[] $matches If matches is provided, then it is filled with the results of search.
 * @param int $flags Can be a combination of flags.
 * @param int $offset Offset from which to start the search (in bytes).
 *
 * @return 0|1 Returns 1 if the pattern matches given subject, 0 if it does not.
 *
 * @throws PcreException
 */
function preg_match(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): int
{
    \error_clear_last();
    $safeResult = \preg_match($pattern, $subject, $matches, $flags, $offset);
    if ($safeResult === false) {
        throw PcreException::createFromPhpError();
    }

    return $safeResult;
}

/**
 * Searches subject for all matches to the regular expression given in pattern
 * and puts them in matches in the order specified by flags.
 *
 * @param string $pattern The pattern to search for, as a string.
 * @param string $subject The input string.
 * @param array|null $matches Array of all matches in multi-dimensional array ordered according to flags.
 * @param int $flags Can be a combination of flags.
 * @param int $offset Offset from which to start the search (in bytes).
 *
 * @return 0|positive-int Returns the number of full pattern matches (which might be zero).
 *
 * @throws PcreException
 */
function preg_match_all(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): int
{
    \error_clear_last();
    $safeResult = \preg_match_all($pattern, $subject, $matches, $flags, $offset);
    if ($safeResult === false) {
        throw PcreException::createFromPhpError();
    }

    return $safeResult;
}

/**
 * Split the given string by a regular expression.
 *
 * @param string $pattern The pattern to search for, as a string.
 * @param string $subject The input string.
 * @param int|null $limit If specified, then only substrings up to limit are returned.
 * @param int $flags Can be any combination of flags.
 *
 * @return list<string> Returns an array containing substrings of subject split along boundaries matched by pattern.
 *
 * @throws PcreException
 */
function preg_split(string $pattern, string $subject, ?int $limit = -1, int $flags = 0): array
{
    \error_clear_last();
    $safeResult = \preg_split($pattern, $subject, $limit, $flags);
    if ($safeResult === false) {
        throw PcreException::createFromPhpError();
    }

    return $safeResult;
}

/**
 * Searches subject for matches to pattern and replaces them with replacement.
 *
 * @param string[]|string $pattern The pattern to search for.
 * @param string[]|string $replacement The string or an array with strings to replace.
 * @param string|array|string[] $subject The string or an array with strings to search and replace.
 * @param int $limit The maximum possible replacements for each pattern. Defaults to -1 (no limit).
 * @param int $count If specified, this variable will be filled with the number of replacements done.
 * @param-out int $count
 *
 * @return string|array|string[] Returns an array if the subject parameter is an array, or a string otherwise.
 *
 * @throws PcreException
 */
function preg_replace($pattern, $replacement, $subject, int $limit = -1, ?int &$count = null)
{
    \error_clear_last();
    $result = \preg_replace($pattern, $replacement, $subject, $limit, $count);
    if (\preg_last_error() !== PREG_NO_ERROR || $result === null) {
        throw PcreException::createFromPhpError();
    }

    return $result;
}

/**
 * Performs a character set conversion on the string from from_encoding to to_encoding.
 *
 * @param string $from_encoding The input charset.
 * @param string $to_encoding The output charset.
 * @param string $string The string to be converted.
 *
 * @return string Returns the converted string.
 *
 * @throws IconvException
 */
function iconv(string $from_encoding, string $to_encoding, string $string): string
{
    \error_clear_last();
    $safeResult = \iconv($from_encoding, $to_encoding, $string);
    if ($safeResult === false) {
        throw IconvException::createFromPhpError();
    }

    return $safeResult;
}

/**
 * This function is similar to file, except that file_get_contents returns the file in a string.
 *
 * @param string $filename Name of the file to read.
 * @param bool $use_include_path Whether to search in the include path.
 * @param resource|null $context A valid context resource created with stream_context_create.
 * @param int $offset The offset where the reading starts on the original stream.
 * @param 0|positive-int $length Maximum length of data read.
 *
 * @return string The function returns the read data.
 *
 * @throws FilesystemException
 */
function file_get_contents(string $filename, bool $use_include_path = false, $context = null, int $offset = 0, ?int $length = null): string
{
    \error_clear_last();
    if ($length !== null) {
        $safeResult = \file_get_contents($filename, $use_include_path, $context, $offset, $length);
    } elseif ($offset !== 0) {
        $safeResult = \file_get_contents($filename, $use_include_path, $context, $offset);
    } elseif ($context !== null) {
        $safeResult = \file_get_contents($filename, $use_include_path, $context);
    } else {
        $safeResult = \file_get_contents($filename, $use_include_path);
    }
    if ($safeResult === false) {
        throw FilesystemException::createFromPhpError();
    }

    return $safeResult;
}

/**
 * Opens up a directory handle to be used in subsequent closedir, readdir, and rewinddir calls.
 *
 * @param string $directory The directory path that is to be opened.
 * @param resource|null $context For a description of the context parameter, refer to the streams section.
 *
 * @return resource Returns a directory handle resource on success.
 *
 * @throws DirException
 */
function opendir(string $directory, $context = null)
{
    \error_clear_last();
    if ($context !== null) {
        $safeResult = \opendir($directory, $context);
    } else {
        $safeResult = \opendir($directory);
    }
    if ($safeResult === false) {
        throw DirException::createFromPhpError();
    }

    return $safeResult;
}
