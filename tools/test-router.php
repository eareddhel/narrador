<?php

declare(strict_types=1);

$temporaryRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'narrador-router-test-' . getmypid();
$configDir = $temporaryRoot . DIRECTORY_SEPARATOR . 'config';

if (defined('ROOT_PATH') === false) {
    define('ROOT_PATH', $temporaryRoot);
}

require_once __DIR__ . '/../app/Core/Autoloader.php';

$autoloader = new App\Core\Autoloader();
$autoloader->register(dirname(__DIR__) . '/app');

use App\Core\Exceptions\CoreException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

final class RouterTestInvokableObject
{
    public function __invoke(Request $request): Response
    {
        return (new Response())
            ->status(201)
            ->body('object:' . $request->path());
    }
}

final class RouterTestClassController
{
    public function __invoke(Request $request): Response
    {
        return (new Response())
            ->status(202)
            ->body('class:' . $request->path());
    }
}

final class RouterTestResolvedController
{
    public function __construct(private readonly string $prefix)
    {
    }

    public function __invoke(Request $request): Response
    {
        return (new Response())
            ->body($this->prefix . ':' . $request->route('id'));
    }
}

final class RouterTestCoreException extends CoreException
{
}

$checks = 0;
$failures = [];
$originalGet = $_GET;
$originalPost = $_POST;
$originalFiles = $_FILES;
$originalCookie = $_COOKIE;
$originalServer = $_SERVER;

function check(bool $condition, string $message): void
{
    global $checks, $failures;

    $checks++;

    if ($condition === false) {
        $failures[] = $message;
    }
}

function makeRequest(string $method, string $uri): Request
{
    $_GET = [];
    $_POST = [];
    $_FILES = [];
    $_COOKIE = [];
    $_SERVER = [
        'REQUEST_METHOD' => $method,
        'REQUEST_URI' => $uri,
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_HOST' => 'localhost',
    ];

    return Request::capture();
}

function cleanupDirectory(string $path): void
{
    if (is_dir($path) === false) {
        return;
    }

    $items = scandir($path);

    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $fullPath = $path . DIRECTORY_SEPARATOR . $item;

        if (is_dir($fullPath)) {
            cleanupDirectory($fullPath);
            continue;
        }

        unlink($fullPath);
    }

    rmdir($path);
}

