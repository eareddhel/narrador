# NARRATIVE MODEL

## Propósito

Este documento define el modelo conceptual de Narrador Studio.

No describe la implementación técnica, la base de datos ni la interfaz de usuario.

Su propósito es definir cómo se organiza el conocimiento dentro de Narrador Studio y cómo una idea termina convirtiéndose en una narración completa.

Cada nueva funcionalidad deberá respetar este modelo antes de ser implementada.

---

# El proceso creativo

Narrador Studio no parte desde un texto.

Parte desde un tema.

A partir de ese tema el usuario construye una narración utilizando un método narrativo, desarrollando ideas y transformándolas progresivamente en un contenido listo para narrar.

El modelo conceptual es el siguiente.

```
Proyecto
    ↓
Tema
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

# ¿Qué es un proyecto?

Un proyecto representa un espacio de trabajo.

Su propósito es reunir narraciones relacionadas por un mismo objetivo.

Ejemplos:

- EstilApp
- Filosofía en Historias
- Classbook
- Credencial Digital de Discapacidad

Un proyecto puede contener múltiples temas.

---

# ¿Qué es un tema?

El tema representa aquello sobre lo que el creador desea hablar.

No es todavía una narración.

No contiene un guion.

No contiene audio.

Simplemente define el asunto que será desarrollado posteriormente.

Ejemplos:

- Reserva de hora
- Sócrates
- El gato que bota la taza
- Firma electrónica

Un mismo tema puede dar origen a múltiples narraciones.

---

# ¿Qué es una narración?

Una narración representa una historia completa construida a partir de un tema.

Toda narración pertenece a un único tema.

Cada narración puede utilizar un método narrativo distinto.

Por ejemplo, un mismo tema podría desarrollarse como:

- Video corto para TikTok.
- Tutorial completo.
- Podcast.
- Historia.
- Presentación.

La narración constituye la unidad principal de producción de contenido.

---

# ¿Qué es un método narrativo?

Un método narrativo define la estructura utilizada para construir una narración.

No pertenece al proyecto.

No pertenece al tema.

Puede reutilizarse en cualquier narración.

Un método está compuesto por una secuencia ordenada de pasos.

Narrador Studio ofrecerá métodos predefinidos y permitirá que cada usuario construya los suyos.

---

# Biblioteca inicial de métodos

Narrador Studio incorpora una biblioteca base.

Ejemplos:

- TikTok educativo (60 segundos)
- Tutorial paso a paso
- Historia personal
- Presentación de producto
- Clase expositiva
- Podcast corto
- Pitch comercial

Además existirá una categoría especial.

## Mis métodos

Cada usuario podrá crear sus propias estructuras narrativas y reutilizarlas en cualquier proyecto.

---

# ¿Qué es un paso?

Un paso representa un objetivo narrativo dentro del método seleccionado.

Ejemplos.

Método TikTok.

- Gancho
- Problema
- Demostración
- Llamado a la acción

Método Historia.

- Introducción
- Conflicto
- Desarrollo
- Desenlace
- Reflexión

Los pasos organizan la construcción de la historia y reducen la incertidumbre durante el proceso creativo.

---

# ¿Qué es una idea?

La idea constituye la unidad mínima del pensamiento.

Una idea puede ser:

- una frase;
- un ejemplo;
- una metáfora;
- una observación;
- una cita;
- una analogía;
- una escena.

Las ideas aparecen durante el trabajo creativo.

No representan todavía texto definitivo.

Pueden:

- capturarse;
- editarse;
- eliminarse;
- reorganizarse;
- reutilizarse.

Posteriormente podrán incorporarse a uno o varios pasos de una narración.

---

# Relaciones

```
Proyecto
│
├── Tema
│     │
│     ├── Narración
│     │      │
│     │      ├── Método narrativo
│     │      │       │
│     │      │       ├── Paso
│     │      │       ├── Paso
│     │      │       └── Paso
│     │      │
│     │      ├── Ideas
│     │      ├── Texto
│     │      ├── Audio
│     │      └── Exportaciones
│     │
│     └── Narración
│
├── Tema
│
└── Tema
```

---

# Principios del modelo narrativo

## Todo contenido pertenece a una narración.

No existen textos ni audios independientes.

---

## Toda narración pertenece a un tema.

La narración desarrolla un único asunto.

---

## Todo tema pertenece a un proyecto.

El proyecto reúne el conjunto de temas relacionados por un mismo propósito.

---

## Toda narración utiliza un método narrativo.

El método proporciona una estructura para construir la historia.

---

## Un método está compuesto por pasos.

Los pasos representan objetivos narrativos.

No representan texto.

---

## Las ideas aparecen antes que los textos.

Narrador Studio prioriza capturar el pensamiento antes de escribir.

---

## La complejidad aparece cuando es necesaria.

El usuario comienza organizando temas.

Después desarrolla narraciones.

Más tarde escribe.

Finalmente genera audio y exportaciones.

---

# Visión

Narrador Studio no pretende únicamente convertir texto en voz.

Su propósito es ayudar a organizar el pensamiento antes, durante y después de construir una historia.

La aplicación acompaña al creador reduciendo la incertidumbre sobre cuál es el siguiente paso.

El conocimiento se organiza desde el proyecto hasta la narración final, permitiendo que una misma idea evolucione de forma ordenada sin perderse durante el proceso creativo.

El audio es una consecuencia del proceso.

La historia siempre es el centro.

# Ejemplo

Proyecto

EstilApp

↓

Tema

Reserva de hora

↓

Narraciones

- TikTok (60 s)
- Tutorial completo
- Reel
- Historia para Instagram

Proyecto

Filosofía en Historias

↓

Tema

Sócrates

↓

Narraciones

- Sócrates en 60 segundos
- El juicio de Sócrates
- La ironía socrática
- ¿Por qué fue condenado?

El tema no limita al creador a una única narración. Al contrario, se convierte en un núcleo de conocimiento desde el cual pueden surgir múltiples historias con enfoques, duraciones y métodos distintos.