# Router

## Responsabilidad

Router es el componente del Core responsable de coordinar el ciclo HTTP de una petición concreta.

Router registra rutas explícitas, captura o recibe un objeto `App\Core\Request`, resuelve método y path, extrae parámetros nombrados, enriquece Request de forma inmutable, invoca un handler, exige un objeto `App\Core\Response`, convierte errores en respuestas seguras y envía la respuesta una sola vez mediante `run()`.

Router no es una fachada estática. Es un objeto con una colección interna de rutas agrupadas por método HTTP.

## Naturaleza mutable

Router es mutable durante su fase de configuración: `get()` y `post()` añaden rutas a su colección interna y devuelven `self` para permitir registro fluido.

Después de configurado, Router coordina peticiones sin usar estado estático, Singleton, Reflection, autodetección, attributes, annotations ni middleware.

## Ciclo de vida

1. Bootstrap inicializa infraestructura global: Autoloader, Env y Config.
2. Se crea una instancia de Router.
3. Se registran rutas explícitas con `get()` y `post()`.
4. `run()` captura la petición mediante `Request::capture()`.
5. `dispatch()` resuelve método HTTP y path.
6. Router extrae parámetros nombrados simples.
7. Router llama a `Request::withRouteParameters()`.
8. Router invoca el Controller o callable con un único argumento Request.
9. El handler devuelve Response.
10. Router captura excepciones en el límite HTTP y las convierte en Response segura.
11. `run()` ejecuta `Response::send()` una única vez.

`dispatch()` no envía salida. `run()` es el único método de Router que envía la respuesta.

## API pública

```php
public function __construct(?callable $controllerResolver = null)
public function get(string $path, callable|string $handler): self
public function post(string $path, callable|string $handler): self
public function dispatch(Request $request): Response
public function run(): void
```

## Registro de rutas

Las rutas se registran de forma explícita:

```php
$router->get('/', DashboardController::class);
$router->get('/projects/{uuid}', ProjectShowController::class);
$router->post('/projects', ProjectStoreController::class);
```

También se aceptan callables y objetos invocables, lo que permite pruebas sin Controllers reales.

Reglas:

- solo se implementan GET y POST;
- toda ruta registrada comienza por `/`;
- `/` permanece `/`;
- las demás rutas no conservan slash final;
- no se aceptan definiciones vacías;
- no se usa query string para el matching;
- una ruta existente bajo otro método produce 404 en esta versión;
- no se implementa 405 Method Not Allowed.

## Matching

Router normaliza tanto rutas registradas como paths entrantes.

Ejemplos:

| Definición | URI entrante | Resultado |
|------------|--------------|-----------|
| `/` | `/` | match |
| `/projects` | `/projects/` | match |
| `/projects/{uuid}` | `/projects/6ab4...` | match |
| `/projects/{uuid}` | `/projects/6ab4...?tab=audio` | match sobre path sin query string |

No se implementan:

- parámetros opcionales;
- comodines;
- regex configurables;
- grupos;
- prefijos;
- nombres de rutas;
- generación inversa de URL.

## Parámetros nombrados

Router soporta parámetros nombrados simples por segmento:

```text
/projects/{uuid}
/projects/{project}/sections/{section}
```

Cada placeholder:

- debe tener nombre no vacío;
- debe tener un nombre válido;
- no puede repetirse dentro de la misma ruta;
- coincide con un único segmento;
- no atraviesa `/`;
- se extrae como string;
- se decodifica con `rawurldecode()`.

Router no entrega parámetros como argumentos independientes. Los incorpora a una nueva instancia de Request:

```php
$request = $request->withRouteParameters($parameters);
```

El Controller consulta los valores desde Request:

```php
$uuid = $request->route('uuid');
$parameters = $request->routeParameters();
```

## Controladores invocables

La convención principal es un Controller invocable por acción:

```php
final class ProjectShowController
{
    public function __invoke(Request $request): Response
    {
        $uuid = $request->route('uuid');

        // Delegar en Services y devolver Response.
    }
}
```

La única firma aprobada es:

```php
public function __invoke(Request $request): Response
```

No se pasan parámetros de ruta como argumentos adicionales.

## Resolución de Controllers

Router admite:

1. Callables.
2. Objetos invocables.
3. Class-strings de Controllers invocables.

Para un class-string:

- si existe un resolver inyectado, Router lo utiliza;
- si no existe resolver, Router instancia con `new $class()`;
- Router verifica que la clase exista;
- Router verifica que la instancia resultante sea invocable.

No hay autowiring, contenedor de dependencias ni inspección de constructores.

