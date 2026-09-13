/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,ts}'],
  theme: {
    extend: {
      colors: {
        background: {
          DEFAULT: '#E8ECF1',
          secondary: '#EAEEF2',
        },
        surface: {
          DEFAULT: '#F5F7FA',
          alt: '#FAFBFC',
        },
        primary: {
          DEFAULT: '#2D6CDF',
          dark: '#1E3FA3',
          light: '#3B82F6',
        },
        accent: {
          DEFAULT: '#F59E0B',
          dark: '#D97706',
          light: '#FEF3C7',
          contrast: '#B45309',
        },
        indigo: {
          DEFAULT: '#6366F1',
          light: '#EEF2FF',
          dark: '#4F46E5',
        },
        secondary: {
          DEFAULT: '#C4C9D0',
        },
        text: {
          DEFAULT: '#2E3440',
          secondary: '#8A94A6',
          muted: '#9AA3B0',
        },
        neu: {
          base: '#E8ECF1',
          surface: '#F5F7FA',
          'surface-alt': '#FAFBFC',
          primary: '#2D6CDF',
          'primary-dark': '#1E3FA3',
          'primary-light': '#3B82F6',
          accent: '#F59E0B',
          'accent-dark': '#D97706',
          'accent-light': '#FEF3C7',
          indigo: '#6366F1',
          text: '#2E3440',
          muted: '#8A94A6',
          secondary: '#C4C9D0',
          icon: '#9AA3B0',
          border: '#D8DFE8',
        },
        status: {
          success: '#2E7D32',
          warning: '#E67E22',
          danger: '#D32F2F',
        },
      },
      borderRadius: {
        'neu-sm': '14px',
        'neu': '22px',
        'neu-lg': '26px',
        'neu-xl': '32px',
      },
      boxShadow: {
        'neu-flat': '5px 5px 14px rgba(163,177,198,0.3), -5px -5px 14px rgba(255,255,255,0.85)',
        'neu-flat-sm': '3px 3px 8px rgba(163,177,198,0.25), -3px -3px 8px rgba(255,255,255,0.8)',
        'neu-flat-lg': '8px 8px 20px rgba(163,177,198,0.28), -8px -8px 20px rgba(255,255,255,0.9)',
        'neu-inset': 'inset 2.5px 2.5px 6px rgba(163,177,198,0.28), inset -2.5px -2.5px 6px rgba(255,255,255,0.85)',
        'neu-pressed': 'inset 2px 2px 4px rgba(163,177,198,0.35), inset -2px -2px 4px rgba(255,255,255,0.9)',
        'neu-btn': '3px 3px 8px rgba(163,177,198,0.28), -3px -3px 8px rgba(255,255,255,0.85)',
        'neu-btn-primary': '3px 3px 10px rgba(45,108,223,0.3), -2px -2px 8px rgba(255,255,255,0.7)',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
