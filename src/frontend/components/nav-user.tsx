import {ChevronsUpDown, LogOut,} from "lucide-react"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import {SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar,} from "@/components/ui/sidebar"
import {useApiClient} from "@/api/apiClient.ts";
import {useAuthStore} from "@/store/useAuthStore.ts";
import {useEffect} from "react";
import {Link, useNavigate} from "react-router";
import {USER_MENU} from "@/lib/navigation.ts";
import {useTokenRefresh} from "@/hooks/use-token-refresh.ts";

export function NavUser() {
  const { isMobile } = useSidebar()
  const navigate = useNavigate();
  const {data: currentUser} = useApiClient.useQuery('/auth/me')
  const { user, setUser, logout, isLoggedIn } = useAuthStore()
  const { startTokenRefresh, stopTokenRefresh } = useTokenRefresh();


  const handleLogout = () => {
    stopTokenRefresh();
    logout()
    navigate('/auth/login')
  }
  
  useEffect(() => {
    if (currentUser) {
      setUser(currentUser)
    }
  }, [currentUser, setUser])

  useEffect(() => {
    if (isLoggedIn()) {
      startTokenRefresh();
    } else {
      stopTokenRefresh();
    }

    return () => {
      stopTokenRefresh();
    };
  }, [isLoggedIn, startTokenRefresh, stopTokenRefresh]);

  if (!currentUser || !user) {
    return null
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
              <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium">{user.name}</span>
                <span className="truncate text-xs">{user.email}</span>
              </div>
              <ChevronsUpDown className="ml-auto size-4" />
            </SidebarMenuButton>
          </DropdownMenuTrigger>
          <DropdownMenuContent
            className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
            side={isMobile ? "bottom" : "right"}
            align="end"
            sideOffset={4}
          >
            <DropdownMenuGroup>
              {USER_MENU.map((item) => (
                <DropdownMenuItem key={item.title} asChild>
                  <Link to={item.url}>
                    <item.icon />
                    {item.title}
                  </Link>
                </DropdownMenuItem>
              ))}
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem onClick={handleLogout}>
              <LogOut />
              Log out
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </SidebarMenuItem>
    </SidebarMenu>
  )
}
