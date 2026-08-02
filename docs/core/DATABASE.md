# Database

## Responsabilidad

Database es el componente del Core responsable de encapsular PDO y ofrecer una API pequeña para consultas, escrituras y transacciones.

Database no es un ORM, no es Active Record y no es un Query Builder. Su responsabilidad termina en ejecutar SQL preparado y transformar errores de infraestructura en `DatabaseException`.

## Filosofía de diseño

Database es un objeto. Cada instancia encapsula su propia conexión PDO.

No utiliza Singleton, no mantiene estado estático compartido y no expone la conexión PDO a capas superiores. Esto permite crear instancias aisladas para pruebas, conexiones explícitas por contexto y un límite claro entre Models y la infraestructura de persistencia.

## Ciclo de vida

1. Un Model recibe o crea una instancia de Database.
2. Database resuelve configuración desde `Config` o recibe una configuración explícita.
3. Database crea una conexión PDO privada.
4. El Model ejecuta consultas mediante métodos públicos de Database.
5. Database prepara, enlaza y ejecuta cada consulta.
6. Database devuelve arrays asociativos, conteos de filas o identificadores.
7. Si ocurre un error PDO, Database lanza `DatabaseException`.

## API pública

### Constructor

```php
public function __construct(?array $config = null)
```

Si `$config` es `null`, Database obtiene la configuración mediante `Config` usando las claves reales de `config/database.php`.

Si se entrega `$config`, Database la utiliza directamente.

### Factoría para pruebas

```php
public static function fromPdo(PDO $pdo): self
```

Permite inyectar una conexión PDO ya creada, por ejemplo SQLite en memoria para pruebas. Esta factoría configura la conexión con:

- `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
- `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`

`fromPdo()` no convierte Database en un servicio estático. Solo construye una instancia con una conexión explícita.

### Consultas

```php
public function select(string $sql, array $parameters = []): array
```

Ejecuta una consulta preparada y devuelve todas las filas como arrays asociativos.

```php
public function selectOne(string $sql, array $parameters = []): ?array
```

Devuelve una fila asociativa o `null` cuando no hay resultados.

### Escrituras

```php
public function statement(string $sql, array $parameters = []): int
```

Ejecuta una sentencia preparada y devuelve el número de filas afectadas.

```php
public function insert(string $sql, array $parameters = []): string
```

Ejecuta una inserción y devuelve `lastInsertId()` como string.

```php
public function update(string $sql, array $parameters = []): int
```

Reutiliza internamente `statement()`.

```php
public function delete(string $sql, array $parameters = []): int
```

Reutiliza internamente `statement()`.

### Transacciones

```php
public function transaction(callable $callback): mixed
```

Ejecuta operaciones relacionadas dentro de una transacción. El callback recibe la instancia actual de Database.

```php
public function inTransaction(): bool
```

Informa si PDO está actualmente dentro de una transacción, sin exponer la conexión.

## Flujo interno

Database prepara y ejecuta cada operación mediante:

```php
$statement = $this->pdo->prepare($sql);
$statement->execute($parameters);
```

Los resultados se obtienen con fetch mode asociativo por defecto.

## Configuración

La configuración canónica vive en `config/database.php` y se consume mediante `App\Core\Config`.

Claves reales utilizadas:

- `database.host`
- `database.port`
- `database.database`
- `database.username`
- `database.password`

Para MySQL/MariaDB, Database construye internamente un DSN con:

```text
mysql:host=...;port=...;dbname=...;charset=utf8mb4
```

Database no imprime, registra ni expone credenciales.

## Prepared statements

Todas las operaciones SQL utilizan prepared statements.

No se utiliza:

- `PDO::query()`;
- SQL concatenado con datos de usuario;
- interpolación manual de parámetros;
- escape SQL artesanal.

Los parámetros nombrados pueden entregarse con `:` o sin `:`. Database normaliza parámetros nombrados cuando es necesario y no altera parámetros posicionales.

## Transacciones

Uso esperado:

```php
$result = $database->transaction(
    function (Database $database): mixed {
        $database->insert(
            'INSERT INTO projects (name) VALUES (:name)',
            ['name' => 'Tutorial']
        );

        return true;
    }
);
```

Comportamiento:

1. Inicia la transacción.
2. Ejecuta el callback con la instancia actual.
3. Confirma si el callback finaliza correctamente.
4. Devuelve el resultado del callback.
5. Si ocurre un error, hace rollback cuando la transacción sigue activa.

Las excepciones ajenas a PDO conservan su tipo original. Los errores PDO se transforman en `DatabaseException`.

No hay soporte para transacciones anidadas en esta iteración.

## Manejo de excepciones

Database captura `PDOException` y la transforma en:

```php
App\Core\Exceptions\DatabaseException
```

La excepción original se conserva como excepción previa.

Database no oculta errores ni devuelve respuestas HTTP. Router gestionará `CoreException` en una iteración posterior.

## Relación con Models

El flujo de acceso a datos es:

```text
Controller
    -> Service
        -> Model
            -> Database
                -> PDO
                    -> MySQL/MariaDB
