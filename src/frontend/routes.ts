import {createBrowserRouter} from "react-router";
import {AdminTheme} from "./themes/AdminTheme";
import {Dashboard} from "@/views/dashboard.tsx";
import {FullScreamTheme} from "@/themes/FullScreamTheme.tsx";
import {Loader} from "@/components/loader.tsx";

export const router = createBrowserRouter(
  [
    {
      path: "/admin",
      Component: AdminTheme,
      children: [
        {
          index: true,
          Component: Dashboard,
          HydrateFallback: Loader,
        },
        {
          path: "create",
          HydrateFallback: Loader,
          async lazy() {
            const {CreateTenant} = await import("@/views/tenant/create.tsx");
            return { Component: CreateTenant };
          },
        },
        {
          path: "settings",
          HydrateFallback: Loader,
          async lazy() {
            const {Settings} = await import("@/views/settings.tsx");
            return { Component: Settings };
          },
        },
        {
          path: "profile",
          HydrateFallback: Loader,
          async lazy() {
            const {Profile} = await import("@/views/profile.tsx");
            return { Component: Profile };
          },
        },
        {
          index: true,
          path: ":tenantId",
          HydrateFallback: Loader,
          async lazy() {
            const {Dashboard} = await import("@/views/tenant/dashboard.tsx");
            return { Component: Dashboard };
          },
        },
        {
          path: ":tenantId/user",
          HydrateFallback: Loader,
          async lazy() {
            const {ListUser} = await import("@/views/user/index.tsx");
            return { Component: ListUser };
          },
          children: [
            {
              path: 'create',
              HydrateFallback: Loader,
              async lazy() {
                const {CreateUser} = await import("@/views/user/create.tsx");
                return { Component: CreateUser };
              },
            },
            {
              path: ':id',
              children: [
                {
                  path: 'edit',
                  HydrateFallback: Loader,
                  async lazy() {
                    const {EditUser} = await import("@/views/user/edit.tsx");
                    return { Component: EditUser };
                  },
                }
              ]
            }
          ]
        },
      ],
    },
    {
      path: "/auth",
      Component: FullScreamTheme,
      children: [
        {
          path: "login",
          async lazy() {
            const {Login} = await import("@/views/auth/login.tsx");
            return { Component: Login };
          },
          HydrateFallback: Loader,
        },
        {
          path: "forgot-password",
          async lazy() {
            const {ForgotPassword} = await import("@/views/auth/forgot-password.tsx");
            return { Component: ForgotPassword };
          },
          HydrateFallback: Loader,
        },
        {
          path: "reset-password",
          async lazy() {
            const {ResetPassword} = await import("@/views/auth/reset-password.tsx");
            return { Component: ResetPassword };
          },
          HydrateFallback: Loader,
        },
      ]
    },
    {
      path: "404",
      Component: FullScreamTheme,
      children: [
        {
          index: true,
          async lazy() {
            const {NotFoundPage} = await import("@/views/404.tsx");
            return { Component: NotFoundPage };
          },
          HydrateFallback: Loader,
        }
      ]
    },
    {
      path: "*",
      async lazy() {
        const {Page} = await import("@/views/page.tsx");
        return { Component: Page };
      },
      HydrateFallback: Loader,
    },
  ],
  { basename: "/" }
);