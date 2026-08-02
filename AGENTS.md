# AGENTS.md

# Narrador Studio

Este documento define las reglas de trabajo para cualquier agente de IA que participe en el desarrollo del proyecto.

Estas reglas tienen prioridad sobre cualquier preferencia implícita del agente y buscan preservar la arquitectura, consistencia y mantenibilidad del sistema.

---

# Filosofía

Narrador Studio es una aplicación construida sobre un microframework propio desarrollado específicamente para este proyecto.

La prioridad absoluta es:

1. Arquitectura
2. Consistencia
3. Mantenibilidad
4. Simplicidad
5. Funcionalidad

Nunca sacrificar arquitectura por velocidad de implementación.

---

# Antes de modificar cualquier archivo

Leer siempre:

README.md

docs/ROADMAP.md

docs/DECISIONS.md

docs/ITERATIONS.md

docs/core/README.md

y la documentación específica del componente que será modificado.

Si existe un ADR relacionado, leer también:

docs/adr/

---

# Si detectas contradicciones

No corregir automáticamente.

Primero:

- informar la contradicción;
- explicar el impacto;
- proponer una solución.

Solo modificarla si la solicitud del usuario lo autoriza.

---

# Arquitectura

El README constituye la fuente oficial de verdad del proyecto.

Los ADR documentan decisiones arquitectónicas permanentes.

Las implementaciones deben ajustarse a dicha arquitectura.

Nunca modificar la arquitectura sin autorización explícita.

---

# Principios de diseño

Aplicar siempre:

- SOLID
- KISS
- DRY
- YAGNI
- PSR-12
- PSR-4

Priorizar clases pequeñas y con una única responsabilidad.

Evitar duplicación de lógica.

---

# Organización del Core

Servicios globales:

- Env
- Config

Características:

- API estática
- Sin estado HTTP
- Una única responsabilidad

Objetos HTTP:

- Request
- Response

Características:

- Objetos
- Inmutables cuando corresponda
- Sin métodos estáticos (excepto Request::capture())

---

# Flujo HTTP

Apache

↓

public/index.php

↓

bootstrap.php

↓

Autoloader

↓

Env

↓

Config

↓

Router

↓

Request::capture()

↓

Controller

↓

Service

↓

Model

↓

View

↓

Response

↓

Response::send()

El Router coordina el ciclo HTTP.

Los Controllers nunca realizan echo, exit() ni header().

Siempre devuelven un objeto Response.

---

# Excepciones

Todas las excepciones del Core extienden:

CoreException

CoreException extiende:

RuntimeException

No lanzar RuntimeException directamente cuando exista una excepción específica.

---

# Configuración

Ninguna clase puede leer directamente:

.env

ni archivos dentro de:

config/

Toda configuración debe obtenerse mediante:

Env::get()

Config::get()

---

# Base de datos

Los Controllers nunca acceden directamente a la base de datos.

Toda lógica de negocio pertenece a Services.

Los Models representan acceso a datos.

---

# JavaScript

Utilizar únicamente:

- JavaScript ES6+
- Fetch API

No incorporar:

- jQuery
- Vue
- React
- Angular
- Alpine

salvo autorización expresa.

---

# Dependencias

No añadir librerías externas.

No añadir Composer.

No modificar vendor.

Toda nueva dependencia requiere aprobación explícita.

---

# Commits

Separar responsabilidades.

Ejemplos:

feat(core): implement request object

docs(core): add request technical documentation

docs(iterations): record iteration completion

test(tools): add request testing utility

Nunca mezclar código y documentación sin necesidad.

---

# Documentación

Mantener sincronizados:

README

ROADMAP

DECISIONS

ITERATIONS

docs/core

docs/adr

Si una modificación cambia la arquitectura, actualizar la documentación correspondiente.

---

# Pruebas

Cuando se implemente un componente importante del Core, crear o actualizar un script de prueba en:

tools/

No incorporar frameworks de testing salvo autorización.

---

# Restricciones

No modificar:

- arquitectura
- convenciones
- estructura del proyecto

sin autorización explícita.

No eliminar archivos existentes.

No mover directorios.

No renombrar namespaces.

---

# Entrega

Al finalizar cualquier tarea informar:

- Archivos creados.
- Archivos modificados.
- Resultado de validaciones.
- Contradicciones detectadas.
- Mejoras sugeridas no implementadas.

---

# Objetivo

Cada modificación debe dejar el proyecto:

- más consistente,
- mejor documentado,
- más mantenible,
- sin deuda técnica adicional.

## Regla de Oro

Antes de escribir código, comprender el sistema.

Antes de modificar arquitectura, comprender las consecuencias.

Antes de optimizar, verificar que exista una necesidad real.

Cuando exista una duda entre dos soluciones técnicamente válidas, elegir siempre la opción más simple y consistente con la arquitectura existente.