import {useForm} from "react-hook-form"
import {z} from "zod"
import {zodResolver} from "@hookform/resolvers/zod"
import {Button} from "@/components/ui/button"
import {Input} from "@/components/ui/input"
import {Label} from "@/components/ui/label"
import {useState} from "react"
import {useApiClient} from "@/api/apiClient"
import {schemas} from "@/api/schemas"
import type {AxiosError} from "axios"

type TenantInputType = z.infer<typeof schemas.TenantInput>

interface TenantFormProps {
  initialData?: TenantInputType
  onSuccess?: () => void
}

/**
 * Generic form for creating or editing a Tenant entity
 */
export function TenantForm({ initialData, onSuccess }: TenantFormProps) {
  const [error, setError] = useState("")
  const defaultValues: TenantInputType = {
    domain: "",
    name: "",
    status: "draft",  // e.g. "draft"
    org_id: 1,
    config: {},
    ...initialData,
  }

  // Adjust the API call as needed (this assumes a POST to "/tenants").
  const { mutate, isPending } = useApiClient.useMutation("post", "/tenants", undefined, {
    onSuccess: () => {
      if (onSuccess) onSuccess()
      console.log("done")
    },
    onError: (err: AxiosError) => {
      setError(err.message || "Error submitting form")
    },
  })

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<TenantInputType>({
    resolver: zodResolver(schemas.TenantInput),
    defaultValues,
  })

  const onSubmit = (formData: TenantInputType) => {
    setError("")
    mutate(formData)
  }

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <div>
        <Label htmlFor="name">Name</Label>
        <Input
          id="name"
          type="text"
          placeholder="Awesome Tenant"
          {...register("name")}
          aria-invalid={!!errors.name}
        />
        {errors.name && (
          <p className="text-sm text-red-500">{errors.name.message}</p>
        )}
      </div>

      <div>
        <Label htmlFor="domain">Domain</Label>
        <Input
          id="domain"
          type="text"
          placeholder="example.com"
          {...register("domain")}
          aria-invalid={!!errors.domain}
        />
        {errors.domain && (
          <p className="text-sm text-red-500">{errors.domain.message}</p>
        )}
      </div>

      <div>
        <Label htmlFor="status">Status</Label>
        <Input
          id="status"
          type="text"
          placeholder="draft | published | archived"
          {...register("status")}
          aria-invalid={!!errors.status}
        />
        {errors.status && (
          <p className="text-sm text-red-500">{errors.status.message}</p>
        )}
      </div>

      <div>
        <Label htmlFor="org_id">Organization ID</Label>
        <Input
          id="org_id"
          type="number"
          {...register("org_id", { valueAsNumber: true })}
          aria-invalid={!!errors.org_id}
        />
        {errors.org_id && (
          <p className="text-sm text-red-500">{errors.org_id.message}</p>
        )}
      </div>

      {error && (
        <div className="text-center text-sm text-red-500">
          {error}
        </div>
      )}

      <div className="flex justify-end gap-2">
        <Button variant="outline" type="button" onClick={() => setError("")}>
          Cancel
        </Button>
        <Button type="submit" disabled={isPending}>
          {isPending ? "Saving..." : "Save Tenant"}
        </Button>
      </div>
    </form>
  )
}