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

## Consecuencias

El estado HTTP queda encapsulado. Los controllers pueden construirse alrededor de entradas y salidas explicitas. El Router puede transformar errores en respuestas sin que otros componentes envien contenido directamente.

## Alternativas descartadas

- Usar `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE` o `$_SERVER` fuera de Request.
- Definir Response como clase estatica.
- Enviar respuestas directamente desde controllers.
- Capturar Request dentro de bootstrap.
