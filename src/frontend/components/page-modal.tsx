import {Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle} from "@/components/ui/dialog.tsx";
import {useNavigate, useParams} from "react-router";
import type {ReactNode} from "react";

interface PageModalProps {
  title: string,
  children: ReactNode
  redirect: string
  fullScreen?: boolean
}
export function PageModal({ title, children, redirect, fullScreen }: PageModalProps) {
  const navigate = useNavigate();
  const params = useParams();

  const handleOpenChange = (open: boolean) => {
    if (!open) navigate(`/admin/${params.tenantId}/${redirect}`)
  }

  return (
    <Dialog defaultOpen onOpenChange={handleOpenChange}>
      <DialogContent
        className={
          fullScreen
            ? "min-w-screen min-h-screen m-0 p-0 [&>button]:hidden"
            : "sm:max-w-[425px]"
        }
      >
        <DialogHeader className={fullScreen ? "hidden" : ""}>
          <DialogTitle>{title}</DialogTitle>
          <DialogDescription className="hidden">{title}</DialogDescription>

        </DialogHeader>
        {fullScreen ? children : (<div className="grid gap-4">{children}</div>)}
      </DialogContent>
    </Dialog>
  )
}