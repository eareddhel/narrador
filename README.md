# Narrador Studio

Professional Tutorial Narration Platform
PHP • Bootstrap • Edge TTS
Version 0.1

# Narrador Studio

Estudio de producción de narraciones para tutoriales. No es un generador de audios: es una herramienta profesional diseñada para crear, organizar y exportar narraciones de forma rápida y mantenible.

---

## Filosofía

Todo el diseño del software privilegia:

- **Rapidez** — flujos ágiles, pasos mínimos.
- **Reutilización** — componentes y servicios compartidos.
- **Mantenibilidad** — código claro, separado y documentado.
- **Independencia de frameworks** — PHP puro con convenciones PSR.
- **Código limpio** — sin atajos, sin atajos técnicos.

---

## Objetivo

Crear una herramienta profesional para producir tutoriales narrados, administrar proyectos de voz y generar archivos de audio de alta calidad mediante motores TTS, manteniendo una arquitectura simple, extensible y libre de dependencias innecesarias.

---

## Arquitectura

### Flujo de ejecución

```
Apache
  └─ public/index.php
       └─ bootstrap.php
            ├─ Autoloader
            ├─ Env (.env)
            ├─ Config
            └─ Router
                 ├─ Request::capture()
                 ├─ resolver ruta
                 ├─ Controller
                 │    └─ Service
                 │         └─ Model
                 ├─ View
                 ├─ Response
                 └─ Response::send()
```

`Request::capture()` no pertenece al bootstrap. El Router coordina el ciclo HTTP completo: captura la petición, resuelve la ruta, invoca el controller, recibe un objeto Response, captura excepciones del Core y envía la respuesta final.

### Regla de capas

Nunca saltarse capas. Un Controller **nunca** conversa con la Database.

```
Controller → Service → Model
```

### Árbol del proyecto

```
narrador/
├── .github/
│   ├── ISSUE_TEMPLATE/
│   ├── workflows/
│   └── PULL_REQUEST_TEMPLATE.md
├── app/
│   ├── Controllers/
│   │   ├── AudioController.php
│   │   ├── DashboardController.php
│   │   └── ProjectController.php
│   ├── Core/
│   │   ├── Autoloader.php
│   │   ├── Database.php
│   │   ├── Env.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Router.php
│   │   ├── View.php
│   │   └── Exceptions/
│   │       ├── CoreException.php
│   │       ├── ConfigurationException.php
│   │       ├── DatabaseException.php
│   │       ├── RouteNotFoundException.php
│   │       ├── ViewNotFoundException.php
│   │       └── TTSException.php
│   ├── Helpers/
│   │   └── Utils.php
│   ├── Models/
│   │   ├── Project.php
│   │   └── Section.php
│   └── Services/
│       ├── AudioService.php
│       ├── EdgeTTSService.php
│       ├── ProjectService.php
│       ├── SlugService.php
│       └── ZipService.php
├── config/
│   ├── app.php
│   ├── constants.php
│   ├── database.php
│   ├── routes.php
│   └── tts.php
├── database/
│   └── schema.sql
├── docs/
│   ├── adr/
│   │   ├── README.md
│   │   ├── 001-core-architecture.md
│   │   ├── 002-global-services.md
│   │   ├── 003-http-request-response-objects.md
│   │   ├── 004-modular-configuration.md
│   │   └── 005-custom-core-exceptions.md
│   ├── core/
│   │   ├── AUTOLOADER.md
│   │   ├── CONFIG.md
│   │   ├── DATABASE.md
│   │   ├── ENV.md
│   │   ├── EXCEPTIONS.md
│   │   ├── README.md
│   │   ├── REQUEST.md
│   │   ├── RESPONSE.md
│   │   ├── ROUTER.md
│   │   └── VIEW.md
│   ├── mockups/
│   ├── screenshots/
│   ├── wireframes/
│   ├── ARCHITECTURE.md
│   ├── DATABASE.md
│   ├── DECISIONS.md
│   ├── ITERATIONS.md
│   ├── PHP.md
│   └── ROADMAP.md
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   └── app.css
│   │   ├── js/
│   │   │   ├── app.js
│   │   │   ├── dashboard.js
│   │   │   └── project.js
│   │   ├── img/
│   │   ├── fonts/
│   │   └── vendor/
│   ├── uploads/
│   └── index.php
├── storage/
│   ├── audio/
│   ├── cache/
│   ├── logs/
│   ├── projects/
│   └── temp/
├── tests/
│   ├── Unit/
│   ├── Integration/
│   ├── Feature/
├── tools/
│   ├── clear-audio.php
│   ├── clear-cache.php
│   ├── test-config.php
│   ├── test-env.php
│   ├── test-request.php
│   ├── test-response.php
│   ├── update-bootstrap.bat
│   └── update-icons.bat
├── vendor/
├── views/
│   ├── dashboard.php
│   ├── layout.php
│   └── project.php
├── .editorconfig
├── .env
├── .env.example
├── .gitattributes
├── .gitignore
├── bootstrap.php
├── CHANGELOG.md
├── License
└── README.md
```

