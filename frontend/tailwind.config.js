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
        secondary: {
          DEFAULT: '#C4C9D0',
        },
        text: {
          DEFAULT: '#2E3440',
          secondary: '#8A94A6',
          muted: '#9AA3B0',
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
        'neu-lg': '28px',
        'neu-xl': '32px',
      },
      boxShadow: {
        'neu-flat': '6px 6px 14px rgba(163,177,198,0.35), -6px -6px 14px rgba(255,255,255,0.85)',
        'neu-flat-sm': '3px 3px 8px rgba(163,177,198,0.3), -3px -3px 8px rgba(255,255,255,0.8)',
        'neu-flat-lg': '10px 10px 24px rgba(163,177,198,0.35), -10px -10px 24px rgba(255,255,255,0.85)',
        'neu-inset': 'inset 3px 3px 6px rgba(163,177,198,0.35), inset -3px -3px 6px rgba(255,255,255,0.85)',
        'neu-pressed': 'inset 2px 2px 5px rgba(163,177,198,0.45), inset -2px -2px 5px rgba(255,255,255,0.9)',
        'neu-btn': '4px 4px 10px rgba(163,177,198,0.35), -4px -4px 10px rgba(255,255,255,0.85)',
        'neu-btn-primary': '4px 4px 12px rgba(45,108,223,0.35), -3px -3px 10px rgba(255,255,255,0.8)',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