try {
    check(class_exists(Router::class), 'Router must autoload.');
    check(class_exists(Request::class), 'Request must autoload.');
    check(class_exists(Response::class), 'Response must autoload.');

    $router = new Router();
    check($router->get('/fluent-get', fn (Request $request): Response => new Response()) === $router, 'get() must be fluent.');
    check($router->post('/fluent-post', fn (Request $request): Response => new Response()) === $router, 'post() must be fluent.');

    $router->get('/exact', fn (Request $request): Response => (new Response())->body('exact'));
    $router->post('/submit', fn (Request $request): Response => (new Response())->status(201)->body('posted'));
    $router->get('/slash', fn (Request $request): Response => (new Response())->body('slash'));
    $router->get('/projects/{uuid}', fn (Request $request): Response => (new Response())->body('uuid:' . $request->route('uuid')));
    $router->get(
        '/projects/{project}/sections/{section}',
        fn (Request $request): Response => (new Response())->body($request->route('project') . ':' . $request->route('section'))
    );
    $router->get('/encoded/{value}', fn (Request $request): Response => (new Response())->body($request->route('value')));

    $receivedArgumentCount = 0;
    $originalRequest = makeRequest('GET', '/projects/original');
    $router->get('/single/{uuid}', function (Request $request) use (&$receivedArgumentCount): Response {
        $receivedArgumentCount = func_num_args();

        return (new Response())->body($request->route('uuid'));
    });

    $response = $router->dispatch(makeRequest('GET', '/exact'));
    check($response->getStatus() === 200, 'Valid route must keep controller status.');
    check($response->getBody() === 'exact', 'GET route must match exact path.');

    $response = $router->dispatch(makeRequest('POST', '/submit'));
    check($response->getStatus() === 201, 'POST route must be registered.');
    check($response->getBody() === 'posted', 'POST route must dispatch.');

    $response = $router->dispatch(makeRequest('GET', '/slash/'));
    check($response->getBody() === 'slash', 'Incoming slash final must be normalized.');

    $response = $router->dispatch(makeRequest('GET', '/projects/abc-123'));
    check($response->getBody() === 'uuid:abc-123', 'Named parameter must be extracted.');

    $response = $router->dispatch(makeRequest('GET', '/projects/proj-1/sections/sec-2'));
    check($response->getBody() === 'proj-1:sec-2', 'Multiple named parameters must be extracted.');

    $response = $router->dispatch(makeRequest('GET', '/encoded/hello%20world'));
    check($response->getBody() === 'hello world', 'Route parameters must be rawurldecode() values.');

    $response = $router->dispatch(makeRequest('GET', '/single/new-value'));
    check($response->getBody() === 'new-value', 'Route parameters must be available through Request.');
    check($originalRequest->routeParameters() === [], 'Original Request must not be modified by routing.');
    check($receivedArgumentCount === 1, 'Controller must receive only one Request argument.');

    $router->get('/callable', fn (Request $request): Response => (new Response())->body('callable'));
    check($router->dispatch(makeRequest('GET', '/callable'))->getBody() === 'callable', 'Callable handler must dispatch.');

    $router->get('/object', new RouterTestInvokableObject());
    $response = $router->dispatch(makeRequest('GET', '/object'));
    check($response->getStatus() === 201, 'Invokable object must dispatch.');
    check($response->getBody() === 'object:/object', 'Invokable object must receive Request.');

    $router->get('/class', RouterTestClassController::class);
    $response = $router->dispatch(makeRequest('GET', '/class'));
    check($response->getStatus() === 202, 'Class-string invokable controller must dispatch.');
    check($response->getBody() === 'class:/class', 'Class-string controller must receive Request.');

    $resolvedClass = null;
    $routerWithResolver = new Router(function (string $class) use (&$resolvedClass): object {
        $resolvedClass = $class;

        return new RouterTestResolvedController('resolved');
    });
    $routerWithResolver->get('/resolved/{id}', RouterTestResolvedController::class);
    $response = $routerWithResolver->dispatch(makeRequest('GET', '/resolved/42'));
    check($resolvedClass === RouterTestResolvedController::class, 'Injected controller resolver must receive class-string.');
    check($response->getBody() === 'resolved:42', 'Injected controller resolver must provide invokable controller.');

    $response = $router->dispatch(makeRequest('GET', '/missing'));
    check($response->getStatus() === 404, 'Missing route must produce 404 Response.');
    check($response->getHeaders()['Content-Type'] === 'text/plain; charset=UTF-8', 'Error Response must use text/plain.');
    check(str_contains($response->getBody(), 'Route not found.'), 'Missing route must use safe 404 message.');

    $response = $router->dispatch(makeRequest('POST', '/exact'));
    check($response->getStatus() === 404, 'Wrong method must produce 404 in this version.');

    $router->get('/core-error', function (Request $request): Response {
        throw new RouterTestCoreException('Core failure with internal path C:\\secret\\file.php');
    });
    $response = $router->dispatch(makeRequest('GET', '/core-error'));
    check($response->getStatus() === 500, 'CoreException must produce 500 Response.');

    $router->get('/unexpected-error', function (Request $request): Response {
        throw new RuntimeException('Unexpected secret DSN mysql://user:pass@localhost/db');
    });
    $response = $router->dispatch(makeRequest('GET', '/unexpected-error'));
    check($response->getStatus() === 500, 'Unexpected Throwable must produce 500 Response.');
    check($response->getBody() === 'Internal server error.', 'Production mode must expose only safe generic message.');
    check(str_contains($response->getBody(), 'mysql://') === false, 'Production mode must not expose secrets.');

    if (is_dir($configDir) === false) {
        mkdir($configDir, 0777, true);
    }

    file_put_contents($configDir . DIRECTORY_SEPARATOR . 'app.php', "<?php\n\nreturn ['debug' => true];\n");
    App\Core\Config::load();

    $response = $router->dispatch(makeRequest('GET', '/unexpected-error'));
    check($response->getStatus() === 500, 'Debug unexpected Throwable must keep 500 status.');
    check(str_contains($response->getBody(), RuntimeException::class), 'Debug mode must include exception class.');
    check(str_contains($response->getBody(), 'Unexpected secret DSN') === true, 'Debug mode must include controlled message.');
    check(str_contains($response->getBody(), '#0') === false, 'Debug mode must not include full stack trace automatically.');

    $router->get('/invalid-return', fn (Request $request): string => 'not a response');
    $response = $router->dispatch(makeRequest('GET', '/invalid-return'));
    check($response->getStatus() === 500, 'Invalid handler return must produce 500 Response.');
    check(str_contains($response->getBody(), UnexpectedValueException::class), 'Debug invalid return must expose diagnostic class.');

    try {
        (new Router())->get('', fn (Request $request): Response => new Response());
        check(false, 'Empty path must be rejected.');
    } catch (InvalidArgumentException) {
        check(true, 'Empty path must be rejected.');
    }

    try {
        (new Router())->get('/bad/{}', fn (Request $request): Response => new Response());
        check(false, 'Empty placeholder must be rejected.');
    } catch (InvalidArgumentException) {
        check(true, 'Empty placeholder must be rejected.');
    }

    try {
        (new Router())->get('/bad/{id}/{id}', fn (Request $request): Response => new Response());
        check(false, 'Repeated placeholder must be rejected.');
    } catch (InvalidArgumentException) {
        check(true, 'Repeated placeholder must be rejected.');
    }

    $dispatchOutputRouter = new Router();
    $dispatchOutputRouter->get('/quiet', fn (Request $request): Response => (new Response())->body('quiet'));
    ob_start();
    $response = $dispatchOutputRouter->dispatch(makeRequest('GET', '/quiet'));
    $output = ob_get_clean();
    check($response->getBody() === 'quiet', 'dispatch() must return Response.');
    check($output === '', 'dispatch() must not send output.');

    $runRouter = new Router();
    $runRouter->get('/run', fn (Request $request): Response => (new Response())->body('run-ok'));
    $_GET = [];
    $_POST = [];
    $_FILES = [];
    $_COOKIE = [];
    $_SERVER = [
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI' => '/run',
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_HOST' => 'localhost',
    ];

    ob_start();
    $runRouter->run();
    $runOutput = ob_get_clean();
    check($runOutput === 'run-ok', 'run() must capture Request and send one Response body once.');
} finally {
    $_GET = $originalGet;
    $_POST = $originalPost;
    $_FILES = $originalFiles;
    $_COOKIE = $originalCookie;
    $_SERVER = $originalServer;
    cleanupDirectory($temporaryRoot);
}

if ($failures !== []) {
    echo 'Router tests failed.' . PHP_EOL;

    foreach ($failures as $failure) {
        echo '- ' . $failure . PHP_EOL;
    }

    echo 'Checks: ' . $checks . PHP_EOL;
    return 1;
}

echo 'Router tests passed. Checks: ' . $checks . PHP_EOL;