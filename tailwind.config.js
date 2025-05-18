/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: "#1E3A8A",
        secondary: "#3B82F6",
      },
      fontFamily: {
        karla: ["Karla", "sans-serif"], 
        abhaya: ["Abhaya Libre", "serif"], // Register Abhaya Libre
      },
      fontWeight: {
        extrabold: "800", // Define Extra Bold weight
        semibold: "600",
      },
    },
  },
  plugins: [],
};
