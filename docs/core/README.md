# Core del Framework

El directorio `docs/core/` contiene la documentación técnica de los componentes que forman el núcleo de Narrador Studio.

Cada documento describe:

- Responsabilidad del componente.
- API pública.
- Flujo interno.
- Dependencias.
- Decisiones arquitectónicas.
- Ejemplos de uso.

## Componentes

| Componente | Estado |
|------------|--------|
| Autoloader | ✅ |
| Env | ✅ |
| Config | ✅ |
| Response | ✅ |
| Request | ✅ |
| Exceptions | ✅ |
| View | ✅ |
| Database | ✅ |
| Router | ✅ |

## Servicios globales

Env y Config son servicios globales con API estática. No pertenecen a una petición HTTP concreta y se cargan una única vez durante la inicialización de la aplicación.

```text
Autoloader
↓
Env
↓
Config
```

## Ciclo HTTP

El Router es el coordinador del ciclo HTTP. `Request::capture()` no pertenece al bootstrap; el Router captura la petición, resuelve la ruta, ejecuta el controller, recibe un objeto Response y envía la respuesta final.

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

## Excepciones

Los componentes del Core lanzan excepciones específicas que extienden `App\Core\Exceptions\CoreException`. Request y Response no capturan excepciones. Router será responsable de capturar `CoreException` y transformarlas en respuestas HTTP.

## Plantilla para escribir documentación de un componente

```markdown
# Response

## Responsabilidad

## Filosofía de diseño

## Ciclo de vida

## API pública

## Flujo interno

## Dependencias

## Ejemplos

## Decisiones arquitectónicas

## Futuras mejoras
```
