import {UserForm} from "@/components/user-form.tsx";
import {PageModal} from "@/components/page-modal.tsx";

export function CreateUser() {
  return (
    <PageModal title="Criar usuário" redirect="user">
      <UserForm />
    </PageModal>
  )
}