# Application Roadmap

## M01 — Dashboard

**Estado:** En revisión

**Subfase:** M01.1 — Integración mínima

**Objetivo:** conectar el primer flujo funcional de la aplicación para que `GET /` renderice el Dashboard mediante componentes reales del Core.

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

**Criterio de cierre:** `GET /` responde 200 y renderiza el Dashboard mediante Router, Controller, View y Response reales.
