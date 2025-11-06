=== Imagina Login ===
Contributors: augusto97
Tags: login, custom login, login page, login background, login logo, login templates
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 2.2.3
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Personaliza la página de inicio de sesión de WordPress con múltiples diseños, logo, imágenes de fondo, videos y colores de marca.

== Description ==

Transforma por completo la aburrida página de inicio de sesión de WordPress (`wp-login.php`) en una experiencia visualmente atractiva y coherente con tu identidad de marca. "Imagina Login" te permite tomar el control total del diseño sin necesidad de escribir una sola línea de código.

**Características principales:**

* **🎭 4 Diseños Profesionales:** Elige entre 4 templates modernos (Clásico, Minimalista, Logo Superior y Pantalla Dividida) que se adaptan a diferentes estilos de marca.
* **🖼️ Fondos Personalizables:** Usa colores sólidos, degradados lineales/radiales, imágenes o videos de fondo tanto para la página como para el área del logo.
* **🎨 Sistema de Colores Completo:** Personaliza colores de etiquetas, botones, enlaces y estados hover de forma independiente o usa los colores de tu tema.
* **✨ Transiciones Animadas:** 3 tipos de animaciones (Fade, Slide Down, Zoom) con duración personalizable para una experiencia suave.
* **🎬 Videos de Fondo:** Sube videos que se reproducen automáticamente con overlay ajustable para mejor legibilidad.
* **📱 100% Responsive:** Todos los diseños se adaptan perfectamente a tablets y móviles con optimizaciones específicas.
* **⚡ Alto Rendimiento:** Código optimizado con cache inteligente, batch queries y auto-disconnect de observers.
* **🔒 Seguridad Mejorada:** Toggle moderno para mostrar/ocultar contraseñas con iconos SVG optimizados.
* **🎯 Interfaz Intuitiva:** Panel de administración moderno con previsualizaciones en tiempo real y tooltips explicativos.

Dale a tus usuarios y clientes una bienvenida profesional desde el primer momento con "Imagina Login".

== Installation ==

1.  Sube la carpeta `imagina-login` al directorio `/wp-content/plugins/` a través de FTP, o sube el archivo ZIP desde `Plugins > Añadir nuevo` en tu panel de WordPress.
2.  Activa el plugin a través del menú 'Plugins' en WordPress.
3.  Ve a la nueva página de ajustes **"Imagina Login"** que aparecerá en tu menú de administración para configurar las imágenes de fondo. ¡Eso es todo!

== Frequently Asked Questions ==

= ¿Dónde puedo configurar las imágenes de fondo? =

Una vez activado el plugin, encontrarás un nuevo menú en el panel de administración de WordPress llamado "Imagina Login". Desde ahí podrás subir la imagen para el fondo de la página y para el fondo del logo.

= ¿Qué logo se utiliza en el formulario? =

El plugin busca de forma inteligente el logo configurado en `Apariencia > Personalizar > Identidad del sitio`. Si tienes un "logo" lo usará, si no, buscará el "icono del sitio". Si no encuentra ninguno, mostrará el nombre de tu sitio como texto.

= ¿El plugin afectará la velocidad de mi sitio? =

No. Los estilos y scripts de este plugin solo se cargan en la página de `wp-login.php`, por lo que no afectan en absoluto al rendimiento del resto de tu web.

= ¿Es compatible con Multisite? =

Sí, el plugin funciona correctamente en instalaciones de WordPress Multisite.

== Screenshots ==

1.  El nuevo diseño de la página de login en acción, con fondo de página y fondo de logo personalizados.
2.  El panel de opciones para subir la imagen de fondo de la página (`body.login`).
3.  El panel de opciones para subir la imagen de fondo del área del logo (`div#login h1`).
4.  Vista del formulario de login en un dispositivo móvil, demostrando su diseño responsive.
5.  Ejemplo del selector de contraseña visible/oculto.

== Changelog ==

= 2.2.3 =
* 🎭 **NUEVO:** Sistema de templates con 4 diseños profesionales
* 🏢 **NUEVO:** Template Clásico Dos Columnas (mejorado del diseño original)
* ✨ **NUEVO:** Template Centrado Minimalista
* 🎯 **NUEVO:** Template Logo Superior
* 🎨 **NUEVO:** Template Pantalla Dividida 60/40
* ⚡ **OPTIMIZACIÓN:** Batch get_option() reduce queries de 13-18 a 1 sola
* ⚡ **OPTIMIZACIÓN:** Cache clearing consolidado de 25 hooks a 1 hook único
* ⚡ **OPTIMIZACIÓN:** MutationObserver con auto-disconnect después de 3s
* 🎨 **MEJORA:** Panel de administración reorganizado y más amigable
* 🎨 **MEJORA:** Selector visual de templates con preview animado
* 🎨 **MEJORA:** Estilos mejorados para cards de templates
* 📱 **MEJORA:** Todos los templates responsive para móviles
* 🐛 **FIX:** Mejor manejo de cache en memoria
* 📦 **ACTUALIZADO:** Versión del plugin a 2.2.3

= 2.2.2 =
* Mejoras de compatibilidad y correcciones menores
* Sistema de transiciones mejorado

= 1.0.0 =
* ¡Lanzamiento inicial del plugin!
* Personalización de fondo para el body y el contenedor del logo
* Integración automática de logo y colores del tema
* Panel de administración para gestión de imágenes
* Diseño responsive

== Upgrade Notice ==

= 2.2.3 =
¡Actualización mayor! Ahora incluye 4 diseños profesionales y optimizaciones de rendimiento significativas. Totalmente compatible con versiones anteriores.

= 1.0.0 =
Esta es la primera versión del plugin. ¡Gracias por probarlo!