<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Uri;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface as Psr7UriInterface;

/**
 * @group modifier
 * @coversDefaultClass \League\Uri\BaseUri
 */
final class BaseUriTest extends TestCase
{
    private const BASE_URI = 'http://a/b/c/d;p?q';

    /**
     * @dataProvider resolveProvider
     */
    public function testCreateResolve(string $baseUri, string $uri, string $expected): void
    {
        self::assertSame($expected, (string) BaseUri::new($baseUri)->resolve($uri));
    }

    public static function resolveProvider(): array
    {
        return [
            'base uri'                => [self::BASE_URI, '',              self::BASE_URI],
            'scheme'                  => [self::BASE_URI, 'http://d/e/f',  'http://d/e/f'],
            'path 1'                  => [self::BASE_URI, 'g',             'http://a/b/c/g'],
            'path 2'                  => [self::BASE_URI, './g',           'http://a/b/c/g'],
            'path 3'                  => [self::BASE_URI, 'g/',            'http://a/b/c/g/'],
            'path 4'                  => [self::BASE_URI, '/g',            'http://a/g'],
            'authority'               => [self::BASE_URI, '//g',           'http://g'],
            'query'                   => [self::BASE_URI, '?y',            'http://a/b/c/d;p?y'],
            'path + query'            => [self::BASE_URI, 'g?y',           'http://a/b/c/g?y'],
            'fragment'                => [self::BASE_URI, '#s',            'http://a/b/c/d;p?q#s'],
            'path + fragment'         => [self::BASE_URI, 'g#s',           'http://a/b/c/g#s'],
            'path + query + fragment' => [self::BASE_URI, 'g?y#s',         'http://a/b/c/g?y#s'],
            'single dot 1'            => [self::BASE_URI, '.',             'http://a/b/c/'],
            'single dot 2'            => [self::BASE_URI, './',            'http://a/b/c/'],
            'single dot 3'            => [self::BASE_URI, './g/.',         'http://a/b/c/g/'],
            'single dot 4'            => [self::BASE_URI, 'g/./h',         'http://a/b/c/g/h'],
            'double dot 1'            => [self::BASE_URI, '..',            'http://a/b/'],
            'double dot 2'            => [self::BASE_URI, '../',           'http://a/b/'],
            'double dot 3'            => [self::BASE_URI, '../g',          'http://a/b/g'],
            'double dot 4'            => [self::BASE_URI, '../..',         'http://a/'],
            'double dot 5'            => [self::BASE_URI, '../../',        'http://a/'],
            'double dot 6'            => [self::BASE_URI, '../../g',       'http://a/g'],
            'double dot 7'            => [self::BASE_URI, '../../../g',    'http://a/g'],
            'double dot 8'            => [self::BASE_URI, '../../../../g', 'http://a/g'],
            'double dot 9'            => [self::BASE_URI, 'g/../h' ,       'http://a/b/c/h'],
            'mulitple slashes'        => [self::BASE_URI, 'foo////g',      'http://a/b/c/foo////g'],
            'complex path 1'          => [self::BASE_URI, ';x',            'http://a/b/c/;x'],
            'complex path 2'          => [self::BASE_URI, 'g;x',           'http://a/b/c/g;x'],
            'complex path 3'          => [self::BASE_URI, 'g;x?y#s',       'http://a/b/c/g;x?y#s'],
            'complex path 4'          => [self::BASE_URI, 'g;x=1/./y',     'http://a/b/c/g;x=1/y'],
            'complex path 5'          => [self::BASE_URI, 'g;x=1/../y',    'http://a/b/c/y'],
            'dot segments presence 1' => [self::BASE_URI, '/./g',          'http://a/g'],
            'dot segments presence 2' => [self::BASE_URI, '/../g',         'http://a/g'],
            'dot segments presence 3' => [self::BASE_URI, 'g.',            'http://a/b/c/g.'],
            'dot segments presence 4' => [self::BASE_URI, '.g',            'http://a/b/c/.g'],
            'dot segments presence 5' => [self::BASE_URI, 'g..',           'http://a/b/c/g..'],
            'dot segments presence 6' => [self::BASE_URI, '..g',           'http://a/b/c/..g'],
            'origin uri without path' => ['http://h:b@a', 'b/../y',        'http://h:b@a/y'],
            'not same origin'         => [self::BASE_URI, 'ftp://a/b/c/d', 'ftp://a/b/c/d'],
        ];
    }

    public function testRelativizeIsNotMade(): void
    {
        $uri = '//path#fragment';

        self::assertEquals($uri, (string) BaseUri::new('https://example.com/path')->relativize($uri));
    }

