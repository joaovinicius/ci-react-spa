import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator
} from "@/components/ui/breadcrumb.tsx";
import {Link, useLocation, useParams} from "react-router";
import {MENU} from "@/lib/navigation.ts";
import React, {useState} from "react";
import {useTenantStore} from "@/store/useTenantStore.ts";

export function NavBreadcrumb() {
  const [urls, setUrls] = useState<{ title: string, url:string }[]>([])
  const location = useLocation()
  const params = useParams()
  const { tenant } = useTenantStore()
  
  React.useEffect(() => {
    const items: { title: string, url: string }[] = []
    MENU.forEach((item) => {
      const currentPath = (params?.id)
        ? location.pathname.replace(/^\/(\d+)\/([a-zA-Z-]+)\/(\d+)\/(.*)$/, '/:tenantId/$2/:id/$4')
        : location.pathname.replace(/\d+/g, ':tenantId')
      if (item.url === currentPath) {
        items.push({
          url: item.url,
          title: item.title,
        })
      }

      item.items?.forEach(subItem => {
        if (subItem.url === currentPath) {
          if (items.length === 0) {
            items.push({
              url: item.url,
              title: item.title,
            })
          }

          items.push({
            url: subItem.url,
            title: subItem.title,
          })
        }
      })
    })

    setUrls(items)
  }, [location])


  return (
    <Breadcrumb>
      <BreadcrumbList>
        <BreadcrumbItem className="hidden md:block">
          <BreadcrumbLink asChild>
            <Link to={`${tenant?.domain}`} target="_blank" rel="noreferrer">
              Site
            </Link>
          </BreadcrumbLink>
        </BreadcrumbItem>


        {params?.tenantId && (
          <>
            <BreadcrumbSeparator className="hidden md:block" />

            <BreadcrumbItem className="hidden md:block">
              {location.pathname === `/admin/${params.tenantId}` ? (
                <BreadcrumbPage>{tenant?.name}</BreadcrumbPage>
              ): (
                <BreadcrumbLink asChild>
                  <Link to={`/admin/${params.tenantId}`}>
                    {tenant?.name}
                  </Link>
                </BreadcrumbLink>
              )}
            </BreadcrumbItem>
          </>
        )}


        {urls.map((item, index) => (
          <React.Fragment key={`${index}-${item.url}`}>
            <BreadcrumbSeparator className="hidden md:block" />

            <BreadcrumbItem  key={item.url} className="hidden md:block">
              {urls.length > 1 && index === 0 ? (
                <BreadcrumbLink asChild>
                  <Link to={item.url.replace(':tenantId', String(params.tenantId))}>
                    {item.title}
                  </Link>
                </BreadcrumbLink>
              ): (
                <BreadcrumbPage>{item.title}</BreadcrumbPage>
              )}
            </BreadcrumbItem>
          </React.Fragment>
        ))}

      </BreadcrumbList>
    </Breadcrumb>
  )
}