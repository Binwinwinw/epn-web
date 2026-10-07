/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.php",
    "./public/**/*.php",
    "./src/pages/**/*.php",
    "./src/includes/**/*.php",
    "./public/assets/js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        bac: {
          DEFAULT: "#2563eb",
          light: "#dbeafe",
        },
        mac: {
          DEFAULT: "#0891b2",
          light: "#cffafe",
        },
      },
      boxShadow: {
        soft: "0 18px 44px rgba(15, 23, 42, 0.08)",
      },
    },
  },
  plugins: [],
};
