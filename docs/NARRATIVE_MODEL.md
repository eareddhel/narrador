# NARRATIVE MODEL

## Propósito

Este documento define el modelo conceptual de Narrador Studio.

No describe la implementación técnica, la base de datos ni la interfaz de usuario.

Su objetivo es responder cómo se construye una narración dentro de Narrador Studio y cuál es la relación entre los distintos elementos que participan en el proceso creativo.

Cada nueva funcionalidad deberá respetar este modelo antes de ser implementada.

---

# El proceso creativo

Narrador Studio no parte desde un texto.

Parte desde una idea.

El objetivo de la aplicación es acompañar el proceso creativo completo, desde el nacimiento de una idea hasta la generación del contenido final.

El modelo general es el siguiente:

```
Proyecto
    ↓
Narración
    ↓
Método narrativo
    ↓
Pasos
    ↓
Ideas
    ↓
Texto
    ↓
Narración de audio
    ↓
Exportación
```

Cada nivel representa una etapa distinta del proceso creativo.

---

# ¿Qué es una idea?

La idea constituye la unidad mínima del pensamiento.

Es una observación, concepto, frase, pregunta o recuerdo que aparece durante el proceso creativo.

Una idea:

- puede capturarse en cualquier momento;
- todavía no representa un texto;
- puede modificarse;
- puede eliminarse;
- puede reorganizarse;
- puede convertirse posteriormente en parte de una narración.

Las ideas existen para evitar que la inspiración se pierda.

---

# ¿Qué es un paso?

Un paso representa una parte del método narrativo.

No contiene una historia completa.

Contiene un propósito específico dentro de la narración.

Ejemplos de pasos:

- Gancho
- Problema
- Demostración
- Llamado a la acción

o bien:

- Introducción
- Desarrollo
- Conclusión

Los pasos guían al usuario para evitar enfrentarse a una página completamente en blanco.

---

# ¿Qué es un método narrativo?

Un método narrativo define la estructura utilizada para construir una narración.

No pertenece a una narración específica.

Puede reutilizarse tantas veces como sea necesario.

Un método está compuesto por una secuencia ordenada de pasos.

Narrador Studio incluye métodos predefinidos, pero cada usuario podrá crear sus propios métodos.

---

# Biblioteca de métodos

Narrador Studio incorpora una biblioteca inicial de métodos narrativos.

Ejemplos:

- TikTok educativo (60 segundos)
- Tutorial paso a paso
- Historia personal
- Presentación de producto
- Clase expositiva
- Podcast corto
- Pitch comercial

Además existe una categoría especial:

**Mis métodos**

En ella cada usuario podrá crear y reutilizar sus propias estructuras narrativas.

---

# ¿Qué es una narración?

Una narración representa una historia completa.

Toda narración pertenece exactamente a un proyecto.

Toda narración utiliza un único método narrativo.

Una narración está compuesta por una secuencia de pasos.

Cada paso podrá contener ideas, texto y posteriormente una narración de audio.

Una narración constituye la unidad de trabajo principal del creador de contenido.

---

# ¿Qué es un proyecto?

Un proyecto representa un espacio de trabajo.

No contiene únicamente archivos.

Contiene un conjunto de narraciones relacionadas por un mismo propósito.

Ejemplos de proyectos:

- Filosofía en Historias
- EstilApp
- Classbook
- Credencial Digital de Discapacidad

Dentro de cada proyecto pueden existir tantas narraciones como sea necesario.

---

# Relaciones

El modelo conceptual queda definido por las siguientes relaciones.

```
Proyecto
│
├── Narración
│      │
│      ├── Método narrativo
│      │       │
│      │       ├── Paso
│      │       ├── Paso
│      │       ├── Paso
│      │       └── Paso
│      │
│      ├── Ideas
│      ├── Texto
│      ├── Audio
│      └── Exportaciones
│
├── Narración
│
└── Narración
```

---

# Principios del modelo narrativo

## Todo contenido pertenece a una narración.

No existen textos, audios o exportaciones aisladas.

Todo contenido forma parte de una narración.

---

## Toda narración pertenece a un proyecto.

El proyecto constituye el espacio de trabajo del usuario.

---

## Toda narración utiliza un método narrativo.

El método proporciona una estructura para reducir la incertidumbre durante el proceso creativo.

---

## Un método está compuesto por pasos reutilizables.

Los pasos representan objetivos narrativos.

No representan textos.

---

## Las ideas aparecen antes que los textos.

Narrador Studio prioriza la captura de ideas por encima de la escritura.

El usuario nunca debería perder una idea por no saber todavía dónde desarrollarla.

---

## La complejidad aparece cuando es necesaria.

El usuario comienza con una estructura simple.

Las funcionalidades más avanzadas aparecen únicamente cuando el proyecto las requiere.

---

# Visión

Narrador Studio no pretende únicamente convertir texto en voz.

Su propósito es ayudar a organizar el pensamiento antes, durante y después de construir una historia.

La aplicación acompaña el proceso creativo reduciendo la incertidumbre sobre cuál es el siguiente paso.

El audio es una consecuencia del proceso.

La historia siempre es el centro.