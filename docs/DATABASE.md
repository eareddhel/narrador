# Database

Documentacion del esquema de datos de Narrador Studio.

## Principios

- Todas las tablas usan `id` como clave primaria interna.
- Las APIs publicas y URLs usan `uuid`.
- Los Controllers no ejecutan SQL.
- Los Services contienen reglas de aplicacion.
- Los Models contienen acceso a datos mediante `App\Core\Database`.
- `Database` encapsula PDO y ejecuta consultas preparadas.

## Tabla `projects`

### Proposito

`projects` almacena los proyectos de narracion creados por la aplicacion. Un proyecto agrupa guiones, futuras secciones, narraciones y exportaciones.

La tabla implementa la persistencia minima de M02.1: creacion, consulta por UUID, listado activo, actualizacion de nombre/descripcion y archivo logico.

### Columnas

| Columna | Tipo | Nulo | Responsabilidad |
|---|---|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT` | No | Clave primaria interna. Nunca se expone en APIs publicas ni URLs. |
| `uuid` | `CHAR(36)` | No | Identificador publico del proyecto. |
| `name` | `VARCHAR(150)` | No | Nombre visible del proyecto. No acepta cadenas vacias desde la capa de aplicacion. |
| `description` | `TEXT` | Si | Descripcion breve opcional. Cadenas vacias se normalizan a `null`. |
| `status` | `VARCHAR(20)` | No | Estado simple del proyecto. |
| `created_at` | `DATETIME` | No | Fecha de creacion generada por la aplicacion. |
| `updated_at` | `DATETIME` | No | Fecha de ultima actualizacion generada por la aplicacion. |
| `archived_at` | `DATETIME` | Si | Fecha de archivo logico. Permanece `null` mientras el proyecto no este archivado. |

### Indices

| Indice | Columnas | Proposito |
|---|---|---|
| `PRIMARY KEY` | `id` | Identificador interno eficiente. |
| `uq_projects_uuid` | `uuid` | Garantizar UUID publico unico. |
| `idx_projects_status` | `status` | Filtrar proyectos activos o archivados. |
| `idx_projects_created_at` | `created_at` | Orden cronologico para listar proyectos recientes. |

### Estados

Estados iniciales permitidos:

- `draft`
- `active`
- `archived`

M02.1 no incorpora workflow complejo. La creacion inicia en `draft`; el archivo cambia el estado a `archived`.

### ID interno y UUID publico

`id` existe solo para la base de datos. No se devuelve desde `App\Models\Project`, no debe llegar a Services, Controllers, vistas ni URLs.

`uuid` es el identificador publico. Se genera en `App\Services\ProjectService` mediante UUID v4 con `random_bytes()` y se almacena como `CHAR(36)` para mantener portabilidad y legibilidad.

### Archivo logico

Archivar un proyecto no elimina la fila. La operacion establece:

- `status = 'archived'`
- `archived_at = fecha actual`
- `updated_at = fecha actual`

Los proyectos archivados no aparecen en `Project::allActive()`, pero siguen existiendo para consulta directa por UUID y para futuras decisiones de restauracion o historial.

`ProjectService::archive()` lanza `InvalidArgumentException` cuando el UUID esta vacio o no existe.

### Campos no incorporados deliberadamente

M02.1 no agrega:

- `user_id`
- equipos
- permisos
- categorias
- tags
- plantillas
- relaciones con secciones
- soft delete generico
- auditoria compleja
- versionado
- multiusuario

Narrador Studio sigue funcionando como aplicacion de un unico usuario. Autenticacion y permisos no se anticipan en esta etapa.

### Relacion futura con Sections

Sections se relacionara con Projects en una fase posterior. Esa relacion no se documenta como implementada todavia para evitar anticipar tablas o claves foraneas sin una decision de dominio completa.
