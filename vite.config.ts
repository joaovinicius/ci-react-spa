import path from "path"
import tailwindcss from "@tailwindcss/vite"
import {defineConfig, loadEnv} from "vite";
import react from "@vitejs/plugin-react";

// https://vite.dev/config/
export default defineConfig(({ command, mode }) => {
  process.env = {...process.env, ...loadEnv(mode, process.cwd())};
  const base = command === "build" && process.env.NODE_ENV
    ? process.env.VITE_BASENAME
    : "/"

  return {
    root: 'src/frontend',
    plugins: [react(), tailwindcss()],
    base,
    build: {
      outDir: "../../public_html",
    },
    resolve: {
      alias: {
        "@": path.resolve(__dirname, "./src/frontend"),
      },
    },
    server: {
      port: 3000,
    },
  }
});
