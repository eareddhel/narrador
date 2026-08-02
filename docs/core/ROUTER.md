# Router

## Responsabilidad

Router será el componente del Core responsable de coordinar el ciclo HTTP completo de una petición concreta.

Router capturará la petición, resolverá una ruta explícitamente registrada, invocará un Controller, recibirá un objeto `App\Core\Response`, gestionará excepciones del Core y enviará la respuesta final.

Router no será una fachada estática. Será un objeto con una colección interna de rutas registradas explícitamente.

## Filosofía de diseño

La Iteración 008 priorizará un Router pequeño, explícito y fácil de razonar.

No habrá:

- autodetección de rutas;
- escaneo de directorios;
- attributes;
- annotations;
- reflexión para descubrir rutas;
- middleware;
- expresiones regulares configurables;
- generación inversa de URL.

La arquitectura debe permitir evolución futura, pero sin anticipar abstracciones que todavía no son necesarias.

## Ciclo de vida

1. Bootstrap inicializa infraestructura global: Autoloader, Env y Config.
2. Se crea/configura una instancia de Router.
3. Se registran rutas explícitas.
4. Router ejecuta `Request::capture()`.
5. Router resuelve método HTTP y URI.
6. Router extrae parámetros nombrados simples.
7. Router construye o invoca el Controller correspondiente.
8. El Controller recibe Request y devuelve Response.
9. Router captura excepciones del Core.
10. Router ejecuta `Response::send()` una única vez al final del ciclo.

Bootstrap no captura Request. Router posee la coordinación de la petición concreta.

## API pública prevista

La API final se definirá durante la Iteración 008, pero la forma aprobada será equivalente a:

```php
$router->get('/', DashboardController::class);
$router->get('/projects', ProjectController::class);
$router->post('/projects', ProjectStoreController::class);
```

No se utilizará una fachada estática para registrar rutas.

Router mantendrá internamente la colección de rutas registradas.

## Registro de rutas

Las rutas serán explícitas y declarativas.

El archivo canónico previsto es:

```text
config/routes.php
```

Ejemplo conceptual:

```php
return [
    ['GET', '/', DashboardController::class],
    ['GET', '/projects/{uuid}', ProjectShowController::class],
    ['POST', '/projects', ProjectStoreController::class],
];
```

Este ejemplo documenta la arquitectura, no fija todavía la implementación exacta del archivo.

Reglas:

- no se escanearán Controllers;
- no se usarán attributes PHP;
- no se usarán annotations;
- no se usará reflexión para descubrir rutas;
- no habrá convenciones ocultas;
- el orden y origen de rutas serán explícitos;
- Router será el único componente que interpretará las definiciones.

## Métodos HTTP

El soporte inicial será:

- GET
- POST

Reglas:

- GET se utiliza para lectura y navegación sin cambio de estado.
- POST se utiliza para operaciones que crean, modifican, eliminan, generan o ejecutan acciones.
- No se usará GET para cambios de estado.
- El soporte de GET no contradice la preferencia por POST en operaciones mutables.

Soporte futuro posible:

- PUT
- PATCH
- DELETE

## Parámetros de ruta

Router soportará parámetros nombrados simples:

```text
/projects/{uuid}
```

Router deberá extraerlos como datos de ruta para el Controller.

No se implementará todavía:

- parámetros opcionales;
- comodines;
- regex configurables;
- grupos de rutas;
- prefijos;
- nombres de rutas;
- generación inversa de URL.

Estos elementos quedan sujetos a una necesidad real futura.

## Controladores invocables

La convención principal será un Controller invocable por acción:

```php
final class DashboardController
{
    public function __invoke(Request $request): Response
    {
        // ...
    }
}
```

Principios:

- Un Controller representa una acción.
- Cada Controller tiene una responsabilidad principal.
- Router invoca `__invoke()`.
- El Controller recibe Request.
- El Controller devuelve Response.
- El Controller no ejecuta `echo`, `header()`, `exit()` ni `die()`.
- El Controller no accede directamente a Database.
- La lógica de negocio pertenece a Services.
- View se inyectará o proporcionará como objeto cuando corresponda.
- View no se utilizará como clase estática.

No se implementará todavía un contenedor de dependencias. La construcción de Controllers con múltiples dependencias queda como decisión pendiente de implementación mínima para Iteración 008.

## Ruta inexistente

Una ruta inexistente por método HTTP o patrón de URI provocará:

```php
RouteNotFoundException
```

Router no devolverá silenciosamente `false`, `null` ni HTML directo.

La excepción será capturada por el mecanismo central del ciclo HTTP.

## Manejo de excepciones

Router será responsable de capturar:

```php
CoreException
```

y convertirla en un objeto Response seguro.

Principios:

- Request no captura excepciones.
- Response no captura excepciones.
- Controllers no muestran errores directamente.
- Router coordina el manejo final.
- En `APP_DEBUG=true`, Router podrá ofrecer información diagnóstica controlada.
- En producción, Router entregará mensajes seguros sin stack trace ni secretos.
- Los errores deberán registrarse en `storage/logs/` cuando exista el componente de logging.
- No se implementará logging en Iteración 008.
- No se implementará una página HTML de errores salvo que se defina expresamente en Iteración 008.

Las excepciones que no pertenezcan a `CoreException` también deberán gestionarse de forma segura en el límite HTTP, sin ocultar su naturaleza durante desarrollo.

## Flujo HTTP consolidado

```text
Apache
  -> public/index.php
  -> bootstrap.php
       -> Autoloader
       -> Env
       -> Config
  -> Router
       -> Request::capture()
       -> resolver método y URI
       -> extraer parámetros nombrados
       -> construir/invocar Controller
       -> Controller
            -> Service
                 -> Model
                      -> Database
            -> View
            -> Response
       -> capturar excepciones
       -> Response::send()
```

Response se envía una única vez, al final del ciclo.

## Middleware

No se implementará middleware en Iteración 008.

La arquitectura no debe impedir incorporarlo más adelante, pero no se añadirán pipeline, contratos ni clases anticipadas. Se aplicará YAGNI.

## Dependencias

Router dependerá de componentes del Core necesarios para coordinar el ciclo HTTP:

- Request
- Response
- Config
- CoreException
- RouteNotFoundException

Router no deberá conocer detalles internos de Services, Models o Database. Solo invocará Controllers y gestionará respuestas.

## Ejemplos

### Registro explícito

```php
$router->get('/', DashboardController::class);
$router->get('/projects/{uuid}', ProjectShowController::class);
$router->post('/projects', ProjectStoreController::class);
```

### Controller invocable

```php
final class ProjectShowController
{
    public function __invoke(Request $request): Response
    {
        // Resolver datos, delegar en Services y devolver Response.
    }
}
```

## Decisiones arquitectónicas

1. **Router como objeto**: No se usará fachada estática.
2. **Rutas explícitas**: El origen de rutas será declarativo y visible.
3. **Controllers invocables**: Un Controller representa una acción.
4. **Router coordina Request y Response**: Captura Request y envía Response.
5. **RouteNotFoundException**: Las rutas inexistentes se expresan con excepción del Core.
6. **Sin autodetección**: No hay attributes, annotations, reflexión ni escaneo.
7. **Parámetros simples**: Se admiten parámetros nombrados como `{uuid}`.
8. **Sin middleware inicial**: Middleware queda aplazado por YAGNI.

## Futuras mejoras

- Soporte para PUT, PATCH y DELETE.
- Middleware cuando exista una necesidad real.
- Nombres de rutas y generación inversa de URL.
- Grupos y prefijos de rutas.
- Páginas de error HTML especializadas.
- Logging centralizado de errores.