    /**
     * @dataProvider relativizeProvider
     */
    public function testRelativize(string $uri, string $resolved, string $expected): void
    {
        self::assertSame(
            $expected,
            (string) BaseUri::new(Http::new($uri))->relativize($resolved)
        );
    }

    public static function relativizeProvider(): array
    {
        return [
            'different scheme'        => [self::BASE_URI,       'https://a/b/c/d;p?q',   'https://a/b/c/d;p?q'],
            'different authority'     => [self::BASE_URI,       'https://g/b/c/d;p?q',   'https://g/b/c/d;p?q'],
            'empty uri'               => [self::BASE_URI,       '',                      ''],
            'same uri'                => [self::BASE_URI,       self::BASE_URI,          ''],
            'same path'               => [self::BASE_URI,       'http://a/b/c/d;p',      'd;p'],
            'parent path 1'           => [self::BASE_URI,       'http://a/b/c/',         './'],
            'parent path 2'           => [self::BASE_URI,       'http://a/b/',           '../'],
            'parent path 3'           => [self::BASE_URI,       'http://a/',             '../../'],
            'parent path 4'           => [self::BASE_URI,       'http://a',              '../../'],
            'sibling path 1'          => [self::BASE_URI,       'http://a/b/c/g',        'g'],
            'sibling path 2'          => [self::BASE_URI,       'http://a/b/c/g/h',      'g/h'],
            'sibling path 3'          => [self::BASE_URI,       'http://a/b/g',          '../g'],
            'sibling path 4'          => [self::BASE_URI,       'http://a/g',            '../../g'],
            'query'                   => [self::BASE_URI,       'http://a/b/c/d;p?y',    '?y'],
            'fragment'                => [self::BASE_URI,       'http://a/b/c/d;p?q#s',  '#s'],
            'path + query'            => [self::BASE_URI,       'http://a/b/c/g?y',      'g?y'],
            'path + fragment'         => [self::BASE_URI,       'http://a/b/c/g#s',      'g#s'],
            'path + query + fragment' => [self::BASE_URI,       'http://a/b/c/g?y#s',    'g?y#s'],
            'empty segments'          => [self::BASE_URI,       'http://a/b/c/foo////g', 'foo////g'],
            'empty segments 1'        => [self::BASE_URI,       'http://a/b////c/foo/g', '..////c/foo/g'],
            'relative single dot 1'   => [self::BASE_URI,       '.',                     '.'],
            'relative single dot 2'   => [self::BASE_URI,       './',                    './'],
            'relative double dot 1'   => [self::BASE_URI,       '..',                    '..'],
            'relative double dot 2'   => [self::BASE_URI,       '../',                   '../'],
            'path with colon 1'       => ['http://a/',          'http://a/d:p',          './d:p'],
            'path with colon 2'       => [self::BASE_URI,       'http://a/b/c/g/d:p',    'g/d:p'],
            'scheme + auth 1'         => ['http://a',           'http://a?q#s',          '?q#s'],
            'scheme + auth 2'         => ['http://a/',          'http://a?q#s',          '/?q#s'],
            '2 relative paths 1'      => ['a/b',                '../..',                 '../..'],
            '2 relative paths 2'      => ['a/b',                './.',                   './.'],
            '2 relative paths 3'      => ['a/b',                '../c',                  '../c'],
            '2 relative paths 4'      => ['a/b',                'c/..',                  'c/..'],
            '2 relative paths 5'      => ['a/b',                'c/.',                   'c/.'],
            'baseUri with query'      => ['/a/b/?q',            '/a/b/#h',               './#h'],
            'targetUri with fragment' => ['/',                  '/#h',                   '#h'],
            'same document'           => ['/',                  '/',                     ''],
            'same URI normalized'     => ['http://a',           'http://a/',             ''],
        ];
    }

    /**
     * @dataProvider relativizeAndResolveProvider
     */
    public function testRelativizeAndResolve(
        string $baseUri,
        string $uri,
        string $expectedRelativize
    ): void {
        self::assertSame(
            $expectedRelativize,
            (string) BaseUri::new($baseUri)->relativize($uri)
        );
    }

