# View

## Responsabilidad

View es el componente del Core responsable de renderizar plantillas PHP ubicadas dentro del directorio canónico de vistas y convertir el HTML resultante en un objeto `App\Core\Response`.

View no envía contenido al navegador. Su salida siempre es un objeto Response que será enviado posteriormente por Router.

## Filosofía de diseño

View es un objeto, no una clase estática. Cada instancia representa un renderizador configurado para un directorio base de vistas.

El componente se mantiene deliberadamente simple:

- no implementa un motor de plantillas externo;
- no conoce Request ni Router;
- no ejecuta lógica de negocio;
- no modifica archivos de vistas;
- no llama `Response::send()`.

Las plantillas son archivos PHP simples. Deben contener presentación y pequeñas decisiones de visualización, nunca lógica de negocio.

## Ciclo de vida

1. El Router o Controller crea una instancia de View.
2. View resuelve el directorio base desde `Config::get('constants.view_path')` o desde una ruta inyectada.
3. El caller ejecuta `render()`.
4. View valida el nombre de la vista.
5. View renderiza la plantilla mediante output buffering.
6. Si existe layout, View renderiza el layout con `$content`.
7. View devuelve un objeto Response con HTML.
8. Router envía la respuesta en una iteración posterior.

## API pública

### Constructor

```php
public function __construct(?string $viewsPath = null)
```

Si `$viewsPath` es `null`, View utiliza la ruta canónica configurada en:

```php
Config::get('constants.view_path')
```

Si se entrega una ruta explícita, se usa esa ruta. Esto permite probar View con un directorio temporal sin depender de las vistas reales de la aplicación.

### render()

```php
public function render(
    string $view,
    array $data = [],
    ?string $layout = 'layout'
): Response
```

Renderiza una vista y devuelve una respuesta HTML.

Parámetros:

| Parámetro | Descripción |
|-----------|-------------|
| `$view` | Nombre relativo de la vista, sin extensión |
| `$data` | Variables disponibles dentro de la plantilla |
| `$layout` | Layout opcional, sin extensión; `null` desactiva layout |

## Flujo interno

### Resolución de vistas

Las vistas se identifican mediante rutas relativas sin extensión:

```text
dashboard
project
projects/show
```

View añade automáticamente `.php`.

No se aceptan:

- nombres vacíos;
- rutas absolutas;
- segmentos con `..`;
- null bytes;
- backslashes;
- extensiones manuales.

### Renderizado

View utiliza output buffering:

```php
ob_start();
extract($data, EXTR_SKIP);
require $filePath;
$content = ob_get_clean();
```

Los datos se exponen a la plantilla con `extract($data, EXTR_SKIP)`. Esta estrategia evita sobrescribir variables internas ya definidas dentro del método de renderizado.

## Dependencias

View depende de:

- `App\Core\Config`
- `App\Core\Response`
- `App\Core\Exceptions\ViewNotFoundException`

View no depende de:

- Request
- Router
- Env
- Database
- Services
- Models

## Integración con Response

View construye y devuelve una nueva instancia de Response:

```php
return (new Response())
    ->status(200)
    ->header('Content-Type', 'text/html; charset=UTF-8')
    ->body($content);
```

View nunca ejecuta `send()`. El envío final pertenece al Router.

## Manejo de layouts

Por defecto, View intenta aplicar el layout `layout`:

```php
$response = $view->render('dashboard');
```

Para renderizar sin layout:

```php
$response = $view->render('errors/404', ['title' => 'Página no encontrada'], null);
```

Cuando existe layout, View entrega al layout la variable `$content` con el HTML ya renderizado de la vista. Las variables originales de `$data` también están disponibles dentro del layout.

Si `$data` contiene una clave `content`, el contenido generado por la vista tiene prioridad dentro del layout.

## Exposición de datos

Las claves del array `$data` se convierten en variables locales de la plantilla.

Ejemplo:

```php
$response = $view->render('dashboard', [
    'title' => 'Narrador Studio',
    'projects' => $projects,
]);
```

Dentro de la plantilla:

```php
<h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
```

View no sanitiza datos automáticamente. La plantilla decide cómo escapar cada valor según su contexto HTML.

## Manejo de excepciones

Si la vista solicitada no existe, View lanza:

```php
ViewNotFoundException
```

Si el layout solicitado no existe, View lanza la misma excepción.

Si ocurre una excepción o error durante la ejecución de una plantilla, View limpia los buffers abiertos por el renderizado y relanza la excepción original.

Request y Response no capturan excepciones. Router capturará `CoreException` en una iteración posterior y transformará errores en respuestas HTTP seguras.

## Ejemplos

### Render con layout

```php
$view = new View();

$response = $view->render(
    'dashboard',
    [
        'title' => 'Narrador Studio',
        'projects' => $projects,
    ]
);
```

### Render sin layout

```php
$view = new View();

$response = $view->render(
    'errors/404',
    ['title' => 'Página no encontrada'],
    null
);
```

## Decisiones arquitectónicas

1. **Objeto por configuración**: View es un objeto para permitir inyectar directorios de vistas en pruebas.
2. **Sin estado HTTP propio**: View no captura Request ni envía la respuesta final.
3. **Devuelve Response**: View transforma HTML renderizado en una respuesta HTTP completa.
4. **Plantillas PHP simples**: No se incorpora motor externo ni sistema avanzado de componentes.
5. **Validación explícita de nombres**: View evita rutas absolutas, path traversal y extensiones manuales.
6. **Buffering seguro**: View limpia buffers abiertos durante errores y relanza la excepción original.
7. **Layout opcional**: El layout por defecto es `layout`; `null` renderiza solo la vista.

## Límites actuales

- No existe herencia avanzada de layouts.
- No existen componentes, slots ni secciones.
- No hay caché de plantillas.
- No hay resolución de múltiples paths de vistas.
- No hay helpers automáticos de escaping.

## Futuras mejoras

- Helpers explícitos para escape HTML.
- Soporte para partials simples.
- Mejor diagnóstico de rutas en modo debug.
- Renderizado especializado para páginas de error.
- Integración con Router cuando se implemente el ciclo HTTP completo.