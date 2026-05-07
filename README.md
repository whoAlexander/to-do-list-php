# 📝 To-Do List App

Una aplicación web de gestión de tareas semi-completa, responsiva y con sistema de usuarios. Permite a los usuarios registrarse, crear listas personalizadas, administrar tareas y marcar su progreso.

## 🚀 Características Principales

* **Autenticación de Usuarios:** Sistema de registro e inicio de sesión seguro con contraseñas encriptadas.
* **Gestión de Tareas:** Crear, editar, eliminar y marcar tareas como completadas.
* **Organización por Listas:** Posibilidad de agrupar tareas en diferentes listas o carpetas.
* **Bandeja de Entrada:** Vista centralizada para tareas sin asignar.
* **Diseño 100% Responsivo:** Interfaz adaptable a computadoras de escritorio y dispositivos móviles usando Flexbox y Bootstrap.

## 🛠️ Tecnologías Utilizadas

* **Frontend:** HTML5, CSS3, JavaScript, Bootstrap (Diseño Glassmorphism).
* **Backend:** PHP.
* **Base de Datos:** MySQL.
* **Arquitectura:** Estructura modular (includes para header, footer, nav) y separación de lógica (carpeta de acciones).

## 📂 Estructura del Proyecto

```text
├── acciones/          # Lógica de backend (crear, editar, eliminar, login)
├── assets/            # CSS personalizado, imágenes y fuentes
├── config/            # Archivos de configuración (conexión a la base de datos)
├── includes/          # Componentes visuales reutilizables (header, sidebar)
├── sql/               # Archivos de exportación de la base de datos
└── [Archivos .php]    # Vistas principales (dashboard, login, register, etc.)