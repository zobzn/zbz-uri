# zbz-uri

URI manipulation library

## Installation

``` bash
composer require zobzn/zbz-uri
```

## Basic Usage

``` php
$uri = Zbz\Uri::get('http://examplex.org/index.php?key1=val1#title')
    ->withScheme('https')
    ->withAuthority('example.com')
    ->withPath('/index.htm')
    ->withQuery('key2=val2')
    ->withFragment('content');

var_export(array(
    (string) $uri,
    $uri->getScheme(),
    $uri->getAuthority(),
    $uri->getPath(),
    $uri->getQuery(),
    $uri->getFragment(),
));
```