---

## Responsabilidad de cada carpeta

| Carpeta | Responsabilidad |
|---|---|
| `Controllers/` | Reciben la petición HTTP. Nunca contienen lógica. |
| `Models/` | Representan entidades de base de datos. |
| `Services/` | Toda la lógica del sistema vive aquí. |
| `Core/` | Framework interno (Router, Request, Response, etc.). |
| `Helpers/` | Funciones auxiliares puras. |
| `Views/` | Plantillas HTML/PHP sin lógica de negocio. |
| `config/` | Configuración modular del sistema, organizada por dominios funcionales. |

---

## Componentes del Core

Los componentes del Core se dividen en dos categorías claramente diferenciadas.

### Categorías de componentes

| Categoría | Características | Componentes |
|---|---|---|
| **Servicios globales** | API estática, sin estado HTTP, se cargan una única vez | Env, Config |
| **Objetos del ciclo HTTP** | Se instancian, poseen estado propio, cada petición genera nuevas instancias | Request, Response |

### Flujo del ciclo HTTP

```
Cliente
  ↓
Router
  ├─ Request::capture()
  ├─ resolver ruta
  ├─ Controller
  ├─ Response
  └─ Response::send()
  ↓
Navegador
```

El Router es el coordinador del ciclo HTTP. Request y Response son objetos por petición, pero su creación/envío se organiza desde Router.

### Flujo de capas de aplicación

```
Controller
  ↓
Service
  ↓
Model
```

### Servicios globales

