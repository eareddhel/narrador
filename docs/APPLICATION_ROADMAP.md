# Application Roadmap

## M01 — Dashboard

**Estado:** En revisión

**Subfase:** M01.2 — Sistema Visual Base

**Objetivo:** establecer la identidad visual base de Narrador Studio sobre el Dashboard inicial, manteniendo el ciclo real Router, Controller, View y Response.

**Sistema visual implementado:** interfaz minimalista, profesional y orientada a producción de contenido, con header reutilizable, main estructurado, footer discreto, Bootstrap local, Bootstrap Icons locales y CSS propio limitado a identidad visual.

**Componentes conectados:**

- `public/index.php`
- `bootstrap.php`
- `config/routes.php`
- `App\Core\Router`
- `App\Core\Request`
- `App\Controllers\DashboardController`
- `App\Core\View`
- `App\Core\Response`
- `views/layout.php`
- `views/dashboard.php`
- `public/assets/css/app.css`

**Criterio de cierre:** `GET /` responde 200 y renderiza el Dashboard mediante Router, Controller, View y Response reales con el sistema visual base aplicado.

## M02 — Projects

**Estado:** En revisión

**Subfase:** M02.1 — Dominio y persistencia

**Objetivo:** implementar la capa minima de dominio y persistencia de Projects sin rutas, Controllers, formularios ni vistas.

**Sistema implementado:** esquema reproducible de `projects`, Model orientado a datos, Service con reglas de aplicacion, UUID publico, archivo logico y pruebas con SQLite en memoria.

**Componentes conectados:**

- `database/schema.sql`
- `App\Models\Project`
- `App\Services\ProjectService`
- `App\Core\Database`
- `tools/test-projects.php`

**Criterio de cierre:** esquema reproducible; Model funcional; Service funcional; creacion, consulta, listado, actualizacion y archivo probados.

### M02.1 — Dominio y persistencia
- Objetivo: Implementar el esquema, Model y Service del módulo Projects, con UUID público, validación, operaciones CRUD internas y archivo lógico.
- Estado: ✅ Completada
- Criterios cumplidos:
  - Esquema MySQL aplicado.
  - Model funcional.
  - Service funcional.
  - Creación, consulta, listado, actualización y archivo probados.
  - ID interno no expuesto.