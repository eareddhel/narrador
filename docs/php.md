# PHP — Filosofía del framework

## Microframework propio

Narrador Studio no utiliza un framework externo. Escribe su propio microframework: pequeño, elegante y específico para el proyecto.

No se busca un framework enorme ni generalista. Se busca algo que se lea así de limpio:

```php
Router::get('/', DashboardController::class);

Router::post('/project/create', ProjectController::class);

Router::post('/audio/generate', AudioController::class);
```

Y dentro de cada controlador:

```php
return View::render(
    'dashboard',
    [
        'projects' => $projects
    ]
);
```

Que todo se lea así de limpio es el objetivo.

## Principios

- **Código explícito** — nada de magia oculta, nada de convenciones mágicas.
- **Flujo lineal** — Apache → index → Bootstrap → Router → Controller → Service → Model → View.
- **Separación real** — cada capa hace una sola cosa.
- **Dependencias mínimas** — solo lo necesario, nada de más.
