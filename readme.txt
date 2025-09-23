=== Imagina Login ===
Contributors: augusto97
Tags: login, custom login, login page, login background, login logo
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Personaliza la página de inicio de sesión de WordPress con tu propio logo, imágenes de fondo y colores de marca.

== Description ==

Transforma por completo la aburrida página de inicio de sesión de WordPress (`wp-login.php`) en una experiencia visualmente atractiva y coherente con tu identidad de marca. "Imagina Login" te permite tomar el control total del diseño sin necesidad de escribir una sola línea de código.

**Características principales:**

* **Doble fondo personalizable:** Sube una imagen de fondo para la página completa (`<body>`) y otra imagen diferente para el contenedor del logo, creando un efecto de diseño profesional.
* **Integración con el logo de tu tema:** El plugin utiliza automáticamente el logo o el icono del sitio que ya tienes configurado en tu tema. Si no tienes uno, muestra el título del sitio para que nunca se vea vacío.
* **Colores dinámicos:** Adapta los colores de los botones y enlaces del formulario utilizando el "color primario" definido en el personalizador de tu tema, asegurando una integración perfecta.
* **Interfaz de administración sencilla:** Utiliza el cargador de medios nativo de WordPress para subir y gestionar las imágenes de fondo de forma fácil y rápida desde una nueva página de opciones en tu panel de administración.
* **Diseño responsive:** La página de login se verá increíble tanto en ordenadores de escritorio como en dispositivos móviles.
* **Seguridad mejorada:** Incluye un icono moderno y funcional para mostrar u ocultar la contraseña, mejorando la experiencia de usuario.
* **Ligero y optimizado:** Carga solo los estilos y scripts necesarios, sin sobrecargar tu sitio.

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

= 1.0.0 =
* ¡Lanzamiento inicial del plugin!
* Personalización de fondo para el body y el contenedor del logo.
* Integración automática de logo y colores del tema.
* Panel de administración para gestión de imágenes.
* Diseño responsive.

== Upgrade Notice ==

= 1.0.0 =
Esta es la primera versión del plugin. ¡Gracias por probarlo!