# Revisión Arquitectónica

Fecha: 2026-08-02

Versión: Core Foundation Review 001

Autor: Revisión arquitectónica asistida

---

## Resumen

Esta revisión documenta el estado del Core de Narrador Studio al cierre de la fase Core Foundation. El Core tiene como propósito ofrecer un microframework propio en PHP puro para inicialización, configuración, ciclo HTTP, vistas, persistencia, enrutamiento y manejo común de errores.

La filosofía documentada privilegia arquitectura, consistencia, mantenibilidad, simplicidad y ausencia de dependencias externas innecesarias. El estado del workspace muestra los componentes principales del Core implementados: Autoloader, Env, Config, Request, Response, View, Database y Router. La revisión sirve como fotografía técnica de referencia antes de iniciar el desarrollo funcional de la aplicación.

La evidencia utilizada proviene de `README.md`, `CHANGELOG.md`, `docs/ROADMAP.md`, `docs/DECISIONS.md`, `docs/ITERATIONS.md`, los ADR de `docs/adr/`, la documentación técnica de `docs/core/`, las implementaciones en `app/Core/` y las herramientas en `tools/`.

---

## Estado del Core

Autoloader

- Responsabilidad: registrar carga automática de clases bajo el namespace `App\` y mapearlo al directorio `app/`.
- Naturaleza: objeto mutable durante `register()`, porque almacena el directorio base.
- Estado: implementado en `app/Core/Autoloader.php`.
- Observaciones: la implementación usa `spl_autoload_register()` y no depende de Composer. El documento técnico `docs/core/AUTOLOADER.md` existe pero está vacío.

Env

- Responsabilidad: cargar `.env` una sola vez y exponer variables mediante API estática tipada.
- Naturaleza: servicio global estático con caché interna.
- Estado: implementado en `app/Core/Env.php`.
- Observaciones: soporta lectura básica de pares clave-valor, valores entre comillas y métodos `get`, `has`, `all`, `getString`, `getInt`, `getFloat` y `getBool`. El documento técnico `docs/core/ENV.md` existe pero está vacío.

Config

- Responsabilidad: cargar archivos de `config/` por dominio y exponer valores mediante notación por puntos.
- Naturaleza: servicio global estático con estado de carga único.
- Estado: implementado en `app/Core/Config.php`.
- Observaciones: consume archivos PHP de configuración y no requiere instancias por petición. El documento técnico `docs/core/CONFIG.md` existe pero está vacío.

Request

- Responsabilidad: representar una petición HTTP capturada, incluyendo query string, POST, archivos, cookies, server, cabeceras derivadas y parámetros de ruta.
- Naturaleza: objeto inmutable. `withRouteParameters()` clona la instancia y devuelve una nueva instancia enriquecida.
- Estado: implementado en `app/Core/Request.php`.
- Observaciones: captura superglobales una sola vez mediante `Request::capture()`. Expone API explícita sin método `get()`. La documentación técnica describe inspiración PSR-7 para métodos `with*` sin implementar PSR-7 completo.

Response

- Responsabilidad: representar una respuesta HTTP con status, headers y body.
- Naturaleza: objeto mutable con API fluida.
- Estado: implementado en `app/Core/Response.php`.
- Observaciones: soporta `status()`, `header()`, `body()`, `json()`, `redirect()`, `download()`, `send()` y getters para pruebas. No depende de Request.

View

- Responsabilidad: renderizar plantillas PHP desde el directorio de vistas y devolver un objeto Response HTML.
- Naturaleza: objeto configurado por constructor; no mantiene estado HTTP propio.
- Estado: implementado en `app/Core/View.php`.
- Observaciones: valida nombres de vistas, evita rutas absolutas y traversal, usa output buffering y relanza errores tras limpiar buffers. No envía respuestas.

Database

- Responsabilidad: encapsular PDO y ejecutar consultas preparadas, escrituras y transacciones.
- Naturaleza: objeto con conexión PDO privada por instancia.
- Estado: implementado en `app/Core/Database.php`.
- Observaciones: no es Singleton, no es ORM, no es Query Builder y no expone PDO públicamente. Transforma `PDOException` en `DatabaseException`. La factoría `fromPdo()` usa `ReflectionClass` para construir una instancia de prueba sin ejecutar el constructor.

Router

- Responsabilidad: coordinar el ciclo HTTP, registrar rutas explícitas, resolver matching, enriquecer Request, invocar handlers y convertir errores en Response.
- Naturaleza: objeto mutable durante el registro de rutas; sin estado estático.
- Estado: implementado en `app/Core/Router.php` en el workspace actual.
- Observaciones: soporta GET y POST, parámetros nombrados simples, callables, objetos invocables, class-strings invocables y resolver opcional de Controllers. `dispatch()` devuelve Response sin enviarla; `run()` captura Request y ejecuta `send()` una vez.

Flujo HTTP documentado y estado observable

```text
Apache
  -> public/index.php
  -> bootstrap.php
  -> Autoloader
  -> Env
  -> Config
  -> Router
  -> Request
  -> Controller
  -> Service
  -> Model
  -> Database
  -> View
  -> Response
  -> Browser
