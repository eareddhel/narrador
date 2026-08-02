# ADR-006: Router explícito y Controllers invocables

## Estado

Aceptado

## Contexto

Narrador Studio necesita implementar Router como coordinador final del ciclo HTTP. Ya existen decisiones previas sobre Request, Response, Config, View, Database y excepciones del Core.

## Problema

Un Router con descubrimiento automático, fachada estática o Controllers con múltiples acciones introduciría convenciones ocultas y acoplamiento temprano. Eso dificultaría mantener un flujo explícito y consistente con el microframework propio.

## Decisión

Router será un objeto. Las rutas se registrarán explícitamente mediante una API equivalente a:

```php
$router->get('/', DashboardController::class);
$router->get('/projects/{uuid}', ProjectShowController::class);
$router->post('/projects', ProjectStoreController::class);
```

Los Controllers serán invocables y orientados a una sola acción:

```php
final class DashboardController
{
    public function __invoke(Request $request): Response
    {
        // ...
    }
}
```

Router capturará `Request::capture()`, resolverá método y URI, extraerá parámetros nombrados simples, invocará el Controller, recibirá Response, capturará excepciones del Core y ejecutará `Response::send()`.

Una ruta inexistente provocará `RouteNotFoundException`.

No habrá autodetección de rutas, attributes, annotations, reflexión para descubrimiento, regex arbitrarias ni middleware en la primera versión.

## Consecuencias

El flujo HTTP queda explícito y predecible. Las rutas tienen un origen claro. Los Controllers se mantienen pequeños y orientados a acciones concretas. La implementación inicial de Router puede permanecer simple sin cerrar puertas a mejoras futuras.

## Alternativas descartadas

- Fachada estática `Route`.
- Controllers con muchos métodos como convención principal.
- Descubrimiento automático de rutas.
- Attributes o annotations.
- Reflexión para descubrir Controllers o rutas.
- Regex arbitrarias en la primera versión.
- Middleware prematuro.