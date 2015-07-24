<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/uri/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.uri
 */
namespace League\Uri\Uri;

use InvalidArgumentException;
use League\Uri;
use ReflectionClass;

/**
 * A Factory Trait to help return a new League\Uri\Interfaces\Uri
 * implemented object
 *
 * @package League.uri
 * @since   4.0.0
 */
trait Factory
{
    /**
     * Create a new instance from a string
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException If the URL can not be parsed
     *
     * @return static
     */
    public static function createFromString($uri = '')
    {
        return static::createFromComponents(static::parse($uri));
    }

    /**
     * Create a new instance from an array returned by
     * PHP parse_url function
     *
     * @param array $components
     *
     * @return Uri\Uri
     */
    public static function createFromComponents(array $components)
    {
        $components = static::formatComponents($components);

        return (new ReflectionClass(get_called_class()))->newInstance(
            new Uri\Scheme($components['scheme']),
            new Uri\UserInfo($components['user'], $components['pass']),
            new Uri\Host($components['host']),
            new Uri\Port($components['port']),
            new Uri\Path($components['path']),
            new Uri\Query($components['query']),
            new Uri\Fragment($components['fragment'])
        );
    }

    /**
     * Format the components to works with all the constructors
     *
     * @param array $components
     *
     * @return array
     */
    protected static function formatComponents(array $components)
    {
        foreach ($components as $name => $value) {
            $components[$name] = (null === $value && 'port' != $name) ? '' : $value;
        }

        return array_merge([
            'scheme' => '', 'user'     => '',
            'pass'   => '', 'host'     => '',
            'port'   => null, 'path'   => '',
            'query'  => '', 'fragment' => '',
        ], $components);
    }

    /**
     * Parse a string as an URI
     *
     * Parse an URI string using PHP parse_url while applying bug fixes
     * and taking into account UTF-8
     *
     * Taken from php.net manual comments:
     *
     * @see http://php.net/manual/en/function.parse-url.php#114817
     *
     * @param string $uri The URL to parse
     *
     * @throws InvalidArgumentException if the URI can not be parsed
     *
     * @return array
     */
    public static function parse($uri)
    {
        $pattern = '%([a-zA-Z][a-zA-Z0-9+\-.]*)?(:?//)?([^:/@?&=#\[\]]+)%usD';
        $enc_uri = preg_replace_callback($pattern, function ($matches) {
            return sprintf('%s%s%s', $matches[1], $matches[2], rawurlencode($matches[3]));
        }, (string) $uri);

        $components = @parse_url($enc_uri);
        if (is_array($components)) {
            return static::formatParsedComponents($components);
        }

        $components = @parse_url(static::fixUrlScheme($enc_uri));
        if (is_array($components)) {
            unset($components['scheme']);

            return static::formatParsedComponents($components);
        }

        throw new InvalidArgumentException(sprintf('The given URI: `%s` could not be parse', (string) $uri));
    }

    /**
     * Format and Decode UTF-8 components
     *
     * @param array $components
     *
     * @return array
     */
    protected static function formatParsedComponents(array $components)
    {
        $components = array_merge([
            'scheme' => null, 'user'     => null,
            'pass'   => null, 'host'     => null,
            'port'   => null, 'path'     => null,
            'query'  => null, 'fragment' => null,
        ], array_map('rawurldecode', $components));

        if (null !== $components['port']) {
            $components['port'] = (int) $components['port'];
        }

        return $components;
    }

    /**
     * bug fix for unpatched PHP version
     *
     * in the following versions
     *    - PHP 5.4.7 => 5.5.24
     *    - PHP 5.6.0 => 5.6.8
     *    - HHVM all versions
     *
     * We must prepend a temporary missing scheme to allow
     * parsing with parse_url function
     *
     * @see https://bugs.php.net/bug.php?id=68917
     *
     * @param string $uri The URL to parse
     *
     * @return array
     */
    protected static function fixUrlScheme($uri)
    {
        static $is_bugged;

        if (is_null($is_bugged)) {
            $is_bugged = !is_array(@parse_url('//a:1'));
        }

        if (!$is_bugged || strpos($uri, '/') !== 0) {
            throw new InvalidArgumentException(sprintf('The given URI: `%s` could not be parse', $url));
        }

        return 'php-bugfix-scheme:' . $uri;
    }
}