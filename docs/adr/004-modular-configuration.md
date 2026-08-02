# ADR-004: Configuracion modular accedida exclusivamente mediante Config

## Estado

Aceptado

## Contexto

La aplicacion necesita configuracion para dominios distintos: app, base de datos, rutas, TTS y constantes. Esa informacion debe mantenerse ordenada y disponible para el Core y los Services.

## Problema

Un unico archivo de configuracion creceria rapidamente y mezclaria responsabilidades. Leer archivos de configuracion directamente desde distintas clases acoplaria el sistema a rutas y formatos concretos.

## Decision

La configuracion se organiza por dominio dentro de `config/` y solo se accede mediante `App\Core\Config`.

Archivos principales:

- `app.php`
- `database.php`
- `routes.php`
- `tts.php`
- `constants.php`

Los consumidores usan `Config::get('dominio.clave')`.

## Consecuencias

Cada archivo conserva una unica responsabilidad. Las clases consumidoras no dependen de rutas fisicas. Agregar nuevas areas de configuracion sera posible sin cambiar la API publica de acceso.

## Alternativas descartadas

- Un archivo unico de configuracion global.
- Acceso directo con `require` o `include` desde componentes del Core.
- Variables hardcodeadas en controllers, services o modelos.
