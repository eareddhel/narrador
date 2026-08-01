# Response

## Responsabilidad

Response representa una respuesta HTTP completa. Modela el código de estado, las cabeceras HTTP, el tipo de contenido y el cuerpo de la respuesta.

Response es un objeto del ciclo HTTP. Cada instancia corresponde a una petición concreta y se crea una nueva por cada solicitud.

## Filosofía de diseño

Response se diseña como un objeto con API fluida (fluent interface). Todos los métodos de configuración devuelven `self`, permitiendo encadenamiento de llamadas.

```php
$response = (new Response())
    ->status(200)
    ->header('X-App', 'Narrador')
    ->body('Hola mundo');
```

Response no depende de Request. Es un componente independiente que puede utilizarse en cualquier contexto donde se necesite construir una respuesta HTTP.

## Ciclo de vida

1. El Controller crea una instancia de Response.
2. Configura estado, cabeceras y cuerpo mediante la API fluida.
3. Devuelve el objeto Response al Router.
4. El Router ejecuta `send()` para enviar la respuesta al navegador.

## API pública

| Método | Retorna | Descripción |
|--------|---------|-------------|
| `status(int $status)` | `self` | Define el código HTTP |
| `header(string $name, string $value)` | `self` | Añade o reemplaza una cabecera |
| `body(string $content)` | `self` | Define el cuerpo de la respuesta |
| `json(array $data)` | `self` | Convierte array a JSON y establece Content-Type |
| `redirect(string $url, int $status)` | `self` | Prepara una redirección |
| `download(string $file, ?string $filename)` | `self` | Prepara la descarga de un archivo |
| `send()` | `void` | Envía la respuesta HTTP |
| `getStatus()` | `int` | Obtiene el código de estado |
| `getHeaders()` | `array` | Obtiene las cabeceras |
| `getBody()` | `string` | Obtiene el cuerpo |

## Flujo interno

### json()

```php
public function json(array $data): self
{
    $json = json_encode($data, JSON_THROW_ON_ERROR);

    $this->body = $json;
    $this->headers['Content-Type'] = 'application/json';

    return $this;
}
```

- Utiliza `json_encode()` con `JSON_THROW_ON_ERROR`.
- Lanza excepción si falla la codificación.
- Establece automáticamente `Content-Type: application/json`.

### redirect()

```php
public function redirect(string $url, int $status = 302): self
{
    $this->statusCode = $status;
    $this->headers['Location'] = $url;

    return $this;
}
```

- Establece la cabecera `Location`.
- Actualiza el código HTTP (por defecto 302).
- No envía la respuesta; eso lo hace el Router.

### download()

```php
public function download(string $file, ?string $filename = null): self
{
    $this->statusCode = 200;
    $this->headers['Content-Type'] = 'application/octet-stream';
    $this->headers['Content-Disposition'] = 'attachment; filename="' . ($filename ?? basename($file)) . '"';

    return $this;
}
```

- Prepara la descarga con cabeceras apropiadas.
- No valida existencia del archivo (validación futura).
- Utiliza `basename()` si no se proporciona nombre.

### send()

```php
public function send(): void
{
    http_response_code($this->statusCode);

    foreach ($this->headers as $name => $value) {
        header($name . ': ' . $value);
    }

    echo $this->body;
}
```

- Envía código de estado mediante `http_response_code()`.
- Envía cada cabecera mediante `header()`.
- Imprime el cuerpo con `echo`.
- No finaliza la ejecución con `exit()`.

## Dependencias

Response no tiene dependencias externas. No utiliza:
- Env
- Config
- Request
- Variables globales

## Ejemplos

### Respuesta JSON

```php
$response = (new Response())
    ->status(200)
    ->json([
        'success' => true,
        'message' => 'Proyecto creado',
        'data' => ['id' => 1]
    ]);

$response->send();
```

### Respuesta de error

```php
$response = (new Response())
    ->status(404)
    ->json([
        'success' => false,
        'message' => 'Proyecto no encontrado',
        'data' => null
    ]);

$response->send();
```

### Redirección

```php
$response = (new Response())
    ->redirect('/dashboard');

$response->send();
```

### Descarga de archivo

```php
$response = (new Response())
    ->download('audio.mp3', 'narracion.mp3');

$response->send();
```

### Respuesta simple

```php
$response = (new Response())
    ->status(200)
    ->header('X-Custom', 'value')
    ->body('Hola mundo');

$response->send();
```

## Decisiones arquitectónicas

1. **No estática**: Response es un objeto porque cada petición genera una respuesta diferente.
2. **Sin dependencias**: Response es completamente independiente para mantener bajo acoplamiento.
3. **Fluent interface**: Permite código expresivo y legible.
4. **JSON_THROW_ON_ERROR**: Las excepciones en codificación JSON se propagan al caller.
5. **No envía exit()**: El Router decide cuándo terminar la ejecución.
6. **download() sin validación**: La validación de archivos se implementará en una iteración futura.

## Futuras mejoras

- Validación de existencia de archivos en `download()`.
- Soporte para streaming de archivos grandes.
- Métodos para cookies y sesiones.
- Soporte para rangos HTTP (parcial content).
