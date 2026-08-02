# Request

## Responsabilidad

Request representa una petición HTTP capturada en un momento determinado. Es una fotografía inmutable de los datos de entrada, incluyendo query string, POST, archivos, cookies, cabeceras, server y parámetros de ruta.

## Filosofía de diseño

Request es un objeto inmutable. Una vez creado, nunca modifica su estado interno ni vuelve a consultar las superglobales.

La creación inicial de una instancia se realiza mediante el método estático `capture()`:

```php
$request = Request::capture();
```

Router enriquece la petición con parámetros de ruta mediante `withRouteParameters(array $parameters): self`. Ese método no modifica la instancia original; devuelve una nueva instancia con los parámetros incorporados.

Esta estrategia adopta una filosofía inspirada en PSR-7, sin implementar PSR-7 completo. Request mantiene una API propia, pequeña y coherente con Narrador Studio.

No existen setters públicos. No existirán mutadores como `setRouteParameters()`. No existen métodos de validación. No existen métodos de sanitización. La única responsabilidad de Request es representar la petición.

## Ciclo de vida

1. El Router invoca `Request::capture()` como parte de la coordinación del ciclo HTTP.
2. Se capturan una única vez `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE` y `$_SERVER`.
3. Router resuelve los parámetros nombrados de la ruta.
4. Router llama a `withRouteParameters()` sobre el Request capturado.
5. `withRouteParameters()` devuelve una nueva instancia enriquecida.
6. Router utiliza esa nueva instancia para invocar el Controller.
7. El Controller consulta los datos mediante la API pública.
8. El objeto se descarta al finalizar la petición.

## API pública

### Creación

| Método | Tipo | Descripción |
|--------|------|-------------|
| `capture()` | `static` | Crea una instancia capturando las superglobales |

### Método HTTP

| Método | Retorna | Descripción |
|--------|---------|-------------|
| `method()` | `string` | Retorna el método HTTP (GET, POST, etc.) |
| `isGet()` | `bool` | Verdadero si el método es GET |
| `isPost()` | `bool` | Verdadero si el método es POST |

### Datos de entrada

| Método | Retorna | Descripción |
|--------|---------|-------------|
| `query(string $key, mixed $default)` | `mixed` | Obtiene un parámetro de la query string |
| `post(string $key, mixed $default)` | `mixed` | Obtiene un dato enviado mediante POST |
| `input(string $key, mixed $default)` | `mixed` | Prioriza POST, fallback a query string |
| `file(string $key)` | `mixed` | Obtiene un archivo subido |
| `cookie(string $key, mixed $default)` | `mixed` | Obtiene una cookie |
| `header(string $key, mixed $default)` | `mixed` | Obtiene una cabecera HTTP |
| `server(string $key, mixed $default)` | `mixed` | Obtiene un valor del servidor |

### Parámetros de ruta

| Método | Retorna | Descripción |
|--------|---------|-------------|
| `route(string $key, mixed $default = null)` | `mixed` | Obtiene un parámetro resuelto desde la URI |
| `routeParameters()` | `array` | Retorna todos los parámetros de ruta |
| `withRouteParameters(array $parameters): self` | `self` | Devuelve una nueva instancia con parámetros de ruta |

Los parámetros de ruta representan valores obtenidos desde la URI al resolver una ruta explícita.

Ejemplo:

```text
Ruta: /projects/{uuid}
URI:  /projects/6ab4...
```

Router incorpora el parámetro sin modificar el Request original:

```php
$request = Request::capture();
$requestWithRoute = $request->withRouteParameters(['uuid' => '6ab4...']);
```

El Controller recibe la nueva instancia y obtiene el valor mediante Request:

```php
$uuid = $request->route('uuid');
```

Estos parámetros forman parte de la petición HTTP. No sustituyen `query()` ni `post()`, y son independientes del query string.

### Información de la petición

| Método | Retorna | Descripción |
|--------|---------|-------------|
| `ip()` | `string` | Dirección IP del cliente |
| `userAgent()` | `string` | User-Agent del navegador |
| `uri()` | `string` | URI completa |
| `path()` | `string` | Solo la ruta (sin query string) |
| `host()` | `string` | Host de la petición |
| `scheme()` | `string` | http o https |
| `isSecure()` | `bool` | Verdadero si la conexión es HTTPS |
| `ajax()` | `bool` | Verdadero si es una petición AJAX |

### Recolección de datos

