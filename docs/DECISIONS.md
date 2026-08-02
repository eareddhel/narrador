# Decisiones arquitectónicas

Registro de decisiones de diseño del proyecto. Las decisiones arquitectónicas formales se documentan como ADRs en `docs/adr/`. Este archivo funciona como índice general y mantiene también decisiones históricas aceptadas que no pertenecen al Core.

---

## ADRs formales del Core

| ADR | Estado | Decisión |
|---|---|---|
| [ADR-001](adr/001-core-architecture.md) | Aceptado | Arquitectura del Core y separación de capas |
| [ADR-002](adr/002-global-services.md) | Aceptado | Env y Config como servicios globales estáticos |
| [ADR-003](adr/003-http-request-response-objects.md) | Aceptado | Request y Response como objetos por petición HTTP |
| [ADR-004](adr/004-modular-configuration.md) | Aceptado | Configuración modular accedida exclusivamente mediante Config |
| [ADR-005](adr/005-custom-core-exceptions.md) | Aceptado | Jerarquía de excepciones con CoreException como clase base |
| [ADR-006](adr/006-explicit-router-and-invokable-controllers.md) | Aceptado | Router explícito y Controllers invocables |

---

## Decisiones históricas aceptadas

### DEC-001: No usar Composer inicialmente

**Estado:** Aceptada

**Contexto:** El proyecto es un microframework propio con dependencias mínimas.

**Decisión:** No se utiliza Composer. Las dependencias se manejan manualmente o se incorporarán cuando sea estrictamente necesario.

**Consecuencias:** Mayor control sobre el código, pero se pierde el ecosistema de paquetes. Se mantiene la posibilidad de integrar Composer en el futuro.

---

### DEC-002: Todo TTS se implementa mediante Services

**Estado:** Aceptada

**Contexto:** El proyecto necesita síntesis de voz (TTS) y podría necesitar múltiples proveedores en el futuro.

**Decisión:** Toda la lógica de TTS vive exclusivamente en la capa `Services/`. El controlador nunca ejecuta comandos de sistema directamente.

**Consecuencias:** Separación clara de responsabilidades. Fácil de cambiar o añadir proveedores (Edge TTS, OpenAI, Azure, Google) sin tocar controladores.

---

### DEC-003: Bootstrap local, sin CDN

**Estado:** Aceptada

**Contexto:** El proyecto se usa en entornos con acceso restringido a Internet (redes escolares).

**Decisión:** Bootstrap 5.3.3 se almacena localmente en `public/assets/vendor/`. No se utilizan CDN en producción.

**Consecuencias:** Funcionamiento sin conexión. Mayor velocidad de carga. Control total sobre versiones. Compatibilidad con redes que bloquean CDNs.

---

### DEC-004: JavaScript Vanilla, sin frameworks

**Estado:** Aceptada

**Contexto:** La interfaz no requiere complejidad de framework frontend.

**Decisión:** JavaScript vanilla (ES6+). Sin jQuery, Vue, React, Angular ni Alpine. Las solicitudes asíncronas se hacen con `fetch()`.

**Consecuencias:** Código más ligero, sin dependencias de runtime. Mantenimiento más simple. Sin curva de aprendizaje de framework.

---

### DEC-005: Los audios se almacenan en storage/audio

**Estado:** Aceptada

**Contexto:** Los archivos de audio generados no deben ser accesibles directamente por URL.

**Decisión:** Los audios se guardan en `storage/audio/`, fuera de `public/`. Solo son accesibles mediante controladores que validan permisos.

**Consecuencias:** Mayor seguridad. Los archivos no se sirven directamente por Apache. Se necesita un endpoint para descargar o reproducir audios.
---

### DEC-006: Router explícito y Controllers invocables

**Estado:** Aceptada

**Contexto:** La Iteración 008 implementará el coordinador final del ciclo HTTP.

**Decisión:** Router será un objeto que registra rutas explícitas, captura `Request::capture()`, resuelve método y URI, invoca Controllers invocables de una sola acción, recibe objetos Response y ejecuta `Response::send()`. No habrá reflexión, autodetección de rutas, attributes, annotations ni middleware en esta iteración.

**Consecuencias:** El flujo HTTP queda explícito y predecible. Las rutas tienen un origen claro en `config/routes.php`. Los Controllers se mantienen pequeños y orientados a una acción.

---

### DEC-007: Parámetros de ruta dentro de Request

**Estado:** Aceptada

**Contexto:** Router resolverá rutas parametrizadas durante la Iteración 008.

**Decisión:** Toda la información de la petición HTTP se representa mediante Request. Los parámetros de ruta resueltos por Router forman parte de la petición y no se entregan como argumentos independientes a los Controllers. Request mantiene una estrategia inmutable: Router utilizará `withRouteParameters(array $parameters): self`, que no modifica la instancia original y devuelve una nueva instancia enriquecida.

**Consecuencias:** Los Controllers mantienen una única entrada (`Request`) y consultan parámetros de ruta mediante la API de Request. La firma aprobada continúa siendo `__invoke(Request $request): Response`. La decisión adopta una filosofía inspirada en PSR-7, sin implementar PSR-7 completo, y descarta mutadores como `setRouteParameters()`.