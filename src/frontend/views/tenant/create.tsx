import {Dialog, DialogContent, DialogHeader, DialogTitle,} from "@/components/ui/dialog.tsx"
import {TenantForm} from "@/components/tenant-form.tsx";

export function CreateTenant() {
  return (
    <Dialog defaultOpen>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Criar site</DialogTitle>
        </DialogHeader>
        <div className="grid gap-4">
          <TenantForm  />
        </div>
      </DialogContent>
    </Dialog>
  )
}