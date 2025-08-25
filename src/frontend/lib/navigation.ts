import {UserPen, Users} from "lucide-react";
import {Roles} from "@/lib/roles.ts";
import type {FC, SVGProps} from "react";

export const MENU: TMenu[] = [
  {
    title: "Usuários",
    url: "/admin/:tenantId/user",
    icon: Users,
    isActive: false,
    roles: [Roles.Admin, Roles.OrgAdmin],
    items: [
      {
        title: "Listar usuários",
        url: "/admin/:tenantId/user",
        sidebar: true,
        breadcrumb: true,
      },
      {
        title: "Novo usuário",
        url: "/admin/:tenantId/user/create",
        sidebar: true,
        breadcrumb: true,
      },
    ],
  },
]

export type TMenu = {
  title: string;
  icon: FC<SVGProps<SVGSVGElement>>;
  url: string;
  isActive?: boolean;
  items: {
    title: string;
    url: string;
    sidebar: boolean;
    breadcrumb: boolean;
  }[];
  roles?: string[];
}

export const USER_MENU = [
  {
    title: "Perfil",
    icon: UserPen,
    url: "/profile",
  },
]

