import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import {Button, type ButtonProps} from "@/components/ui/button"
import React from "react";

interface ConfirmDialogProps {
  children: React.ReactNode
  open: boolean,
  title: string,
  description: string,
  onOpenChange: (open: boolean) => void,
  onConfirm: () => void
  variant?: ButtonProps['variant']
}

export function ConfirmDialog({
  children,
  open,
  title,
  description,
  onOpenChange,
  onConfirm,
  variant
}: ConfirmDialogProps) {
  return (
    <Dialog
      onOpenChange={onOpenChange}
      open={open}
    >
      <DialogTrigger asChild>
        <Button variant={variant}>{children}</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{title}</DialogTitle>
          <DialogDescription>{description}</DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button type="submit" onClick={onConfirm}>Confirm</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}