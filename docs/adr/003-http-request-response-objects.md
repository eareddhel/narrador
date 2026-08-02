# ADR-003: Request y Response como objetos por peticion HTTP

## Estado

Aceptado

## Contexto

Cada solicitud HTTP tiene datos de entrada y genera una respuesta propia. Request y Response representan ese estado del ciclo HTTP.

## Problema

Usar superglobales, cabeceras y salida directa desde cualquier punto del sistema dificulta pruebas, mantenimiento y manejo uniforme de errores.

## Decision

Request y Response seran objetos del ciclo HTTP.

- El Router crea la peticion mediante `Request::capture()`.
- Request es inmutable y captura las superglobales una sola vez.
- Response representa estado, cabeceras y cuerpo.
- Los controllers devuelven objetos Response.
- El Router llama finalmente a `Response::send()`.


## Evolución

Request mantendrá una estrategia inmutable para incorporar parámetros de ruta.

Router resolverá los parámetros nombrados y llamará a:

```php
$request->withRouteParameters($parameters);
```

`withRouteParameters(array $parameters): self` no modificará la instancia original. Devolverá una nueva instancia de Request enriquecida, y Router utilizará esa nueva instancia para invocar el Controller.

Los Controllers continuarán recibiendo únicamente Request y consultarán los parámetros mediante:

```php
$request->route('uuid');
$request->routeParameters();
```

No existirán métodos mutadores como `setRouteParameters()`.

La decisión adopta una filosofía inspirada en PSR-7, especialmente en métodos `with*` que devuelven nuevas instancias, sin implementar PSR-7 completo ni incorporar sus interfaces.

## Consecuencias

El estado HTTP queda encapsulado. Los controllers pueden construirse alrededor de entradas y salidas explicitas. El Router puede transformar errores en respuestas sin que otros componentes envien contenido directamente.

## Alternativas descartadas

- Usar `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE` o `$_SERVER` fuera de Request.
- Definir Response como clase estatica.
- Enviar respuestas directamente desde controllers.
- Capturar Request dentro de bootstrap.
