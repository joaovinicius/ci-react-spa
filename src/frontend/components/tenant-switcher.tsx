import {ChevronsUpDown, GalleryVerticalEnd, Plus} from "lucide-react"

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuShortcut,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import {SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar,} from "@/components/ui/sidebar"
import {useTenantStore} from "@/store/useTenantStore.ts";
import {Link, useNavigate, useParams} from "react-router";
import type {z} from "zod";
import {schemas} from "@/api/schemas.ts";
import {useApiClient} from "@/api/apiClient.ts";
import * as React from "react";

type Tenant = z.infer<typeof schemas.Tenant>

export function TenantSwitcher() {
  const params = useParams()
  const tenantId = params.tenantId
  const { isMobile } = useSidebar()
  const { tenant, tenants, setTenants, setTenant } = useTenantStore()
  const navigate = useNavigate();
  const {data} = useApiClient.useQuery('/tenants/all')

  React.useEffect(() => {
    if (!tenant && data?.length) {
      setTenants(data)
      const item = (tenantId)
        ? data.find(item => item.id === Number(tenantId))
        : data[0]
      const tenant = item ?? data[0]
      setTenant(tenant)
    }
  }, [tenant, data, tenantId, setTenants, setTenant])

  const handleChangeTenant = (item: Tenant) => {
    setTenant(item)
    navigate(`/admin/${item.id}`)
  }

  return (
    <SidebarMenu>
      <SidebarMenuItem>
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <SidebarMenuButton
              size="lg"
              className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
            >
              <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg">
                <GalleryVerticalEnd className="size-4" />
              </div>
              <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium">{tenant?.name ?? ''}</span>
                <span className="truncate text-xs">{tenant?.domain ?? ''}</span>
              </div>
              <ChevronsUpDown className="ml-auto" />
            </SidebarMenuButton>
          </DropdownMenuTrigger>
          <DropdownMenuContent
            className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
            align="start"
            side={isMobile ? "bottom" : "right"}
            sideOffset={4}
          >
            <DropdownMenuLabel className="text-muted-foreground text-xs">
              Site
            </DropdownMenuLabel>
            {tenants.map((tenant, index) => (
              <DropdownMenuItem
                key={tenant.id}
                onClick={() => handleChangeTenant(tenant)}
                className="gap-2 p-2 items-center"
              >
                <div className="flex flex-col">
                  <span className="truncate font-medium">{tenant.name}</span>
                  <span className="truncate text-xs">{tenant.domain}</span>
                </div>
                <DropdownMenuShortcut>⌘{index + 1}</DropdownMenuShortcut>
              </DropdownMenuItem>
            ))}
            <DropdownMenuSeparator />
            <DropdownMenuItem className="gap-2 p-2" asChild>
              <Link to="/create">
                <div className="flex size-6 items-center justify-center rounded-md border bg-transparent">
                  <Plus className="size-4" />
                </div>
                <div className="text-muted-foreground font-medium">Criar site</div>
              </Link>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </SidebarMenuItem>
    </SidebarMenu>
  )
}