```

Reglas:

- Controllers nunca acceden directamente a Database.
- Services no ejecutan SQL.
- Models utilizan Database para persistencia y consultas.
- Database no conoce Controllers, Services ni Models.
- PDO permanece encapsulado dentro de Database.

## Ejemplos

### Select

```php
$projects = $database->select(
    'SELECT id, uuid, name FROM projects WHERE status = :status',
    ['status' => 'draft']
);
```

### Select one

```php
$project = $database->selectOne(
    'SELECT id, uuid, name FROM projects WHERE uuid = :uuid',
    ['uuid' => $uuid]
);
```

### Insert

```php
$id = $database->insert(
    'INSERT INTO projects (uuid, name) VALUES (:uuid, :name)',
    [
        'uuid' => $uuid,
        'name' => $name,
    ]
);
```

### Update

```php
$affectedRows = $database->update(
    'UPDATE projects SET name = :name WHERE uuid = :uuid',
    [
        'name' => $name,
        'uuid' => $uuid,
    ]
);
```

### Delete

```php
$affectedRows = $database->delete(
    'DELETE FROM sections WHERE project_uuid = :project_uuid',
    ['project_uuid' => $projectUuid]
);
```

## Decisiones arquitectónicas

1. **Objeto, no Singleton**: Cada instancia encapsula una conexión PDO propia.
2. **PDO privado**: La conexión no se expone mediante getters, propiedades públicas ni métodos mágicos.
3. **Prepared statements siempre**: Todas las operaciones pasan por `prepare()` y `execute()`.
4. **Errores PDO encapsulados**: Las capas superiores reciben `DatabaseException`, no `PDOException`.
5. **Excepciones no PDO preservadas**: Los callbacks de transacción relanzan excepciones de dominio sin alterar su tipo.
6. **Sin ORM**: Database no conoce entidades, relaciones ni reglas de negocio.
7. **fromPdo() controlado**: La única factoría estática existe para pruebas e inyección explícita.

## Límites del componente

- No implementa ORM.
- No implementa Query Builder.
- No implementa Active Record.
- No implementa repositorios.
- No ejecuta migraciones.
- No conoce el esquema de negocio.
- No hace paginación.
- No registra consultas.
- No implementa reconexión automática.
- No soporta múltiples conexiones ni réplicas.
- No soporta transacciones anidadas.
- No cachea resultados.
- No sanitiza datos ni valida reglas de negocio.

## Futuras mejoras

- Soporte explícito para transacciones anidadas mediante savepoints.
- Logging controlado de errores en Router o infraestructura dedicada.
- Métricas y profiling opcionales para desarrollo.
- Soporte para múltiples conexiones si aparece una necesidad real.
- Helpers pequeños para construir cláusulas comunes sin llegar a Query Builder.