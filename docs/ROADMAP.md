# Roadmap

Hitos del proyecto Narrador Studio.

---

## v0.1 — Arquitectura

Microframework propio con el siguiente flujo de inicialización y ciclo HTTP:

```
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

`Request::capture()` no pertenece al bootstrap. El Router coordina la petición HTTP, captura excepciones del Core y envía la respuesta final.

### Iteración 001 — Autoloader
Sistema de carga automática de clases (`spl_autoload_register`). Namespace `App\` mapeado a `app/`. Compatible Windows/Linux.

### Iteración 002 — Env
Gestor de variables de entorno. Lectura única de `.env`, caché interna, API tipada (`get`, `getString`, `getInt`, `getBool`, `has`, `all`).

### Iteración 003 — Config
Sistema de configuración modular por dominios funcionales. Clase `App\Core\Config` como capa de acceso centralizada.

Archivos de configuración:
- `app.php` — configuración general de la aplicación.
- `database.php` — conexión y parámetros de base de datos.
- `routes.php` — definición de rutas.
- `tts.php` — configuración de servicios de síntesis de voz.
- `constants.php` — constantes globales del sistema.

Reglas:
- `Env` proporciona las variables de entorno.
- `Config` será la capa de acceso a la configuración.
- `Database`, `Router`, `View` y los servicios consumirán `Config`, nunca los archivos directamente.
- Ninguna clase accederá directamente a los archivos dentro de `config/`.

### Iteración 004 — Response
Representa la respuesta HTTP de la aplicación. No depende de Request. API para respuestas JSON, redirecciones y cabeceras.

Documentación: `docs/core/RESPONSE.md`

### Iteración 005 — Request
Representa la petición HTTP recibida. Acceso a datos de entrada sin superglobales. API explícita sin método `get()` para eliminar confusión con HTTP GET.

Documentación: `docs/core/REQUEST.md`

### Consolidación arquitectónica previa a View
Jerarquía común de excepciones del Core mediante `CoreException`. ADRs formales para arquitectura del Core, servicios globales, objetos HTTP, configuración modular y excepciones propias.

Reglas consolidadas:
- Env y Config son servicios globales estáticos.
- Request y Response son objetos del ciclo HTTP.
- Los controllers devuelven objetos Response.
- Router coordina el ciclo HTTP, captura `CoreException` y ejecuta `Response::send()`.

### Iteración 006 — View
Motor de renderizado de plantillas.

Documentación: `docs/core/VIEW.md`

### Iteración 007 — Database
Capa orientada a objetos sobre PDO, sin Singleton, con conexión encapsulada por instancia. Utilizará prepared statements, soportará transacciones, transformará errores PDO en `DatabaseException` y no implementará ORM ni Query Builder.

Documentación: `docs/core/DATABASE.md`

### Iteración 008 — Router
Enrutador con soporte para rutas parametrizadas (`/project/{uuid}`). Coordina `Request::capture()`, resolución de rutas, controllers, captura de `CoreException` y envío final mediante `Response::send()`. Depende de Request, Response y View.

Documentación: `docs/core/ROUTER.md`

Estructura de carpetas, convenciones PSR-4, separación de capas (Controller → Service → Model).

---

## v0.2 — CRUD

Proyectos y secciones. Crear, listar, editar, eliminar. Dashboards con listado de proyectos.

---

## v0.3 — TTS

Integración con Edge TTS. Generación de audio por sección. AudioService como orquestador.

---

## v0.4 — Exportación

Exportación de proyectos completos a ZIP con audios y estructura organizada.

---

## v0.5 — Timeline

Editor de timeline para ordenar, recortar y organizar secciones de audio antes de exportar.

---

## v0.6 — IA

Integración con inteligencia artificial para sugerencias de contenido, corrección de textos y generación de guiones.

---

## v0.7 — Video

Generación de video sincronizado con audio y texto. Exportación a formatos estándar.

---

## v1.0 — Primera versión estable

Release consolidado con todas las funcionalidades estables y documentación completa.