## Controller Resolver

El constructor acepta un resolver opcional:

```php
$router = new Router(function (string $class): object {
    return new $class();
});
```

Su objetivo es facilitar pruebas y permitir una futura estrategia de construcción de Controllers con dependencias sin introducir todavía un contenedor.

## dispatch()

```php
$response = $router->dispatch($request);
```

Responsabilidades:

- resolver método HTTP y path;
- encontrar la ruta;
- extraer parámetros nombrados;
- enriquecer Request con `withRouteParameters()`;
- resolver e invocar el handler;
- exigir que el resultado sea Response;
- convertir excepciones en respuestas seguras;
- devolver Response.

`dispatch()` no ejecuta `send()` y no imprime salida.

## run()

```php
$router->run();
```

Responsabilidades:

1. Crear la petición con `Request::capture()`.
2. Ejecutar `dispatch()`.
3. Enviar una única vez mediante `Response::send()`.

`run()` no ejecuta `exit()` ni `die()`.

## Ruta inexistente

Si no existe coincidencia para método y path, Router genera internamente `RouteNotFoundException` y `dispatch()` la convierte en una respuesta HTTP 404.

No se devuelve `false`, `null` ni HTML directo.

## Manejo de excepciones

Router representa el límite HTTP y convierte errores en Response.

| Error | Status |
|-------|--------|
| `RouteNotFoundException` | 404 |
| Otras `CoreException` | 500 |
| Otros `Throwable` | 500 |

Las respuestas de error usan:

```text
Content-Type: text/plain; charset=UTF-8
```

### Producción

Cuando `Config::get('app.debug', false)` es `false`, el cuerpo contiene mensajes seguros y genéricos.

No expone:

- stack trace;
- rutas internas;
- SQL;
- credenciales;
- DSN;
- variables de entorno.

### Debug

Cuando `Config::get('app.debug', false)` es `true`, Router puede incluir diagnóstico controlado:

- clase de excepción;
- mensaje;
- archivo y línea.

No incluye automáticamente el stack trace completo.

No se implementa logging todavía y no se renderizan vistas HTML de error.

## Interacción con Request y Response

Request representa toda la petición HTTP. Router utiliza `withRouteParameters()` para producir una nueva instancia enriquecida con parámetros de ruta sin modificar la original.

Response representa el resultado HTTP. Los handlers deben devolver Response. Router no convierte strings, arrays ni null silenciosamente en Response; un retorno inválido se considera error de programación y se transforma en Response 500 en el límite HTTP.

## Límites actuales

Router no implementa:

- middleware;
- autodetección de Controllers;
- Reflection;
- attributes;
- annotations;
- contenedor de dependencias;
- autowiring;
- sesiones;
- CSRF;
- autenticación;
- generación de URLs;
- logging;
- PUT, PATCH o DELETE;
- 405 Method Not Allowed.

Router tampoco ejecuta SQL, accede a Database, renderiza View directamente ni contiene lógica de negocio.

## Ejemplos

### Registro explícito

```php
$router = new Router();

$router
    ->get('/', DashboardController::class)
    ->get('/projects/{uuid}', ProjectShowController::class)
    ->post('/projects', ProjectStoreController::class);
```

### Dispatch aislado

```php
$request = Request::capture();
$response = $router->dispatch($request);
```

### Ejecución completa

```php
$router->run();
```

## Decisiones arquitectónicas

1. **Router como objeto**: No se usa API estática ni fachada estática.
2. **Rutas explícitas**: No hay autodetección ni escaneo de directorios.
3. **Controllers invocables**: Cada Controller representa una acción.
4. **Request único**: Los Controllers reciben solo Request.
5. **Parámetros inmutables**: Router usa `withRouteParameters()` para enriquecer Request.
6. **dispatch() testeable**: Devuelve Response sin enviarla.
7. **run() como límite de envío**: Captura Request y ejecuta `send()` una vez.
8. **Errores seguros**: Las excepciones se convierten en respuestas controladas.
9. **Sin middleware inicial**: Middleware queda aplazado por YAGNI.
10. **Sin contenedor todavía**: El resolver opcional permite evolución futura sin autowiring.

## Futuras mejoras

- Soporte para PUT, PATCH y DELETE.
- 405 Method Not Allowed.
- Middleware cuando exista una necesidad real.
- Nombres de rutas y generación inversa de URL.
- Grupos y prefijos de rutas.
- Páginas de error HTML especializadas.
- Logging centralizado de errores.
- Integración de `config/routes.php` cuando se conecte el front controller.