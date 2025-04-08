/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      colors: {
        customGreen: '#82BA28',
        customBlue: '#1E90FF',
        customRed: '#FF4500',
      },
    },
  },
  plugins: [],
}
