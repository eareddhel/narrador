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
            ├─ Router
            ├─ Controller
            │    └─ Service
            │         └─ Model
            └─ View
```

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
│   │   └── View.php
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
│   └── config.php
├── database/
│   └── schema.sql
├── docs/
│   ├── ARCHITECTURE.md
│   ├── DATABASE.md
│   ├── DECISIONS.md
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
├── tools/
│   ├── update-bootstrap.bat
│   ├── update-icons.bat
│   ├── clear-cache.php
│   └── clear-audio.php
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
├── CHANGELOG.md
├── README.md
└── bootstrap.php
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

---

## Componentes del Core

### Autoloader (`app/Core/Autoloader.php`)
Carga automática de clases bajo el namespace `App\`.

### Env (`app/Core/Env.php`)
Lee automáticamente el archivo `.env`. Uso:
```php
Env::get('DB_HOST');
```
Nunca acceder a `$_ENV` directamente.

### Database (`app/Core/Database.php`)
Conexión PDO con patrón Singleton.

### Router (`app/Core/Router.php`)
Soporte para rutas:
- `GET /`
- `GET /project`
- `GET /project/{uuid}`
- `POST /project/create`
- `POST /audio/generate`

### Request (`app/Core/Request.php`)
Acceso a datos de entrada sin usar superglobales:
```php
Request::post('nombre');
Request::get('id');
```

### Response (`app/Core/Response.php`)
```php
Response::json($datos);
Response::redirect('/');
```

### View (`app/Core/View.php`)
Renderizado de plantillas.

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

Toda configuración va en `.env`. Nunca en el código:
```
❌ $host = "localhost";
✅ DB_HOST=localhost  (en .env)
```

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
✅ Request::get(), Request::post()
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

## Compromiso

El README es la fuente de verdad. Cada decisión arquitectónica se registra aquí primero y luego se implementa. Esto mantiene consistencia entre el diseño y el código a medida que el proyecto crece.
