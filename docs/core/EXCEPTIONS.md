# Excepciones del Core

## Responsabilidad

Las excepciones del Core representan errores de infraestructura del framework interno de Narrador Studio. Su responsabilidad es comunicar fallos técnicos de forma explícita, legible y centralizada, sin mezclar el manejo de errores con la lógica de negocio.

Los componentes del Core lanzan excepciones específicas. El Router será responsable de capturarlas y convertirlas en respuestas HTTP adecuadas.

## Jerarquía

```text
RuntimeException
  -> CoreException
       -> ConfigurationException
       -> DatabaseException
       -> RouteNotFoundException
       -> ViewNotFoundException
       -> TTSException
```

## Propósito de CoreException

`App\Core\Exceptions\CoreException` es la clase base común para todas las excepciones propias del Core.

Solo `CoreException` extiende directamente `RuntimeException`. Las excepciones específicas extienden `CoreException` y no contienen lógica adicional por ahora.

Esto permite que Router capture todos los errores del framework mediante un único tipo base:

```php
use App\Core\Exceptions\CoreException;

try {
    // Ciclo HTTP coordinado por Router.
} catch (CoreException $exception) {
    // Convertir el error en una respuesta HTTP segura.
}
```

## Excepciones existentes

| Excepción | Componente | Uso |
|-----------|------------|-----|
| `CoreException` | Core | Clase base común para errores del framework |
| `ConfigurationException` | Config | Errores de carga o acceso a configuración |
| `DatabaseException` | Database | Errores de conexión o consulta a base de datos |
| `RouteNotFoundException` | Router | Ruta no encontrada |
| `ViewNotFoundException` | View | Plantilla no encontrada |
| `TTSException` | EdgeTTSService | Errores en servicios de síntesis de voz |

## Relación con Router

Router coordina el ciclo HTTP completo:

```text
Apache
  -> public/index.php
  -> bootstrap.php
  -> Autoloader
  -> Env
  -> Config
  -> Router
       -> Request::capture()
       -> resolver ruta
       -> Controller
       -> Service
       -> Model
       -> View
       -> Response
       -> Response::send()
```

Durante ese ciclo, Router será responsable de capturar `CoreException`.

En modo debug, Router podrá mostrar detalles técnicos útiles para desarrollo, como mensaje interno o traza controlada.

En producción, Router deberá mostrar mensajes seguros para el usuario y registrar el detalle técnico en `storage/logs/`.

## Relación con Request y Response

Request y Response no capturan excepciones.

Request solo representa la petición HTTP capturada mediante `Request::capture()`.

Response solo representa la respuesta HTTP: estado, cabeceras y cuerpo. Puede ser usada por Router para construir respuestas de error, pero no decide qué errores capturar.

## Ejemplos de uso

### Config

```php
use App\Core\Exceptions\ConfigurationException;

throw new ConfigurationException('Archivo de configuración no encontrado.');
```

### View

```php
use App\Core\Exceptions\ViewNotFoundException;

throw new ViewNotFoundException('La plantilla solicitada no existe.');
```

### Router

```php
use App\Core\Exceptions\CoreException;
use App\Core\Response;

try {
    $response = $controller($request);
} catch (CoreException $exception) {
    $response = (new Response())
        ->status(500)
        ->json([
            'success' => false,
            'message' => 'Ha ocurrido un error interno.',
            'data' => null,
        ]);
}

$response->send();
```

## Decisiones arquitectónicas

1. **Jerarquía común**: Todas las excepciones del Core comparten `CoreException` como clase base.
2. **Solo CoreException extiende RuntimeException**: Las excepciones específicas no dependen directamente de excepciones nativas.
3. **Excepciones específicas por componente**: Cada error técnico importante debe poder identificarse por su tipo.
4. **Router captura CoreException**: El manejo HTTP de errores se centraliza en Router.
5. **Request y Response no capturan errores**: Ambos se mantienen como objetos simples del ciclo HTTP.
6. **Mensajes seguros en producción**: El detalle técnico no debe mostrarse al usuario final.

## Futuras mejoras

- Incorporar códigos HTTP sugeridos por tipo de excepción.
- Registrar excepciones automáticamente en `storage/logs/` desde Router.
- Añadir una estrategia de renderizado de errores HTML y JSON.
- Diferenciar respuestas de error para rutas web y peticiones AJAX.
- Definir una política de mensajes técnicos visibles solo en modo debug.