# Iteraciones

## 001 - Core / Autoloader
- Objetivo: Implementar el sistema de carga automática de clases.
- Estado: ✅ Completada
- Commit: feat(core): implement autoloader

## 002 - Core / Env
- Objetivo: Implementar el gestor de variables de entorno.
- Estado: ✅ Completada
- Commit: feat(core): implement env loader

## 003 - Core / Config
- Objetivo: Implementar el sistema centralizado de configuración del framework mediante la clase `App\Core\Config`, con carga única de archivos de configuración modulares y acceso mediante notación por puntos.
- Estado: ✅ Completada
- Commit: feat(core): implement configuration loader

## 004 - Core / Response
- Objetivo: Implementar la clase `App\Core\Response` como un objeto que represente una respuesta HTTP completa, con soporte para código de estado, cabeceras, cuerpo, respuestas JSON, redirecciones, descargas de archivos y envío mediante una API fluida (Fluent Interface).
- Estado: ✅ Completada
- Commit: feat(core): implement response object

## 005 - Core / Request
- Objetivo: Implementar la clase `App\Core\Request` como un objeto inmutable que represente una petición HTTP, capturando una única vez las superglobales y proporcionando una API orientada a objetos para acceder a los datos de la petición.
- Estado: ✅ Completada
- Commit: feat(core): implement request object

## 006 - Core / View
- Objetivo: Implementar la clase `App\Core\View` como un renderizador orientado a objetos capaz de localizar plantillas PHP, exponer datos, aplicar layouts, capturar la salida de forma segura y devolver el resultado mediante un objeto `Response`.
- Estado: ✅ Completada
- Commit: feat(core): implement view renderer

## 007 - Core / Database
- Objetivo: Implementar la clase `App\Core\Database` como una capa orientada a objetos sobre PDO, sin Singleton, con conexión encapsulada por instancia, consultas preparadas, operaciones de lectura y escritura, transacciones mediante callback y manejo de errores mediante `DatabaseException`.
- Estado: ✅ Completada
- Commit: feat(core): implement database abstraction