    public static function relativizeAndResolveProvider(): array
    {
        return [
            'empty path'            => [self::BASE_URI, 'http://a/', '../../',   'http://a/'],
            'absolute empty path'   => [self::BASE_URI, 'http://a',  '../../',   'http://a/'],
            'relative single dot 1' => [self::BASE_URI, '.',         '.',        'http://a/b/c/'],
            'relative single dot 2' => [self::BASE_URI, './',        './',       'http://a/b/c/'],
            'relative double dot 1' => [self::BASE_URI, '..',        '..',       'http://a/b/'],
            'relative double dot 2' => [self::BASE_URI, '../',       '../',      'http://a/b/'],
            '2 relative paths 1'    => ['a/b',          '../..',     '../..',    '/'],
            '2 relative paths 2'    => ['a/b',          './.',       './.',      'a/'],
            '2 relative paths 3'    => ['a/b',          '../c',      '../c',     'c'],
            '2 relative paths 4'    => ['a/b',          'c/..',      'c/..',     'a/'],
            '2 relative paths 5'    => ['a/b',          'c/.',       'c/.',      'a/c/'],
            'path with colon'       => ['http://a/',    'http://a/d:p', './d:p', 'http://a/d:p'],
        ];
    }

    /**
     * @dataProvider uriProvider
     *
     * @param array<bool> $infos
     */
    public function testInfo(
        Psr7UriInterface|Uri $uri,
        Psr7UriInterface|Uri|null $base_uri,
        array $infos
    ): void {
        if (null !== $base_uri) {
            self::assertSame($infos['same_document'], BaseUri::new($base_uri)->isSameDocument($uri));
        }
        self::assertSame($infos['relative_path'], BaseUri::new($uri)->isRelativePath());
        self::assertSame($infos['absolute_path'], BaseUri::new($uri)->isAbsolutePath());
        self::assertSame($infos['absolute_uri'], BaseUri::new($uri)->isAbsolute());
        self::assertSame($infos['network_path'], BaseUri::new($uri)->isNetworkPath());
    }