```

Responsabilidad por etapa:

- Apache: entrega la petición al front controller.
- `public/index.php`: punto público esperado de entrada. En el workspace actual está vacío.
- `bootstrap.php`: define `ROOT_PATH`, registra Autoloader, carga Env y carga Config.
- Autoloader: resuelve clases bajo `App\`.
- Env: carga variables de entorno.
- Config: carga configuración modular.
- Router: coordina petición, rutas, handlers, errores y envío final.
- Request: encapsula datos de entrada y parámetros de ruta.
- Controller: recibe Request y devuelve Response.
- Service: concentra lógica de negocio.
- Model: concentra acceso a datos.
- Database: encapsula PDO y prepared statements.
- View: renderiza HTML y devuelve Response.
- Response: contiene status, headers y body; `send()` envía al navegador.
- Browser: recibe la respuesta HTTP.

---

## Estado de la aplicación

Iteraciones registradas en `docs/ITERATIONS.md`:

- 001 - Core / Autoloader: completada.
- 002 - Core / Env: completada.
- 003 - Core / Config: completada.
- 004 - Core / Response: completada.
- 005 - Core / Request: completada.
- 006 - Core / View: completada.
- 007 - Core / Database: completada.

El archivo de iteraciones no registra todavía Iteración 008. Sin embargo, el workspace actual contiene implementación de `app/Core/Router.php`, documentación técnica de Router actualizada y `tools/test-router.php`.

ADR aprobados presentes:

- ADR-001: Arquitectura del Core y separación de capas. Estado: Aceptado.
- ADR-002: Env y Config como servicios globales estáticos. Estado: Aceptado.
- ADR-003: Request y Response como objetos por petición HTTP. Estado: Aceptado.
- ADR-004: Configuración modular accedida exclusivamente mediante Config. Estado: Aceptado.
- ADR-005: Jerarquía de excepciones con CoreException como clase base. Estado: Aceptado.
- ADR-006: Router explícito y Controllers invocables. Estado: Aceptado.

Decisiones arquitectónicas principales sintetizadas:

- Narrador Studio usa un microframework propio en PHP puro.
- El README actúa como fuente oficial de verdad arquitectónica.
- Las decisiones permanentes se documentan mediante ADR.
- El flujo HTTP es coordinado por Router, no por bootstrap.
- La separación de capas principal es Controller -> Service -> Model.
- Controllers devuelven Response y no ejecutan `echo`, `header()`, `exit()` ni `die()`.
- Env y Config son servicios globales estáticos.
- Request y Response son objetos del ciclo HTTP.
- Request es inmutable para la aplicación y se enriquece mediante `withRouteParameters()`.
- Response es mutable y usa API fluida.
- View es un renderizador desacoplado que devuelve Response.
- Database encapsula PDO por instancia, sin Singleton y sin exponer PDO públicamente.
- SQL se ejecuta mediante prepared statements dentro de Database.
- Router usa rutas explícitas, Controllers invocables y no implementa middleware ni autodetección.
- No se incorpora ORM ni Query Builder.

Documentación existente:

- `docs/adr/`: 6 ADR numerados y un índice.
- `docs/core/`: 10 documentos Markdown de Core, incluyendo README técnico.
- Documentos técnicos con contenido: `DATABASE.md`, `EXCEPTIONS.md`, `README.md`, `REQUEST.md`, `RESPONSE.md`, `ROUTER.md`, `VIEW.md`.
- Documentos técnicos existentes pero vacíos: `AUTOLOADER.md`, `CONFIG.md`, `ENV.md`.

Herramientas de prueba observables en `tools/`:

- Scripts con contenido: `test-database.php`, `test-request.php`, `test-router.php`, `test-view.php`.
- Scripts presentes sin contenido: `test-config.php`, `test-env.php`, `test-response.php`, `clear-audio.php`, `clear-cache.php`.
- Scripts batch presentes sin contenido: `update-bootstrap.bat`, `update-icons.bat`.

Métricas verificadas en el repositorio:

- Iteraciones completadas registradas: 7.
- ADR formales: 6.
- Componentes principales del Core implementados como archivos PHP: 8.
- Excepciones del Core implementadas: 6.
- Documentos técnicos en `docs/core/`: 10.
- Documentos técnicos de Core vacíos: 3.
- Archivos en `tools/`: 11.
- Herramientas de prueba con contenido observable: 4.

Estado general del Core:

El Core Foundation está implementado a nivel de componentes principales en el workspace actual. La integración pública completa entre `public/index.php`, bootstrap, Router y rutas de aplicación no se observa en `public/index.php`, que se encuentra vacío.

---

## Fortalezas

Cohesión:

- Los componentes del Core tienen responsabilidades acotadas: carga de clases, entorno, configuración, petición, respuesta, vista, base de datos y enrutamiento.
- Database concentra la interacción con PDO y transforma errores PDO en `DatabaseException`.
- View se limita a renderizar plantillas y devolver Response.

Acoplamiento:

- Request no depende de otros componentes del Core.
- Response no depende de Request ni de Config.
- View depende solo de Config, Response y ViewNotFoundException.
- Router depende de Request, Response, Config y excepciones del Core, coherente con su rol de límite HTTP.

Separación de responsabilidades:

- La documentación y ADR establecen que Controllers no acceden directamente a Database y que Services contienen la lógica de negocio.
- El Core diferencia servicios globales estáticos de objetos del ciclo HTTP.
- `dispatch()` y `run()` separan pruebas de enrutamiento y envío final.

Consistencia:

- Los nombres de clases y namespaces observados siguen `App\Core` y `App\Core\Exceptions`.
- Las excepciones específicas extienden `CoreException`, y `CoreException` extiende `RuntimeException`.
- La estrategia de Request inmutable está documentada e implementada.

Documentación:

- Existen README, ROADMAP, DECISIONS, ITERATIONS, ADR y documentación técnica del Core.
- Las decisiones relevantes del Core están registradas en ADR.
- Router, Request, Response, View, Database y Exceptions tienen documentación técnica con contenido.

Pruebas:

- Existen herramientas CLI con contenido para Database, Request, Router y View.
- Request y Router incluyen pruebas específicas para comportamiento HTTP y parámetros de ruta.

Mantenibilidad:

- La arquitectura favorece componentes pequeños y sustituibles.
- No se observan dependencias externas nuevas ni Composer obligatorio.
- Las rutas de Router son explícitas y no dependen de escaneo de directorios.

---

## Debilidades

- `public/index.php` existe pero está vacío. El flujo oficial documenta `Apache -> public/index.php -> bootstrap.php`, pero no existe integración observable en el front controller.
- `docs/ITERATIONS.md` registra hasta Iteración 007, mientras el workspace actual contiene Router implementado y probado.
- `docs/core/AUTOLOADER.md`, `docs/core/ENV.md` y `docs/core/CONFIG.md` existen pero no contienen documentación técnica.
- Algunos scripts de `tools/` existen con longitud cero, incluyendo pruebas para Config, Env y Response.
- `CHANGELOG.md` no contiene información observable en el estado revisado.
- `docs/core/README.md` marca View, Database y Router como pendientes, mientras sus implementaciones y documentación técnica existen en el workspace actual.

---

## Riesgos técnicos

- La ausencia de integración en `public/index.php` impide verificar desde el repositorio que el ciclo HTTP documentado esté conectado de extremo a extremo bajo Apache.
- La diferencia entre `docs/ITERATIONS.md` y el estado actual de Router puede generar ambigüedad histórica sobre si la Iteración 008 está cerrada formalmente.
- Los documentos técnicos vacíos para Autoloader, Env y Config reducen trazabilidad para componentes ya implementados.
- Los scripts de prueba vacíos para Config, Env y Response limitan la evidencia automatizada disponible para esos componentes.
- `Database::fromPdo()` utiliza `ReflectionClass` para construir instancias de prueba sin constructor. Este uso está localizado en Database y no contradice la prohibición específica de Reflection en Router, pero debe permanecer acotado para no extender esa práctica a la resolución de Controllers o rutas.

Riesgos mitigados:

- El acceso directo a PDO queda mitigado por encapsulación privada en Database.
- La exposición de detalles técnicos en errores HTTP queda mitigada por el manejo seguro de Router en producción.
- La mutación accidental de Request queda mitigada por `withRouteParameters()` basado en clonación.

---

## Deuda técnica

- Falta documentación técnica efectiva en `docs/core/AUTOLOADER.md`, `docs/core/ENV.md` y `docs/core/CONFIG.md`.
- Falta contenido en herramientas existentes: `test-config.php`, `test-env.php`, `test-response.php`, `clear-audio.php`, `clear-cache.php`, `update-bootstrap.bat` y `update-icons.bat`.
- Falta registro formal de Iteración 008 en `docs/ITERATIONS.md`, si Router se considera parte cerrada de Core Foundation.
- Falta integración observable del front controller público con bootstrap y Router.
- `CHANGELOG.md` está vacío, por lo que no aporta trazabilidad histórica desde ese archivo.

No se considera deuda técnica la ausencia de middleware, ORM, Query Builder, autowiring, rutas nombradas, grupos de rutas, generación inversa de URL ni métodos HTTP PUT/PATCH/DELETE, porque aparecen documentados como decisiones conscientes o mejoras futuras sujetas a necesidad real.

---

## Recomendaciones

- Completar la documentación técnica vacía de Autoloader, Env y Config usando el mismo formato de `docs/core/REQUEST.md`, `RESPONSE.md`, `VIEW.md`, `DATABASE.md` y `ROUTER.md`.
- Registrar formalmente el cierre de Iteración 008 en `docs/ITERATIONS.md` cuando el equipo confirme que Router está aceptado.
- Integrar `public/index.php` con bootstrap y Router en una iteración específica, manteniendo la separación ya documentada.
- Añadir contenido o eliminar del plan activo los scripts vacíos de `tools/`, para evitar que aparenten cobertura inexistente.
- Mantener `Database::fromPdo()` como excepción localizada para pruebas y no trasladar Reflection a Router, Controllers o resolución de dependencias.
- Ejecutar las pruebas CLI existentes antes de iniciar desarrollo funcional sobre el Core.

---

## Próximas acciones

La fase Core Foundation queda documentada como finalizada a nivel de componentes principales del Core en el workspace actual. El siguiente paso arquitectónico es iniciar el desarrollo funcional de Narrador Studio sobre este Core, conectando flujos reales de aplicación, Controllers, Services, Models, Views y rutas explícitas.

El Core debe considerarse estable para comenzar desarrollo funcional, con las inconsistencias documentales y de integración señaladas en esta revisión como elementos a resolver de forma explícita. Futuras ampliaciones del Core deberán justificarse por necesidades reales detectadas en la aplicación y evitar crecimiento especulativo.

No se recomiendan nuevas abstracciones de Core sin evidencia de uso desde funcionalidades concretas.