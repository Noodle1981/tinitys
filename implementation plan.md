Plan de Integración de Vue en Laravel (Monolito)
Este plan detalla cómo integrar el frontend alojado en acufenos_front dentro del proyecto Laravel principal (d:\tinitus), convirtiéndolo en un monolito donde Laravel sirve la aplicación Vue.

User Review Required
IMPORTANT

Conflicto de Tecnologías: El proyecto Laravel actual utiliza Livewire 4 y Tailwind 4. El proyecto Vue utiliza Tailwind 3 y PrimeVue.

He decidido integrar Vue como una SPA (Single Page Application) dentro de Laravel en lugar de usar Inertia.js para preservar tu lógica de vue-router y pinia tal como están.

WARNING

Migración de Tailwind: Adaptaremos tus estilos de Tailwind 3 a la arquitectura de Tailwind 4 de Laravel 13. Esto implica mover las configuraciones de tailwind.config.js directamente al archivo CSS principal usando variables CSS y directivas @theme.

Proposed Changes
[Backend] Configuración de Entorno
[MODIFY] 
package.json
Añadir dependencias de Vue: vue, vue-router, pinia, primevue, @primevue/themes, lucide-vue-next, chart.js, vue-chartjs.
Añadir devDependencies: @vitejs/plugin-vue.
[MODIFY] 
vite.config.js
Registrar el plugin de Vue @vitejs/plugin-vue.
Configurar el alias @ para apuntar a resources/js.
[Frontend] Estructura de Archivos
[NEW] resources/js/vue-app (Directorio)
Moveremos todo el contenido de acufenos_front/src a esta carpeta para mantener orden respecto a los archivos actuales de Laravel.
[NEW] resources/views/vue-app.blade.php
Punto de entrada de la aplicación Vue con <div id="app"></div> y la directiva @vite.
[Backend] Rutas y Controladores
[MODIFY] 
routes/web.php
Crear una ruta "catch-all" (o específica, ej: /app/{any}) que devuelva la vista vue-app.blade.php.
[Estilos] Tailwind 4 e UI
[MODIFY] resources/css/app.css
Integrar las configuraciones de color y fuentes de acufenos_front/tailwind.config.js dentro del tema de Tailwind 4.
Open Questions
¿Ruta Principal?: ¿Quieres que la aplicación Vue sea la página de inicio (/) sustituyendo al welcome.blade.php actual, o prefieres una ruta específica como /dashboard o /app?
¿PrimeVue?: El proyecto Vue usa PrimeVue 4. Me aseguraré de que las fuentes y temas se carguen correctamente desde el nuevo entorno de Laravel.
Verification Plan
Automated Tests
Ejecutar npm run dev y verificar que Vite compila correctamente tanto el CSS de Laravel como los componentes Vue.
Manual Verification
Acceder a la ruta configurada en el navegador.
Verificar que el Router de Vue funciona correctamente (navegación interna).
Verificar que los gráficos (Chart.js) y componentes de PrimeVue se renderizan con estilos.
