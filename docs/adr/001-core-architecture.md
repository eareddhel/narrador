# ADR-001: Arquitectura del Core y separacion de capas

## Estado

Aceptado

## Contexto

Narrador Studio se construye como un microframework propio en PHP puro. El proyecto necesita un flujo de ejecucion claro, facil de leer y mantenible antes de avanzar con componentes como View, Database y Router.

## Problema

Sin una separacion explicita de responsabilidades, los controladores podrian acumular logica de negocio, acceso a datos, renderizado y envio de respuestas. Eso volveria dificil evolucionar el Core y reemplazar componentes.

## Decision

El Core seguira un flujo HTTP coordinado por el Router:

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

La regla de capas sera:

```text
Controller -> Service -> Model
```

Los controllers reciben la peticion, delegan la logica en Services y devuelven objetos Response. No acceden directamente a la base de datos ni envian contenido al navegador.

## Consecuencias

El flujo queda predecible y facil de documentar. Cada componente puede evolucionar sin invadir responsabilidades de otros. El Router concentra la coordinacion HTTP y los controllers quedan pequenos.

## Alternativas descartadas

- Controllers enviando respuestas directamente con `echo`, `header()` o `exit()`.
- Controllers accediendo directamente a Database.
- Bootstrap capturando Request y coordinando todo el ciclo HTTP.
- Usar un framework externo para resolver el ciclo HTTP.
