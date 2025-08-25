import {UserForm} from "@/components/user-form.tsx";
import {useParams} from "react-router";
import {useApiClient} from "@/api/apiClient.ts";
import {PageModal} from "@/components/page-modal.tsx";

export function EditUser() {
  const params = useParams()
  const tenantId = Number(params.tenantId)
  const id = Number(params.id)

  const { data, invalidate } = useApiClient.useGet("/users/:id", {
    keys: [tenantId, id],
    params: { id },
  })

  return (
    <PageModal title="Editar usuário" redirect="user">
      {data?.id && <UserForm initialData={data} onSuccess={() => invalidate()} />}
    </PageModal>
  )
}