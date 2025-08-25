import React from "react";
import ReactDOM from "react-dom/client";
import {RouterProvider} from "react-router";
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {ReactQueryDevtools} from '@tanstack/react-query-devtools'; // Optional: for dev tools
import "./assets/index.css";
import {router} from "./routes";

let root = document.getElementById("root");

if (!root) {
  root = document.createElement("div");
  root.id = "root";
  document.appendChild(root);
}

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 1000 * 60 * 5, // 5 minutes
    },
  },
});

ReactDOM.createRoot(root).render(
  <React.StrictMode>
    <QueryClientProvider client={queryClient}>
      <RouterProvider router={router} />
      <ReactQueryDevtools initialIsOpen={false} />
    </QueryClientProvider>
  </React.StrictMode>,
);