<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Request;

$checks = 0;
$failures = [];

function check(bool $condition, string $message): void
{
    global $checks, $failures;

    $checks++;

    if ($condition === false) {
        $failures[] = $message;
    }
}

$originalGet = $_GET;
$originalPost = $_POST;
$originalFiles = $_FILES;
$originalCookie = $_COOKIE;
$originalServer = $_SERVER;

try {
    $_GET = ['page' => '2', 'search' => 'audio'];
    $_POST = ['name' => 'Narrador', 'search' => 'post-value'];
    $_FILES = ['upload' => ['name' => 'voice.wav']];
    $_COOKIE = ['session' => 'abc'];
    $_SERVER = [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI' => '/projects/123?tab=audio',
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_USER_AGENT' => 'NarradorTest/1.0',
        'HTTP_HOST' => 'localhost',
        'HTTP_ACCEPT' => 'text/html',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTPS' => 'on',
    ];

    $request = Request::capture();

    check($request instanceof Request, 'Request::capture() must return Request.');
    check($request->method() === 'POST', 'method() must return POST.');
    check($request->isPost() === true, 'isPost() must be true.');
    check($request->isGet() === false, 'isGet() must be false.');
    check($request->query('page') === '2', 'query() must return GET value.');
    check($request->query('missing', 'default') === 'default', 'query() must return default.');
    check($request->post('name') === 'Narrador', 'post() must return POST value.');
    check($request->post('missing', 'default') === 'default', 'post() must return default.');
    check($request->input('search') === 'post-value', 'input() must prefer POST.');
    check($request->input('page') === '2', 'input() must fallback to query.');
    check($request->file('upload')['name'] === 'voice.wav', 'file() must return uploaded file data.');
    check($request->file('missing') === null, 'file() must return null for missing file.');
    check($request->cookie('session') === 'abc', 'cookie() must return cookie value.');
    check($request->header('Accept') === 'text/html', 'header() must normalize header name.');
    check($request->server('REMOTE_ADDR') === '127.0.0.1', 'server() must return server value.');
    check($request->ip() === '127.0.0.1', 'ip() must return remote address.');
    check($request->userAgent() === 'NarradorTest/1.0', 'userAgent() must return user agent.');
    check($request->uri() === '/projects/123?tab=audio', 'uri() must return full URI.');
    check($request->path() === '/projects/123', 'path() must remove query string.');
    check($request->host() === 'localhost', 'host() must return host.');
    check($request->scheme() === 'https', 'scheme() must return https.');
    check($request->isSecure() === true, 'isSecure() must detect HTTPS.');
    check($request->ajax() === true, 'ajax() must detect XMLHttpRequest.');
    check($request->all() === ['page' => '2', 'search' => 'post-value', 'name' => 'Narrador'], 'all() must merge GET and POST.');
    check($request->only(['name', 'page']) === ['name' => 'Narrador', 'page' => '2'], 'only() must filter selected keys.');
    check($request->except(['search']) === ['page' => '2', 'name' => 'Narrador'], 'except() must remove selected keys.');

    check($request->route('uuid') === null, 'route() must return null for missing route parameter.');
    check($request->route('uuid', 'fallback') === 'fallback', 'route() must return default for missing key.');
    check($request->routeParameters() === [], 'routeParameters() must be empty by default.');

    $requestWithRoute = $request->withRouteParameters(['uuid' => 'abc-123', 'section' => 'intro']);

    check($requestWithRoute instanceof Request, 'withRouteParameters() must return Request.');
    check($requestWithRoute !== $request, 'withRouteParameters() must return a new instance.');
    check($request->routeParameters() === [], 'Original Request must remain without route parameters.');
    check($requestWithRoute->route('uuid') === 'abc-123', 'route() must return existing route parameter.');
    check($requestWithRoute->route('missing', 'fallback') === 'fallback', 'route() must return default on enriched instance.');
    check(
        $requestWithRoute->routeParameters() === ['uuid' => 'abc-123', 'section' => 'intro'],
        'routeParameters() must return all route parameters.'
    );
    check($requestWithRoute->query('page') === '2', 'withRouteParameters() must preserve query data.');
    check($requestWithRoute->post('name') === 'Narrador', 'withRouteParameters() must preserve POST data.');
    check($requestWithRoute->cookie('session') === 'abc', 'withRouteParameters() must preserve cookies.');
    check($requestWithRoute->header('Accept') === 'text/html', 'withRouteParameters() must preserve headers.');

    $_GET['page'] = '999';
    $_POST['name'] = 'Changed';
    $_SERVER['REQUEST_URI'] = '/changed';

    check($request->query('page') === '2', 'Request must not read changed GET superglobal after capture.');
    check($request->post('name') === 'Narrador', 'Request must not read changed POST superglobal after capture.');
    check($request->path() === '/projects/123', 'Request must not read changed SERVER superglobal after capture.');

    check(method_exists($request, 'setRouteParameters') === false, 'Request must not expose setRouteParameters().');
    check(method_exists($request, 'set') === false, 'Request must not expose generic set().');
    check(method_exists($request, 'get') === false, 'Request must not expose get().');
} finally {
    $_GET = $originalGet;
    $_POST = $originalPost;
    $_FILES = $originalFiles;
    $_COOKIE = $originalCookie;
    $_SERVER = $originalServer;
}

if ($failures !== []) {
    echo 'Request tests failed.' . PHP_EOL;

    foreach ($failures as $failure) {
        echo '- ' . $failure . PHP_EOL;
    }

    echo 'Checks: ' . $checks . PHP_EOL;
    return 1;
}

echo 'Request tests passed. Checks: ' . $checks . PHP_EOL;