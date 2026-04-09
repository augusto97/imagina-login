=== Imagina Login ===
Contributors: augusto97
Tags: login, custom login, login page, login background, login logo, login templates, fullscreen login, sidebar login
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 2.3.7
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Personaliza la página de inicio de sesión de WordPress con 9 diseños profesionales, logo, imágenes de fondo, videos y colores de marca.

== Description ==

Transforma por completo la aburrida página de inicio de sesión de WordPress (`wp-login.php`) en una experiencia visualmente atractiva y coherente con tu identidad de marca. "Imagina Login" te permite tomar el control total del diseño sin necesidad de escribir una sola línea de código.

**Características principales:**

* **🎭 9 Diseños Profesionales:** Elige entre 9 templates modernos que se adaptan a diferentes estilos de marca:
  - Clásico Dos Columnas (empresas corporativas)
  - Sidebar Izquierda (variante invertida full height)
  - Sidebar 50/50 Derecha (diseño equilibrado)
  - Sidebar 50/50 Izquierda (balance con énfasis izquierdo)
  - Pantalla Dividida 60/40 (agencias creativas)
  - Pantalla Completa (aplicaciones web y SaaS)
  - Glassmorphism (startups tech y apps premium)
  - Sidebar Derecha (panel lateral full height)
  - Centrado Compacto (sitios minimalistas)
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

= 2.3.7 =
* 🐛 **FIX:** Corregido bug donde el layout no se guardaba en la primera selección
* 🐛 **FIX:** Cache de opciones ahora se limpia correctamente con added_option y updated_option
* 🐛 **FIX:** Variable estática de cache ahora se resetea correctamente al guardar
* 🎨 **MEJORA:** Panel de administración compactado - menos espacio vertical
* 🎨 **MEJORA:** Header, secciones, cards de templates y controles más compactos
* 🎨 **MEJORA:** Tabs de tipo de fondo ahora horizontales en vez de columnas
* 📦 **CÓDIGO:** Cache key actualizado a v10 para regeneración de estilos
* 📦 **ACTUALIZADO:** Versión del plugin a 2.3.7

= 2.3.6 =
* 🎛️ **NUEVO:** Controles separados para animaciones de Logo y Formulario
* ⚡ **NUEVO:** Control individual de velocidad para Logo (0.1 - 1.5s)
* ⚡ **NUEVO:** Control individual de delay para Logo (0 - 1.0s)
* ⚡ **NUEVO:** Control individual de velocidad para Formulario (0.1 - 1.5s)
* ⚡ **NUEVO:** Control individual de delay para Formulario (0 - 1.0s)
* 🎨 **MEJORA:** UI mejorada con secciones visuales separadas para cada animación
* 🎯 **FLEXIBILIDAD:** Personalización completa del timing de cada elemento
* 📦 **CÓDIGO:** Variables CSS actualizadas (--logo-duration, --logo-delay, --form-duration, --form-delay)
* 📦 **CÓDIGO:** Cache key actualizado a v9 para regeneración de estilos
* 📦 **ACTUALIZADO:** Versión del plugin a 2.3.6

= 2.3.5 =
* 🎬 **MEJORA MAYOR:** Transiciones completamente reoptimizadas con timings naturales
* ✨ **NUEVO:** Curvas de animación cubic-bezier para movimientos más fluidos y profesionales
* ⏱️ **OPTIMIZACIÓN:** Delays reducidos y sincronizados (150ms, 120ms, 100ms según tipo)
* 🎯 **MEJORA:** Movimientos más sutiles (20px en lugar de 30px, scale 0.95 en lugar de 0.9)
* 🎨 **MEJORA:** Duración de animaciones ajustada (80-90% de la duración base)
* 🎭 **MEJORA:** Efecto "bounce" sutil en animaciones con cubic-bezier(0.34, 1.56, 0.64, 1)
* ⚡ **PERFORMANCE:** Activación simultánea de animaciones a los 50ms (delays en CSS)
* 📦 **CÓDIGO:** Cache key actualizado a v8 para regeneración de estilos
* 📦 **ACTUALIZADO:** Versión del plugin a 2.3.5

= 2.3.4 =
* ✨ **MEJORA:** Restauradas animaciones individuales del logo y formulario con delays
* 🎨 **MEJORA:** Logo aparece con animación a los 100ms (fade + scale o slidedown o zoom)
* 🎨 **MEJORA:** Formulario aparece con animación a los 300ms (slide horizontal o vertical)
* 🎯 **BALANCE:** Fondo aparece inmediatamente (sin flash) + contenido con animaciones suaves
* 📦 **ACTUALIZADO:** Versión del plugin a 2.3.4

