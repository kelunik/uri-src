<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/url/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/url/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.url
 */
namespace League\Uri\Uri;

use InvalidArgumentException;
use League\Uri;
use League\Uri\Interfaces;
use Psr\Http\Message\UriInterface;

/**
 * a Trait to access URI properties methods
 *
 * @package League.url
 * @since   1.0.0
 *
 */
trait Properties
{
    /**
     * A Factory trait to fetch info from Server environment variables
     */
    use ServerInfo;

    /**
     * a Factory to create new URI instances
     */
    use Factory;

    /**
     * partially modifying an URL object
     */
    use PartialModifier;

    /**
     * {@inheritdoc}
     */
    abstract public function __toString();

    /**
     * {@inheritdoc}
     */
    abstract public function getAuthority();

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return static::parse($this->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function hasStandardPort()
    {
        if ($this->scheme->isEmpty()) {
            return false;
        }

        if ($this->port->isEmpty()) {
            return true;
        }

        return $this->scheme->getSchemeRegistry()->getPort($this->scheme)->sameValueAs($this->port);
    }

    /**
     * {@inheritdoc}
     */
    public function isAbsolute()
    {
        return !$this->scheme->isEmpty() && !$this->host->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(UriInterface $url)
    {
        if (!$url instanceof Interfaces\Uri) {
            try {
                $url = static::createFromString($url->__toString(), $this->scheme->getSchemeRegistry());
            } catch (InvalidArgumentException $e) {
                return false;
            }
        }

        return $url->toAscii()->ksortQuery()->__toString() === $this->toAscii()->ksortQuery()->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($url)
    {
        $relative = $this->convertToUrlObject($url);
        if ($relative->isAbsolute()) {
            return $relative->withoutDotSegments();
        }

        if (!$relative->host->isEmpty() && $relative->getAuthority() != $this->getAuthority()) {
            return $relative->withScheme($this->scheme)->withoutDotSegments();
        }

        return $this->resolveRelative($relative)->withoutDotSegments();
    }

    /**
     * Convert to an Url object
     *
     * @param UriInterface|string $url
     *
     * @return static
     */
    protected function convertToUrlObject($url)
    {
        if ($url instanceof Interfaces\Uri) {
            return $url;
        }

        return Uri\Uri::createFromString((string) $url, $this->scheme->getSchemeRegistry());
    }

    /**
     * returns the resolve URL
     *
     * @param Interfaces\Uri $relative the relative URL
     *
     * @return static
     */
    protected function resolveRelative(Interfaces\Uri $relative)
    {
        $newUrl = $this->withProperty('fragment', $relative->fragment->__toString());
        if (!$relative->path->isEmpty()) {
            return $newUrl
                ->withProperty('path', $this->resolvePath($newUrl, $relative)->__toString())
                ->withProperty('query', $relative->query->__toString());
        }

        if (!$relative->query->isEmpty()) {
            return $newUrl->withProperty('query', $relative->query->__toString());
        }

        return $newUrl;
    }

    /**
     * returns the resolve URL components
     *
     * @param Interfaces\Uri $newUrl   the final URL
     * @param Interfaces\Uri $relative the relative URL
     *
     * @return Interfaces\Path
     */
    protected function resolvePath(Interfaces\Uri $newUrl, Interfaces\Uri $relative)
    {
        $path = $relative->path;
        if (!$path->isAbsolute()) {
            $segments = $newUrl->path->toArray();
            array_pop($segments);
            $isAbsolute = Uri\Path::IS_RELATIVE;
            if ($newUrl->path->isEmpty() || $newUrl->path->isAbsolute()) {
                $isAbsolute = Uri\Path::IS_ABSOLUTE;
            }
            $path = Uri\Path::createFromArray(array_merge($segments, $path->toArray()), $isAbsolute);
        }

        return $path;
    }
}
