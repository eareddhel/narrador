# ADR-002: Env y Config como servicios globales estaticos

## Estado

Aceptado

## Contexto

Env y Config representan informacion global de la aplicacion: variables de entorno y configuracion modular. No pertenecen a una peticion HTTP concreta.

## Problema

Instanciar Env y Config por cada request agregaria complejidad sin beneficio real. Ademas, permitir lecturas directas de `.env` o de archivos dentro de `config/` dispersaria el acceso a configuracion.

## Decision

Env y Config seran servicios globales con API estatica.

- `Env` carga `.env` una sola vez y expone sus valores mediante metodos tipados.
- `Config` carga archivos de configuracion por dominio y expone valores mediante notacion por puntos.
- Ninguna clase debe acceder directamente a `$_ENV` ni a archivos dentro de `config/`.

## Consecuencias

El acceso a configuracion queda centralizado, simple y consistente. Se reduce el acoplamiento con archivos fisicos y se evita repetir carga de configuracion en cada componente.

## Alternativas descartadas

- Crear instancias de Env y Config por request.
- Leer `$_ENV` directamente desde controllers, services o modelos.
- Incluir archivos de `config/` directamente desde clases consumidoras.
