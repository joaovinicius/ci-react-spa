import {Link, Outlet, useParams} from "react-router";
import {useState} from "react";
import {useApiClient} from "@/api/apiClient.ts";
import {Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow} from "@/components/ui/table.tsx";
import {Button} from "@/components/ui/button.tsx";
import {PenBoxIcon, TrashIcon} from "lucide-react";
import {ConfirmDialog} from "@/components/confirm-dialog.tsx";

export function ListUser() {
  const params = useParams()
  const tenantId = Number(params.tenantId)

  const [selectedId, setSelectedId] = useState<number>(0)
  const [confirmOpen, setConfirmOpen] = useState<boolean>(false)

  const { data, refetch, invalidate } = useApiClient.useGet("/users/tenant/:tenantId", {
    keys: [tenantId],
    params: { tenantId },
  })

  const { mutate } = useApiClient.useDelete("/users/:id", {
    keys: [selectedId],
    params: {id: selectedId}
  })

  function handleConfirmDelete(open: boolean, clickedId?: number) {
    if (open && clickedId) {
      setSelectedId(clickedId)
      setConfirmOpen(true)
    } else {
      setSelectedId(0)
      setConfirmOpen(false)
    }
  }

  function handleConfirm() {
    mutate(undefined, {
      onSuccess: async () => {
        await refetch()
        setSelectedId(0)
        setConfirmOpen(false)
      },
    })
  }

  return (
    <>
      <Table>
        <TableCaption>Lista de usuários</TableCaption>
        <TableHeader>
          <TableRow>
            <TableHead className="w-[100px]">#</TableHead>
            <TableHead>Nome</TableHead>
            <TableHead>Permissão</TableHead>
            <TableHead className="text-right"></TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {data?.map((user) => (
            <TableRow key={user.id}>
              <TableCell className="font-medium">{user.id}</TableCell>
              <TableCell>{user.name}</TableCell>
              <TableCell>{user.role}</TableCell>
              <TableCell className="text-right">
                <div className="flex justify-end gap-2">
                  <Button variant="ghost" asChild>
                    <Link to={`/admin/${tenantId}/user/${user.id}/edit`}>
                      <PenBoxIcon />
                    </Link>
                  </Button>
                  <ConfirmDialog
                    open={confirmOpen && selectedId === user.id}
                    title="Excluir usuário?"
                    description="Tem certeza que deseja excluir este usuário?"
                    onOpenChange={(open) => handleConfirmDelete(open, user.id)}
                    onConfirm={handleConfirm}
                    variant="ghost"
                  >
                    <TrashIcon />
                  </ConfirmDialog>
                </div>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
      <Outlet context={{ invalidate }} />
    </>
  )
}