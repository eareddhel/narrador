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
