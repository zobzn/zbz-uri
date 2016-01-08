<?php

namespace Zbz;

class Uri
{
    protected $scheme;
    protected $authority;
    protected $path;
    protected $query;
    protected $fragment;

    /**
     * Конструктор
     *
     * @param string $uri
     */
    public function __construct($uri = '')
    {
        $uri    = (string) $uri;
        $parsed = $this->parse($uri);
        $this->setScheme($parsed['scheme']);
        $this->setAuthority($parsed['authority']);
        $this->setPath($parsed['path']);
        $this->setQuery($parsed['query']);
        $this->setFragment($parsed['fragment']);
    }

    /**
     * Конструктор
     *
     * @param string $uri
     * @param bool   $normalize
     *
     * @return static
     */
    public static function get($uri = '', $normalize = false)
    {
        if ($normalize) {
            return static::get($uri)->normalize();
        } else {
            return new static($uri);
        }
    }

    /**
     * Parsing a URI Reference with a Regular Expression
     *
     * @see http://tools.ietf.org/html/rfc3986#page-50
     *
     * @param string $uri
     *
     * @return array
     */
    public static function parse($uri = null)
    {
        preg_match('/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/', $uri, $match);
        for ($i = count($match); $i <= 9; ++$i) {
            $match[$i] = null;
        }

        return array(
            'scheme'    => $match[2],
            'authority' => $match[4],
            'path'      => $match[5],
            'query'     => $match[7],
            'fragment'  => $match[9],
        );
        // http://www.w3.org/2005/Incubator/wcl/matching.html#RFC3986
        // ^(([^:/?#]+):)?(//((([^/?#]*)@)?([^/?#:]*)(:([^/?#]*))?))?([^?#]*)(\?([^#]*))?(#(.*))?
        // scheme: $2, authority: $4 (userinfo: $6, host:$7, port: $9), path: $10, query: $12, fragment: $14
    }

    /**
     * Merge Paths
     *
     * This function implements the "merge" algorithm from
     * the RFC3986 specification for URIs.
     *
     * @see http://tools.ietf.org/html/rfc3986#page-32
     *
     * @param string $base
     * @param string $relative
     *
     * @return string
     */
    protected static function merge($base, $relative)
    {
        $base = preg_replace('#(.*/)[^/]*#', '\1', $base);

        return $base . $relative;
    }

    /**
     * Filter out "." and ".." segments from a URI's path and return
     * the result.
     *
     * This function implements the "remove_dot_segments" algorithm from
     * the RFC3986 specification for URIs.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-5.2.4
     *
     * @param string $input
     *
     * @return string
     */
    protected static function remove_dot_segments($input)
    {
        $output = '';
        while (strpos($input, './') !== false || strpos($input, '/.') !== false || $input === '.' || $input === '..') {
            // A: If the input buffer begins with a prefix of "../" or "./", then remove that prefix from the input buffer; otherwise,
            if (strpos($input, '../') === 0) {
                $input = substr($input, 3);
            } elseif (strpos($input, './') === 0) {
                $input = substr($input, 2);
                // B: if the input buffer begins with a prefix of "/./" or "/.", where "." is a complete path segment, then replace that prefix with "/" in the input buffer; otherwise,
            } elseif (strpos($input, '/./') === 0) {
                $input = substr_replace($input, '/', 0, 3);
            } elseif ($input === '/.') {
                $input = '/';
                // C: if the input buffer begins with a prefix of "/../" or "/..", where ".." is a complete path segment, then replace that prefix with "/" in the input buffer and remove the last segment and its preceding "/" (if any) from the output buffer; otherwise,
            } elseif (strpos($input, '/../') === 0) {
                $input  = substr_replace($input, '/', 0, 4);
                $output = substr_replace($output, '', strrpos($output, '/'));
            } elseif ($input === '/..') {
                $input  = '/';
                $output = substr_replace($output, '', strrpos($output, '/'));
                // D: if the input buffer consists only of "." or "..", then remove that from the input buffer; otherwise,
            } elseif ($input === '.' || $input === '..') {
                $input = '';
                // E: move the first path segment in the input buffer to the end of the output buffer, including the initial "/" character (if any) and any subsequent characters up to, but not including, the next "/" character or the end of the input buffer
            } elseif (($pos = strpos($input, '/', 1)) !== false) {
                $output .= substr($input, 0, $pos);
                $input = substr_replace($input, '', 0, $pos);
            } else {
                $output .= $input;
                $input = '';
            }
        }

        return $output . $input;
    }