= 2.3.3 =
* 🚀 **OPTIMIZACIÓN MAYOR:** Refactorizado completamente el sistema de fondos
* 🐛 **FIX:** Eliminado flash visual de imagen al cargar - el fondo ahora aparece inmediatamente
* 🐛 **FIX:** Transiciones optimizadas - contenido aparece en 50ms en lugar de 500ms
* ⚡ **OPTIMIZACIÓN:** Eliminada duplicación de backgrounds entre body.login y ::after
* ⚡ **OPTIMIZACIÓN:** Removidas propiedades CSS redundantes de todos los templates
* 🎨 **MEJORA:** Sistema de clases simplificado (has-transitions, content-loaded)
* 🎨 **MEJORA:** Los fondos ya no se animan - aparecen instantáneamente
* 🎨 **MEJORA:** Solo el contenido del formulario se anima para mejor UX
* 📦 **CÓDIGO:** Estructura CSS más limpia y mantenible sin sobreescrituras
* 📦 **CÓDIGO:** Cache key actualizado a v7 para forzar regeneración de estilos
* 📦 **ACTUALIZADO:** Versión del plugin a 2.3.3

= 2.3.2 =
* 🐛 **FIX:** Resuelto scroll horizontal y vertical en sidebar templates alineados a la derecha
* 🐛 **FIX:** Eliminados gradientes hardcodeados - ahora todos controlables desde panel admin
* 🐛 **FIX:** Fondos de imagen ahora usan background-size: cover sin repetición
* 🐛 **FIX:** Removido flash de imagen de fallback antes de carga completa
* 🎨 **MEJORA:** Agregado !important para forzar background-size: cover en todos los templates
* 🎨 **MEJORA:** Consistencia de widths entre sidebar templates izquierdos y derechos
* 🎨 **MEJORA:** Todos los fondos de logo ahora personalizables vía CSS variables
* ⚡ **OPTIMIZACIÓN:** Removidos preloads de imágenes que causaban flash visual
* 📦 **ACTUALIZADO:** Versión del plugin a 2.3.2

= 2.3.1 =
* 🎨 **MEJORA:** Removidos templates muy similares (Centrado Minimalista y Logo Superior dejando solo Boxed)
* ✅ **NUEVO:** Sidebar Izquierda - variante invertida del sidebar con panel a la izquierda
* ✅ **NUEVO:** Sidebar 50/50 Derecha - diseño equilibrado con sidebar ocupando 50% del ancho
* ✅ **NUEVO:** Sidebar 50/50 Izquierda - diseño equilibrado con sidebar izquierdo
* 🐛 **FIX:** Template Full Screen ahora usa correctamente los fondos personalizables
* 🐛 **FIX:** Full Screen con backgrounds transparentes para mostrar fondo del body
* 🎨 **MEJORA:** Mejor visibilidad de labels y enlaces en Full Screen con text-shadow
* 🎨 **MEJORA:** Footer con backdrop-filter en Full Screen para mejor legibilidad
* 📱 **MEJORA:** Todas las variantes sidebar responsive (50% en desktop/tablet, 100% en móvil)
* 📦 **ACTUALIZADO:** Plugin ahora tiene 9 templates profesionales en total
* 📦 **ACTUALIZADO:** Versión del plugin a 2.3.1

= 2.3.0 =
* 🎭 **NUEVO:** 4 templates adicionales (ahora 8 en total)
* 🖥️ **NUEVO:** Template Pantalla Completa - login cubre toda la pantalla
* 💎 **NUEVO:** Template Glassmorphism - efecto cristal moderno
* 📱 **NUEVO:** Template Sidebar Lateral - panel lateral full height
* 📦 **NUEVO:** Template Centrado Compacto - caja flotante sobre fondo
* 🎨 **MEJORA:** Mockups visuales mejorados para todos los templates
* 🎨 **MEJORA:** Efectos backdrop-filter en template Glassmorphism
* 📱 **MEJORA:** Mejor experiencia responsive en todos los nuevos templates
* 🎨 **MEJORA:** Animaciones de entrada suaves en template Boxed
* 📦 **ACTUALIZADO:** Versión del plugin a 2.3.0

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

= 2.3.0 =
¡Actualización mayor! Ahora incluye 8 diseños profesionales incluyendo Full Screen, Glassmorphism, Sidebar y Boxed. Totalmente compatible con versiones anteriores.

= 2.2.3 =
Primera versión con sistema de templates múltiples y optimizaciones de rendimiento significativas. Totalmente compatible con versiones anteriores.

= 1.0.0 =
Esta es la primera versión del plugin. ¡Gracias por probarlo!