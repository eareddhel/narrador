# Roadmap

Hitos del proyecto Narrador Studio.

---

## v0.1 — Arquitectura

Microframework propio con el siguiente flujo de inicialización:

```
Autoloader
    ↓
Env
    ↓
Config
    ↓
Request
    ↓
Response
    ↓
View
    ↓
Database
    ↓
Router
```

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

### Iteración 004 — Request / Response
Acceso a datos de entrada (`Request`) y respuestas HTTP (`Response`). Sin superglobales.

### Iteración 005 — Router
Enrutador con soporte para rutas parametrizadas (`/project/{uuid}`).

### Iteración 006 — View
Motor de renderizado de plantillas.

### Iteración 007 — Database
Conexión PDO con patrón Singleton.

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
