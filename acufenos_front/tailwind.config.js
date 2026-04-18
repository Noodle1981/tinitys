/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#fdfdfc',
          100: '#f7f7f5',
          200: '#eeeeec',
          300: '#dbdbd7',
          400: '#a1a09a',
          500: '#706f6c',
          600: '#555451',
          700: '#3e3e3a',
          800: '#272724',
          900: '#1b1b18',
          950: '#0a0a0a',
        },
        accent: {
          red: '#f53003',
          orange: '#ff750f',
          pink: '#f3bec7',
          blue: '#3b82f6',
        },
        audio: {
          right: '#EF4444',   // Rojo (Oído Derecho)
          left: '#3B82F6',    // Azul (Oído Izquierdo)
          speech: '#FDE047',  // Amarillo (Banana del habla)
          bg: '#FFFFFF',      // Fondo limpio para gráficas
          text: '#111827',    // Texto médico de alta legibilidad
        }
      },
      fontFamily: {
        sans: ['"Instrument Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