#### Autoloader (`app/Core/Autoloader.php`)
Carga automática de clases bajo el namespace `App\`.

#### Env (`app/Core/Env.php`)
Servicio global. Lee automáticamente el archivo `.env`. Uso:
```php
Env::get('DB_HOST');
```
Nunca acceder a `$_ENV` directamente.

#### Config (`app/Core/Config.php`)
Servicio global. Carga y gestiona la configuración modular del sistema. Los archivos de configuración se organizan por dominios funcionales dentro de `config/`:
- `app.php` — configuración general de la aplicación.
- `database.php` — conexión y parámetros de base de datos.
- `routes.php` — definición de rutas.
- `tts.php` — configuración de servicios de síntesis de voz.
- `constants.php` — constantes globales del sistema.

Cada archivo posee una única responsabilidad. Ninguna clase accederá directamente a los archivos dentro de `config/`. Toda la configuración del sistema deberá obtenerse mediante la API de `Config`:
```php
Config::get('database.host');
```

### Objetos del ciclo HTTP

#### Response (`app/Core/Response.php`)
Objeto que representa la respuesta HTTP de la aplicación. No depende de Request. Uso:
```php
$response = new Response();
$response->json($datos);
$response->redirect('/');
```

#### Request (`app/Core/Request.php`)
Objeto que representa la petición HTTP recibida. Acceso a datos de entrada sin usar superglobales. API explícita y deliberadamente sin método `get()` para eliminar la confusión con el método HTTP GET:
```php
$request = Request::capture();
$request->query('page');       // parámetros de la URL (query string)
$request->post('nombre');      // datos enviados mediante HTTP POST
$request->input('email');     // prioriza POST, fallback a query string
$request->file('avatar');     // archivos multipart/form-data
$request->header('Accept');   // cabeceras HTTP
$request->cookie('session');  // cookies
$request->server('REMOTE_ADDR'); // valores del servidor (uso estricto)
```

Se evita deliberadamente `Request::get()` porque en el contexto HTTP significa "obtener un valor" y no guarda relación con el método HTTP GET. Las clases `Config`, `Env`, `Cache`, `Session` y otras clases de infraestructura sí utilizarán `get()` ya que en ese contexto el significado es explícito.

### Componentes del ciclo HTTP (futuros)

#### View (`app/Core/View.php`)
Renderizado de plantillas.

#### Database (`app/Core/Database.php`)
Conexión PDO con patrón Singleton.

#### Router (`app/Core/Router.php`)
Enrutador que coordina el ciclo HTTP. Captura la petición con `Request::capture()`, resuelve la ruta, invoca el controller, recibe un objeto Response, captura excepciones del Core y envía finalmente la respuesta con `Response::send()`. Soporte para rutas:
- `GET /`
- `GET /project`
- `GET /project/{uuid}`
- `POST /project/create`
- `POST /audio/generate`

### Principios arquitectónicos

- Env y Config son servicios globales con API estática.
- Request y Response representan objetos del protocolo HTTP.
- Los controladores nunca enviarán directamente contenido al navegador.
- Los controladores devolverán un objeto Response.
- El Router será el responsable de enviar finalmente la respuesta.
- El Router será el responsable de capturar `CoreException`.

### Excepciones del Core

Cada componente del Core utilizará excepciones específicas que describan claramente el tipo de error producido. Se evita el uso directo de excepciones genéricas de PHP (`RuntimeException`, `Exception`, `InvalidArgumentException`, etc.) cuando exista una excepción propia del framework.

Las excepciones representan errores de infraestructura del framework. Mejoran la legibilidad del código, el diagnóstico y el mantenimiento.

| Excepción | Componente | Uso |
|-----------|------------|-----|
| `CoreException` | Core | Clase base común para errores del framework |
| `ConfigurationException` | Config | Errores de carga o acceso a configuración |
| `DatabaseException` | Database | Errores de conexión o consulta a base de datos |
| `RouteNotFoundException` | Router | Ruta no encontrada |
| `ViewNotFoundException` | View | Plantilla no encontrada |
| `TTSException` | EdgeTTSService | Errores en servicios de síntesis de voz |

Todas las excepciones se ubican en `app/Core/Exceptions/`. `CoreException` hereda de `RuntimeException`; las excepciones específicas heredan de `CoreException`.

```
RuntimeException
  └─ CoreException
       ├─ ConfigurationException
       ├─ DatabaseException
       ├─ RouteNotFoundException
       ├─ ViewNotFoundException
       └─ TTSException
```

#### Relación con el ciclo HTTP

- Request y Response no gestionan excepciones.
- Router será el responsable de capturar `CoreException`.
- Router transformará errores del Core en respuestas HTTP adecuadas.
- En modo debug, el Router podrá mostrar información técnica adicional.
- En producción, el Router mostrará mensajes seguros para el usuario.

```
Cliente
  ↓
Router ← captura CoreException
  ├─ Request::capture()
  ├─ Controller
  ├─ Service
  ├─ Model
  ├─ View
  └─ Response
  ↓
Navegador
```

### Architecture Decision Records

Las decisiones arquitectónicas importantes se registran como ADRs en `docs/adr/`.

| ADR | Decisión |
|---|---|
| `001-core-architecture.md` | Arquitectura del Core y separación de capas |
| `002-global-services.md` | Env y Config como servicios globales estáticos |
| `003-http-request-response-objects.md` | Request y Response como objetos por petición HTTP |
| `004-modular-configuration.md` | Configuración modular accedida exclusivamente mediante Config |
| `005-custom-core-exceptions.md` | Jerarquía de excepciones con CoreException como clase base |

### Documentación técnica del Core

Cada componente importante del Core contará con documentación técnica independiente ubicada en `docs/core/`. Los documentos previstos son:

| Documento | Componente |
|---|---|
| `autoloader.md` | Autoloader |
| `env.md` | Env |
| `config.md` | Config |
| `response.md` | Response |
| `request.md` | Request |
| `view.md` | View |
| `database.md` | Database |
| `router.md` | Router |

Cada documento describirá:
- Responsabilidad.
- Diseño.
- API pública.
- Flujo interno.
- Decisiones arquitectónicas.
- Ejemplos de uso.

---

## Convenciones PHP

- **PSR-12** — estándar de código.
- **PSR-4** — namespaces y autoload.
- **PDO** — acceso a base de datos.
- Controladores pequeños, toda la lógica en Services.
- Sin HTML mezclado con PHP.
- Sin `include` por todos lados.
- Todo preparado para crecer.

### Namespaces PSR-4

```php
namespace App\Core;

