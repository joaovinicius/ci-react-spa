import * as React from "react"
import {NavMain} from "@/components/nav-main"
import {NavUser} from "@/components/nav-user"
import {Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarRail,} from "@/components/ui/sidebar"
import {TenantSwitcher} from "@/components/tenant-switcher.tsx";

export function AppSidebar(sidebarProps: React.ComponentProps<typeof Sidebar>) {
  return (
    <Sidebar collapsible="icon" {...sidebarProps}>
      <SidebarHeader>
        <TenantSwitcher/>
      </SidebarHeader>
      <SidebarContent>
        <NavMain />
      </SidebarContent>
      <SidebarFooter>
        <NavUser />
      </SidebarFooter>
      <SidebarRail/>
    </Sidebar>
  )
}
