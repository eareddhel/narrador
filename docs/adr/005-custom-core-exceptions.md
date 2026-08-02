# ADR-005: Jerarquia de excepciones con CoreException como clase base

## Estado

Aceptado

## Contexto

Los componentes del Core necesitan comunicar fallos de infraestructura de forma clara. El Router debe poder distinguir errores propios del framework de otros errores inesperados.

## Problema

Si cada componente lanza excepciones genericas o extiende directamente excepciones nativas distintas, el manejo centralizado en Router se vuelve fragil y poco expresivo.

## Decision

Se crea `App\Core\Exceptions\CoreException` como clase base comun para errores del Core.

Jerarquia:

```text
RuntimeException
  -> CoreException
       -> ConfigurationException
       -> DatabaseException
       -> RouteNotFoundException
       -> ViewNotFoundException
       -> TTSException
```

Solo `CoreException` extiende directamente `RuntimeException`. Las excepciones especificas son `final` y extienden `CoreException`.

## Consecuencias

El Router podra capturar `CoreException` para transformar errores del framework en respuestas HTTP consistentes. Cada componente mantiene excepciones expresivas sin duplicar logica.

## Alternativas descartadas

- Lanzar `RuntimeException` directamente desde cada componente.
- Usar una unica excepcion generica para todos los errores.
- Capturar excepciones dentro de Request o Response.
- Ocultar excepciones sin registrarlas.