class Router { ... }
```

```php
use App\Core\Router;
```

### Encabezado estándar

```php
<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Narrador Studio
|--------------------------------------------------------------------------
| Archivo : Autoloader.php
| Autor   : Roberto + ChatGPT
| Versión : 0.1.0
|--------------------------------------------------------------------------
*/

namespace App\Core;
```

---

## Principios de diseño

El proyecto sigue los principios SOLID siempre que sea razonable.

- Single Responsibility Principle
- Open/Closed Principle
- Liskov Substitution Principle
- Interface Segregation Principle
- Dependency Inversion Principle

No se aplicarán de forma dogmática, pero cualquier cambio deberá respetar la separación de responsabilidades.

## Convenciones de nombres

| Elemento | Convención | Ejemplo |
|---|---|---|
| Clases | PascalCase | `ProjectService` |
| Métodos | camelCase (verbos) | `generateAudio()` |
| Variables | camelCase | `$projectId` |
| Constantes | UPPER_SNAKE_CASE | `PROJECT_STATUS_DRAFT` |
| Archivos | Misma clase + `.php` | `ProjectService.php` |
| Rutas | minúsculas, guiones | `/project/create` |

### Nombres de métodos

Siempre verbos:
- `createProject()`
- `generateAudio()`
- `deleteSection()`
- `updateVoice()`

Nunca:
- `project()`
- `audio()`
- `voice()`

### Variables

Nunca usar una letra:
```php
// Incorrecto
$a, $b, $c

