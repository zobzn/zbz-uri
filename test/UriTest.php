<?php

use Zbz\Uri;

class UriTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorEmpty()
    {
        $uri = Uri::get();

        $this->assertSame(null, $uri->getScheme());
        $this->assertSame(null, $uri->getAuthority());
        $this->assertSame(null, $uri->getPath());
        $this->assertSame(null, $uri->getQuery());
        $this->assertSame(null, $uri->getFragment());
        $this->assertSame('', (string) $uri);
    }

    public function testConstructorWithFullUrlString()
    {
        $uri = Uri::get('https://example.org/some/path/?some[]=value#key1&key2=val2&key3');

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('example.org', $uri->getAuthority());
        $this->assertSame('/some/path/', $uri->getPath());
        $this->assertSame('some[]=value', $uri->getQuery());
        $this->assertSame('key1&key2=val2&key3', $uri->getFragment());
        $this->assertSame('https://example.org/some/path/?some[]=value#key1&key2=val2&key3', (string) $uri);
    }

    public function testToStringWithOnePart()
    {
        $this->assertSame('', (string) Uri::get());
        $this->assertSame('http:', (string) Uri::get()->withScheme('http'));
        $this->assertSame('//example.org', (string) Uri::get()->withAuthority('example.org'));
        $this->assertSame('some', (string) Uri::get()->withPath('some'));
        $this->assertSame('some/', (string) Uri::get()->withPath('some/'));
        $this->assertSame('/some', (string) Uri::get()->withPath('/some'));
        $this->assertSame('/some/', (string) Uri::get()->withPath('/some/'));
        $this->assertSame('/some/other', (string) Uri::get()->withPath('/some/other'));
        $this->assertSame('/index.htm', (string) Uri::get()->withPath('/index.htm'));
        $this->assertSame('?key[]=val', (string) Uri::get()->withQuery('key[]=val'));
        $this->assertSame('??key[]=val', (string) Uri::get()->withQuery('?key[]=val'));
        $this->assertSame('#fragment', (string) Uri::get()->withFragment('fragment'));
        $this->assertSame('##fragment', (string) Uri::get()->withFragment('#fragment'));
    }

    public function testToStringFileScheme()
    {
        $this->assertSame('d:/var/www/index.php', (string) Uri::get('D:\\var\\www\\index.php'));
        $this->assertSame('file://localhost/etc/fstab', (string) Uri::get('file://localhost/etc/fstab'));
        $this->assertSame('file:/etc/fstab', (string) Uri::get('file:///etc/fstab'));
        $this->assertSame('file:/D:/var/www/index.php', (string) Uri::get('file:///D:/var/www/index.php'));
    }

    public function testNormalization()
    {
        $this->assertSame('.', (string) Uri::get()->withPath('.'));
        $this->assertSame('..', (string) Uri::get()->withPath('..'));
        $this->assertSame('some/.', (string) Uri::get()->withPath('some/.'));
        $this->assertSame('some/..', (string) Uri::get()->withPath('some/..'));
        $this->assertSame('some/../other', (string) Uri::get()->withPath('some/../other'));
    }

    public function testMakeEmpty()
    {
        $uri = Uri::get('https://example.org/some/path/?some[]=value#key1&key2=val2&key3')
            ->withScheme(null)
            ->withAuthority(null)
            ->withPath(null)
            ->withQuery(null)
            ->withFragment(null);

        $this->assertSame('', (string) $uri);
    }
}