| Método | Retorna | Descripción |
|--------|---------|-------------|
| `all()` | `array` | Combina GET y POST |
| `only(array $keys)` | `array` | Solo los campos indicados |
| `except(array $keys)` | `array` | Todos excepto los indicados |

## Flujo interno

### capture()

```php
public static function capture(): self
{
    return new self(
        $_GET,
        $_POST,
        $_FILES,
        $_COOKIE,
        $_SERVER
    );
}
```

El constructor es privado en la implementación actual. `capture()` crea la instancia inicial y copia directamente los datos de las superglobales.

### withRouteParameters()

```php
public function withRouteParameters(array $parameters): self
```

`withRouteParameters()` es el único mecanismo para añadir parámetros de ruta a Request.

Reglas:

- no modifica la instancia original;
- devuelve una nueva instancia;
- conserva los datos ya capturados de query string, POST, archivos, cookies, headers y server;
- reemplaza el conjunto de parámetros de ruta en la nueva instancia;
- será utilizado por Router después de resolver la ruta;
- no estará pensado para uso desde Controllers, Services, Models ni Views.

`withRouteParameters()` clona la instancia actual, reemplaza los parámetros de ruta en el clon y conserva intacta la instancia original. No habrá mutadores como `setRouteParameters()`.

### header()

```php
public function header(string $key, mixed $default = null): mixed
{
    $normalizedKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));

    return $this->server[$normalizedKey] ?? $default;
}
```

Las cabeceras HTTP se almacenan en `$_SERVER` con el prefijo `HTTP_`. El método normaliza el nombre automáticamente.

### input()

```php
public function input(string $key, mixed $default = null): mixed
{
    if (isset($this->post[$key])) {
        return $this->post[$key];
    }

    return $this->get[$key] ?? $default;
}
```

Prioriza datos POST sobre GET. Útil para formularios que pueden enviar datos por ambos métodos.

## Dependencias

Request no tiene dependencias. No utiliza Env, Config ni ninguna otra clase del Core.

## Ejemplos

### Crear y consultar

```php
$request = Request::capture();

if ($request->isPost()) {
    $nombre = $request->post('nombre');
    $email = $request->input('email');
}
```

### Obtener cabeceras

```php
$request = Request::capture();

$accept = $request->header('Accept');
$userAgent = $request->userAgent();
$ip = $request->ip();
```

### Filtrar datos

```php
$request = Request::capture();

$soloNecesarios = $request->only(['nombre', 'email']);
$sinPassword = $request->except(['password', 'password_confirm']);
```

### Obtener parámetros de ruta

```php
$uuid = $request->route('uuid');
$routeParameters = $request->routeParameters();
```

Los Controllers no reciben parámetros de ruta como argumentos independientes. La firma aprobada continúa siendo:

```php
public function __invoke(Request $request): Response
{
    // ...
}
```

No será válido:

```php
public function __invoke(Request $request, string $uuid): Response
{
    // ...
}
```

### Verificar tipo de petición

```php
$request = Request::capture();

if ($request->ajax()) {
    // Respuesta JSON
}

if ($request->isSecure()) {
    // Forzar HTTPS
}
```

## Decisiones arquitectónicas

1. **Inmutabilidad**: Request no modifica su estado después de su creación.
2. **Captura única**: Las superglobales se consultan solo una vez en `capture()`.
3. **Creación inicial explícita**: `capture()` crea la instancia inicial de la petición.
4. **Evolución inmutable**: `withRouteParameters()` devuelve una nueva instancia enriquecida con parámetros de ruta.
5. **Sin mutadores**: No existirán métodos como `setRouteParameters()`.
6. **Sin método get()**: Se evita la confusión con el verbo HTTP GET. Se usa `query()` para query string.
7. **API explícita**: Cada método tiene un nombre claro que describe su origen.
8. **Coordinación desde Router**: Request::capture() no pertenece al bootstrap; el Router coordina cuándo capturar la petición y cuándo usar la instancia enriquecida.
9. **Parámetros de ruta dentro de Request**: Router resuelve parámetros nombrados y los incorpora usando `withRouteParameters()`.
10. **Controller con un único argumento**: Los Controllers reciben solo Request y consultan parámetros mediante `route()`.
11. **Inspiración PSR-7**: Se adopta una filosofía de métodos `with*` inmutables, sin implementar PSR-7 completo.

## Futuras mejoras

- Soporte para contenido JSON en el body de la petición.
- Método para obtener el body completo como string.
- Soporte para parsear formularios complejos.
- Integración con Middleware para transformaciones pre-procesamiento.