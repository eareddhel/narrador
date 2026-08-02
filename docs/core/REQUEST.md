# Request

## Responsabilidad

Request representa una petición HTTP capturada en un momento determinado. Es una fotografía inmutable de los datos de entrada.

## Filosofía de diseño

Request es un objeto inmutable. Una vez creado mediante `Request::capture()`, nunca modifica su estado interno ni vuelve a consultar las superglobales.

La única forma de crear una instancia es mediante el método estático `capture()`:

```php
$request = Request::capture();
```

No existen setters. No existen métodos de validación. No existen métodos de sanitización. La única responsabilidad de Request es representar la petición.

## Ciclo de vida

1. El Router invoca `Request::capture()` como parte de la coordinación del ciclo HTTP.
2. Se capturan una única vez `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE` y `$_SERVER`.
3. El objeto resultante se pasa al Controller.
4. El Controller consulta los datos mediante la API pública.
5. El objeto se descarta al finalizar la petición.

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

El constructor es privado. Solo `capture()` puede crear instancias. Los datos se copian directamente de las superglobales.

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

1. **Inmutabilidad**: Request nunca modifica su estado después de la creación.
2. **Captura única**: Las superglobales se consultan solo una vez en `capture()`.
3. **Constructor privado**: La única forma de crear instancias es mediante `capture()`.
4. **Sin método get()**: Se evita la confusión con el verbo HTTP GET. Se usa `query()` para query string.
5. **API explícita**: Cada método tiene un nombre claro que describe su origen.
6. **Coordinación desde Router**: Request::capture() no pertenece al bootstrap; el Router coordina cuándo capturar la petición.

## Futuras mejoras

- Soporte para contenido JSON en el body de la petición.
- Método para obtener el body completo como string.
- Soporte para parsear formularios complejos.
- Integración con Middleware para transformaciones pre-procesamiento.
