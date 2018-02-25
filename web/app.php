<?php
//umask(0000);

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ClassLoader\ApcClassLoader;

/**
 * @var Composer\Autoload\ClassLoader
 */

//$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
//// Use APC for autoloading to improve performance.
//// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
//// with other applications also using APC.
//$loader = new ApcClassLoader('symfony-prestacms', $loader);
//$loader->register(true);
//require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';
//$kernel = new AppKernel('prod', false);
//$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
//$request = Request::createFromGlobals();
//$response = $kernel->handle($request);
//$response->send();
//$kernel->terminate($request, $response);


$classLoader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$apcLoader = new ApcClassLoader(md5($_SERVER['HTTP_HOST']), $loader);
$loader->unregister();
$apcLoader->register(true);

require_once __DIR__.'/../app/AppCache.php';
$kernel = new AppKernel('prod', false);
$kernel = new AppCache($kernel);
Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