// Correcto
$project, $projectId, $audioPath, $section
```

---

## Base de datos

Toda tabla tendrá:

| Columna | Descripción |
|---|---|
| `id` | Clave primaria interna. |
| `uuid` | Identificador público (nunca exponer el `id`). |
| `created_at` | Fecha de creación. |
| `updated_at` | Fecha de última modificación. |

### UUID

Nunca exponer IDs internos al usuario:

```
❌ /project/15
✅ /project/c0ab5d8d-...
```

---

## Configuración

### Variables de entorno

Toda configuración sensible va en `.env`. Nunca en el código:
```
❌ $host = "localhost";
✅ DB_HOST=localhost  (en .env)
```

### Configuración modular

El sistema utiliza una configuración modular organizada por dominios funcionales. Cada archivo en `config/` posee una única responsabilidad:

```
config/
├── app.php          — configuración general de la aplicación
├── database.php     — conexión y parámetros de base de datos
├── routes.php       — definición de rutas
├── tts.php          — configuración de servicios de síntesis de voz
└── constants.php    — constantes globales del sistema
```

Ninguna clase accederá directamente a los archivos dentro de `config/`. Toda la configuración del sistema deberá obtenerse mediante la API de `App\Core\Config`:
```php
Config::get('database.host');
Config::get('app.timezone');
```

La clase `App\Core\Config` es responsable de cargar estos archivos y exponer los valores de forma centralizada.

---

## Manejo de errores

Nunca capturar excepciones para ocultarlas.

Siempre:

- Registrar en storage/logs/
- Mostrar un mensaje amigable al usuario.
- Mantener el stack trace únicamente en modo DEBUG.

El sistema nunca debe mostrar errores PHP al usuario final.

## Gestión de dependencias

Narrador Studio separa estrictamente las dependencias del backend y del frontend.

### Dependencias PHP

La carpeta:

```
/vendor/
```

está reservada exclusivamente para Composer y librerías PHP.

Aunque actualmente el proyecto no utiliza Composer, esta carpeta se mantiene para conservar la compatibilidad con el estándar PSR y facilitar futuras incorporaciones.

Nunca deben almacenarse aquí archivos CSS, JavaScript, imágenes o fuentes.

---

### Dependencias Frontend

Todas las librerías utilizadas por el navegador deben almacenarse localmente en:

```
public/assets/vendor/
```

Por ejemplo:

```
public/assets/vendor/bootstrap/
public/assets/vendor/bootstrap-icons/
public/assets/vendor/sortablejs/
```

No se utilizarán CDN en producción.

Esto garantiza:

- funcionamiento sin conexión a Internet;
- mayor velocidad de carga;
- independencia de servicios externos;
- control total sobre las versiones utilizadas;
- compatibilidad con redes escolares que restringen CDNs.

---

### Bootstrap

Narrador Studio utiliza Bootstrap 5.3.3 como framework principal de interfaz.

Antes de escribir CSS personalizado, debe comprobarse que Bootstrap no proporcione ya un componente o utilidad equivalente.

Se privilegiará el uso de:

- Grid System
- Utility Classes
- Cards
- Forms
- Offcanvas
- Accordion
- Modal
- Toast
- Dropdown
- Navbar
- Bootstrap Icons

El CSS propio deberá limitarse únicamente a la identidad visual del proyecto y a componentes específicos que Bootstrap no resuelva.

---

### JavaScript

El proyecto utiliza JavaScript Vanilla (ES6+).

No se incorporarán frameworks como:

- jQuery
- Vue
- React
- Angular
- Alpine

Las solicitudes asíncronas se realizarán exclusivamente mediante la API `fetch()`.

### Respuestas JSON

Toda respuesta AJAX tendrá esta estructura:
```json
{
    "success": true,
    "message": "",
    "data": {}
}
```

Error:
```json
{
    "success": false,
    "message": "Proyecto inexistente",
    "data": null
}
```

---

## Seguridad

### Datos de entrada

Nunca confiar en superglobales. Todo dato pasa por `Request.php`:
```
❌ $_GET, $_POST, $_FILES, $_COOKIE
✅ Request::query(), Request::post(), Request::file(), Request::cookie()
```

### Ejecución de comandos

El único archivo autorizado para ejecutar comandos del sistema (`exec()`, `shell_exec()`, `proc_open()`) es:

**`EdgeTTSService.php`**

Ningún otro archivo podrá ejecutar comandos del sistema.

---

## Servicios de TTS

El servicio activo es `EdgeTTSService.php`. La arquitectura permite futuras implementaciones:

- `EdgeTTSService.php` (activo)
- `OpenAITTSService.php` (futuro)
- `AzureTTSService.php` (futuro)
- `GoogleTTSService.php` (futuro)

`AudioService` decidirá cuál utilizar según la configuración del proyecto.

---

## Logging

Todos los errores se registran en:

```
storage/logs/
```

Nunca hacer:
```php
echo $e->getMessage();
```

---

## Idioma

- **Código:** inglés (`ProjectService`, `createProject()`, `AudioService`)
- **Interfaz:** español

---

## Versionado

| Versión | Descripción |
|---|---|
| v0.1 | Arquitectura base |
| v0.2 | CRUD |
| v0.3 | TTS |
| v0.4 | Exportación |
| v0.5 | Editor |
| v1.0 | Primera versión estable |

---

## Convención para commits

Narrador Studio utiliza **Conventional Commits** para mantener un historial claro, facilitar la revisión del código y simplificar la generación de versiones. Un commit = Un objetivo.

| Tipo      | Uso                                               |
|-----------|---------------------------------------------------|
| feat      | Nueva funcionalidad                               |
| fix       | Corrección de errores                             |
| refactor  | Reestructuración sin cambiar el comportamiento    |
| docs      | Documentación                                     |
| style     | Cambios de formato o estilo                       |
| test      | Pruebas                                           |
| chore     | Tareas de mantenimiento                           |

### Ejemplos

docs: add architecture specification
docs: add development iteration tracker
feat(core): implement autoloader
feat(core): implement env loader
feat(core): implement configuration loader

## Compromiso

El README es la fuente de verdad. Cada decisión arquitectónica se registra aquí primero y luego se implementa. Esto mantiene consistencia entre el diseño y el código a medida que el proyecto crece.

Lo suficientemente simple para comprenderlo en una tarde. Lo suficientemente sólido para construir productos durante años.