    /**
     * Преобразовать в строку
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Преобразовать в строку
     *
     * @return string
     */
    public function toString()
    {
        return $this->build();
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function withScheme($scheme)
    {
        $uri = clone $this;

        return $uri->setScheme($scheme);
    }

    /**
     * @param string $scheme
     *
     * @return $this
     */
    protected function setScheme($scheme)
    {
        if ($scheme === null || $scheme === '') {
            $this->scheme = null;
        } else {
            $this->scheme = strtolower($scheme);
        }

        return $this;
    }

    public function getAuthority()
    {
        return $this->authority;
    }

    public function withAuthority($authority)
    {
        $uri = clone $this;

        return $uri->setAuthority($authority);
    }

    protected function setAuthority($authority)
    {
        if ($authority === null || $authority === '') {
            $this->authority = null;
        } else {
            $this->authority = $authority;
        }

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function withPath($path)
    {
        $uri = clone $this;

        return $uri->setPath($path);
    }

    protected function setPath($path)
    {
        $path = trim(preg_replace('~[\\\/]+~', '/', $path));
        $path = $path === '' ? null : $path;

        $this->path = $path;

        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function withQuery($query)
    {
        $uri = clone $this;

        return $uri->setQuery($query);
    }

    protected function setQuery($query)
    {
        if ($query === null || $query === '') {
            $this->query = null;
        } else {
            $this->query = $query;
        }

        return $this;
    }

    public function getParameters()
    {
        parse_str($this->getQuery(), $params);

        return $params;
    }

    public function getParameter($key, $default = null)
    {
        $params = $this->getParameters();

        return isset($params[$key]) ? $params[$key] : $default;
    }

    public function withParameter($key, $value)
    {
        $uri = clone $this;
        $uri->setParameter($key, $value);

        return $uri;
    }

    protected function setParameter($key, $value)
    {
        parse_str($this->getQuery(), $params);
        $params[$key] = $value;
        $this->setQuery(http_build_query($params));

        return $this;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function withFragment($fragment)
    {
        $uri = clone $this;

        return $uri->setFragment($fragment);
    }

    protected function setFragment($fragment)
    {
        if ($fragment === null || $fragment === '') {
            $this->fragment = null;
        } else {
            $this->fragment = $fragment;
        }

        return $this;
    }

    /**
     * Transform References
     *
     * This function implements the "transform references" algorithm from
     * the RFC3986 specification for URIs.
     *
     * @see http://tools.ietf.org/html/rfc3986#page-31
     *
     * @param string $relative
     * @param bool   $strict
     *
     * @return static
     */
    public function transformReference($relative, $strict = false)
    {
        $base        = $this;
        $relative    = new static((string) $relative);
        $transformed = new static();

        if (!$strict && $relative->getScheme() == $this->getScheme()) {
            $relative->setScheme(null);
        }

        if ($relative->getScheme() !== null) {
            $transformed->setScheme($relative->getScheme());
            $transformed->setAuthority($relative->getAuthority());
            $transformed->setPath(static::remove_dot_segments($relative->getPath()));
            $transformed->setQuery($relative->getQuery());
        } else {
            if ($relative->getAuthority() !== null) {
                $transformed->setAuthority($relative->getAuthority());
                $transformed->setPath(static::remove_dot_segments($relative->getPath()));
                $transformed->setQuery($relative->getQuery());
            } else {
                if ($relative->getPath() == '') {
                    $transformed->setPath($base->getPath());
                    if ($relative->getQuery() !== null) {
                        $transformed->setQuery($relative->getQuery());
                    } else {
                        $transformed->setQuery($base->getQuery());
                    }
                } else {
                    if ('/' == substr($relative->getPath(), 0, 1)) {
                        $transformed->setPath(static::remove_dot_segments($relative->getPath()));
                    } else {
                        $transformed->setPath(static::merge($base->getPath(), $relative->getPath()));
                        $transformed->setPath(static::remove_dot_segments($transformed->getPath()));
                    }
                    $transformed->setQuery($relative->getQuery());
                }
                $transformed->setAuthority($base->getAuthority());
            }
            $transformed->setScheme($base->getScheme());
        }
        $transformed->setFragment($relative->getFragment());

        return $transformed;
    }

    /**
     * URI Component Recomposition
     *
     * @see http://tools.ietf.org/html/rfc3986#section-5.3
     *
     * @return string
     */
    protected function build()
    {
        $uri = '';

        if (!empty($this->scheme)) {
            $uri .= sprintf('%s:', $this->scheme);
        }
        if (!empty($this->authority)) {
            $uri .= sprintf('//%s', $this->authority);
        }
        if (!empty($this->path)) {
            $uri .= sprintf('%s', $this->path);
        }
        if (!empty($this->query)) {
            $uri .= sprintf('?%s', $this->query);
        }
        if (!empty($this->fragment)) {
            $uri .= sprintf('#%s', $this->fragment);
        }

        return $uri;
    }

    /**
     * Нормализовать путь
     * Раскрывает папки вида '.' и '..'
     *
     * @return static
     */
    protected function normalize()
    {
        $path = $this->getPath();
        $path = preg_replace('#[\\\/]+#', '/', $path);
        $path = static::remove_dot_segments($path);

        return $this->withPath($path);
    }
}
