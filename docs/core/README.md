# Core del Framework

El directorio `docs/core/` contiene la documentación técnica de los componentes que forman el núcleo de Narrador Studio.

Cada documento describe:

- Responsabilidad del componente.
- API pública.
- Flujo interno.
- Dependencias.
- Decisiones arquitectónicas.
- Ejemplos de uso.

## Componentes

| Componente | Estado |
|------------|--------|
| Autoloader | ✅ |
| Env | ✅ |
| Config | ✅ |
| Response | 🚧 |
| Request | ⏳ |
| View | ⏳ |
| Database | ⏳ |
| Router | ⏳ |

## Orden de inicialización

Autoloader
↓
Env
↓
Config
↓
Response
↓
Request
↓
View
↓
Database
↓
Router

## Plantilla para escribir documentación de un componente

```markdown
# Response

## Responsabilidad

## Filosofía de diseño

## Ciclo de vida

## API pública

## Flujo interno

## Dependencias

## Ejemplos

## Decisiones arquitectónicas

## Futuras mejoras
```
