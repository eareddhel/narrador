# Decisiones arquitectónicas

Registro de decisiones de diseño del proyecto (Architecture Decision Records — ADR). Cada documento explica por qué se tomó una decisión, no solo cómo está implementado.

---

## ADR-001: No usar Composer inicialmente

**Estado:** Aceptada

**Contexto:** El proyecto es un microframework propio con dependencias mínimas.

**Decisión:** No se utiliza Composer. Las dependencias se manejan manualmente o se incorporarán cuando sea estrictamente necesario.

**Consecuencias:** Mayor control sobre el código, pero se pierde el ecosistema de paquetes. Se mantiene la posibilidad de integrar Composer en el futuro.

---

## ADR-002: Todo TTS se implementa mediante Services

**Estado:** Aceptada

**Contexto:** El proyecto necesita síntesis de voz (TTS) y podría necesitar múltiples proveedores en el futuro.

**Decisión:** Toda la lógica de TTS vive exclusivamente en la capa `Services/`. El controlador nunca ejecuta comandos de sistema directamente.

**Consecuencias:** Separación clara de responsabilidades. Fácil de cambiar o añadir proveedores (Edge TTS, OpenAI, Azure, Google) sin tocar controladores.

---

## ADR-003: Bootstrap local, sin CDN

**Estado:** Aceptada

**Contexto:** El proyecto se usa en entornos con acceso restringido a Internet (redes escolares).

**Decisión:** Bootstrap 5.3.3 se almacena localmente en `public/assets/vendor/`. No se utilizan CDN en producción.

**Consecuencias:** Funcionamiento sin conexión. Mayor velocidad de carga. Control total sobre versiones. Compatibilidad con redes que bloquean CDNs.

---

## ADR-004: JavaScript Vanilla, sin frameworks

**Estado:** Aceptada

**Contexto:** La interfaz no requiere complejidad de framework frontend.

**Decisión:** JavaScript vanilla (ES6+). Sin jQuery, Vue, React, Angular ni Alpine. Las solicitudes asíncronas se hacen con `fetch()`.

**Consecuencias:** Código más ligero, sin dependencias de runtime. Mantenimiento más simple. Sin curva de aprendizaje de framework.

---

## ADR-005: Los audios se almacenan en storage/audio

**Estado:** Aceptada

**Contexto:** Los archivos de audio generados no deben ser accesibles directamente por URL.

**Decisión:** Los audios se guardan en `storage/audio/`, fuera de `public/`. Solo son accesibles mediante controladores que validan permisos.

**Consecuencias:** Mayor seguridad. Los archivos no se sirven directamente por Apache. Se necesita un endpoint para descargar o reproducir audios.