    public static function uriProvider(): array
    {
        return [
            'absolute uri' => [
                'uri' => Http::new('http://a/p?q#f'),
                'base_uri' => null,
                'infos' => [
                    'absolute_uri' => true,
                    'network_path' => false,
                    'absolute_path' => false,
                    'relative_path' => false,
                    'same_document' => false,
                ],
            ],
            'network relative uri' => [
                'uri' => Http::new('//스타벅스코리아.com/p?q#f'),
                'base_uri' => Http::new('//xn--oy2b35ckwhba574atvuzkc.com/p?q#z'),
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => true,
                    'absolute_path' => false,
                    'relative_path' => false,
                    'same_document' => true,
                ],
            ],
            'path relative uri with non empty path' => [
                'uri' => Http::new('p?q#f'),
                'base_uri' => null,
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => false,
                    'absolute_path' => false,
                    'relative_path' => true,
                    'same_document' => false,
                ],
            ],
            'path relative uri with empty' => [
                'uri' => Http::new('?q#f'),
                'base_uri' => null,
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => false,
                    'absolute_path' => false,
                    'relative_path' => true,
                    'same_document' => false,
                ],
            ],
        ];
    }

    public function testIsFunctionsThrowsTypeError(): void
    {
        self::assertTrue(BaseUri::new('http://example.com')->isAbsolute());
        self::assertFalse(BaseUri::new('http://example.com')->isNetworkPath());
        self::assertTrue(BaseUri::new('/example.com')->isAbsolutePath());
        self::assertTrue(BaseUri::new('example.com#foobar')->isRelativePath());
    }

    /**
     * @dataProvider sameValueAsProvider
     */
    public function testSameValueAs(Psr7UriInterface|Uri $uri1, Psr7UriInterface|Uri $uri2, bool $expected): void
    {
        self::assertSame($expected, BaseUri::new($uri2)->isSameDocument($uri1));
    }

    public static function sameValueAsProvider(): array
    {
        return [
            '2 disctincts URIs' => [
                Http::new('http://example.com'),
                Uri::new('ftp://example.com'),
                false,
            ],
            '2 identical URIs' => [
                Http::new('http://example.com'),
                Http::new('http://example.com'),
                true,
            ],
            '2 identical URIs after removing dot segment' => [
                Http::new('http://example.org/~foo/'),
                Http::new('http://example.ORG/bar/./../~foo/'),
                true,
            ],
            '2 distincts relative URIs' => [
                Http::new('~foo/'),
                Http::new('../~foo/'),
                false,
            ],
            '2 identical relative URIs' => [
                Http::new('../%7efoo/'),
                Http::new('../~foo/'),
                true,
            ],
            '2 identical URIs after normalization (1)' => [
                Http::new('HtTp://مثال.إختبار:80/%7efoo/%7efoo/'),
                Http::new('http://xn--mgbh0fb.xn--kgbechtv/%7Efoo/~foo/'),
                true,
            ],
            '2 identical URIs after normalization (2)' => [
                Http::new('http://www.example.com'),
                Http::new('http://www.example.com/'),
                true,
            ],
            '2 identical URIs after normalization (3)' => [
                Http::new('http://www.example.com'),
                Http::new('http://www.example.com:/'),
                true,
            ],
            '2 identical URIs after normalization (4)' => [
                Http::new('http://www.example.com'),
                Http::new('http://www.example.com:80/'),
                true,
            ],
        ];
    }

    /**
     * @dataProvider getOriginProvider
     */
    public function testGetOrigin(Psr7UriInterface|Uri $uri, ?string $expectedOrigin): void
    {
        self::assertSame($expectedOrigin, BaseUri::new($uri)->origin()?->__toString());
    }

    public static function getOriginProvider(): array
    {
        return [
            'http uri' => [
                'uri' => Uri::new('https://example.com/path?query#fragment'),
                'expectedOrigin' => 'https://example.com',
            ],
            'http uri with non standard port' => [
                'uri' => Uri::new('https://example.com:81/path?query#fragment'),
                'expectedOrigin' => 'https://example.com:81',
            ],
            'relative uri' => [
                'uri' => Uri::new('//example.com:81/path?query#fragment'),
                'expectedOrigin' => null,
            ],
            'absolute uri with user info' => [
                'uri' => Uri::new('https://user:pass@example.com:81/path?query#fragment'),
                'expectedOrigin' => 'https://example.com:81',
            ],
            'opaque URI' => [
                'uri' => Uri::new('mailto:info@thephpleague.com'),
                'expectedOrigin' => null,
            ],
            'file URI' => [
                'uri' => Uri::new('file:///usr/bin/test'),
                'expectedOrigin' => null,
            ],
            'blob' => [
                'uri' => Uri::new('blob:https://mozilla.org:443/'),
                'expectedOrigin' => 'https://mozilla.org',
            ],
        ];
    }

    /**
     * @dataProvider getCrossOriginExamples
     */
    public function testIsCrossOrigin(string $original, string $modified, bool $expected): void
    {
        self::assertSame($expected, BaseUri::new($original)->isCrossOrigin($modified));
    }

    /**
     * @return array<string, array{0:string, 1:string, 2:bool}>
     */
    public static function getCrossOriginExamples(): array
    {
        return [
            'different path' => ['http://example.com/123', 'http://example.com/', false],
            'same port with default value (1)' => ['https://example.com/123', 'https://example.com:443/', false],
            'same port with default value (2)' => ['ws://example.com:80/123', 'ws://example.com/', false],
            'same explicit port' => ['wss://example.com:443/123', 'wss://example.com:443/', false],
            'same origin with i18n host' => ['https://xn--bb-bjab.be./path', 'https://Bébé.BE./path', false],
            'same origin using a blob' => ['blob:https://mozilla.org:443/', 'https://mozilla.org/123', false],
            'different scheme' => ['https://example.com/123', 'ftp://example.com/', true],
            'different host' => ['ftp://example.com/123', 'ftp://www.example.com/123', true],
            'different port implicit' => ['https://example.com/123', 'https://example.com:81/', true],
            'different port explicit' => ['https://example.com:80/123', 'https://example.com:81/', true],
            'same scheme different port' => ['https://example.com:443/123', 'https://example.com:444/', true],
            'comparing two opaque URI' => ['ldap://ldap.example.net', 'ldap://ldap.example.net', true],
            'comparing a URI with an origin and one with an opaque origin' => ['https://example.com:443/123', 'ldap://ldap.example.net', true],
            'cross origin using a blob' => ['blob:http://mozilla.org:443/', 'https://mozilla.org/123', true],
        ];
    }

    /**
     * @dataProvider resolveProvider
     */
    public function testResolveWithPsr7Implementation(string $baseUri, string $uri, string $expected): void
    {
        $resolvedUri = BaseUri::new(Utils::uriFor($baseUri))->resolve($uri);

        self::assertInstanceOf(\GuzzleHttp\Psr7\Uri::class, $resolvedUri->uri());
        self::assertSame($expected, (string) $resolvedUri);
    }

    /**
     * @dataProvider relativizeProvider
     */
    public function testRelativizeWithPsr7Implementation(string $uri, string $resolved, string $expected): void
    {
        $relativizedUri = BaseUri::new(Utils::uriFor($uri))->relativize($resolved);

        self::assertInstanceOf(\GuzzleHttp\Psr7\Uri::class, $relativizedUri->uri());
        self::assertSame($expected, (string) $relativizedUri);
    }

    public function testItCanBeJsonSerialized(): void
    {
        self::assertSame(
            json_encode(Uri::new('http://example.com')),
            json_encode(BaseUri::new('http://example.com'))
        );
    }
}
