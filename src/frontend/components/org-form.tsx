import {useForm} from "react-hook-form"
import {z} from "zod"
import {zodResolver} from "@hookform/resolvers/zod"
import {useState} from "react"
import {Button} from "@/components/ui/button"
import {Input} from "@/components/ui/input"
import {Label} from "@/components/ui/label"
import {useApiClient} from "@/api/apiClient"
import {schemas} from "@/api/schemas"
import type {AxiosError} from "axios"

type OrgInputType = z.infer<typeof schemas.OrganizationInput>

interface OrgFormProps {
  initialData?: OrgInputType & { id?: number } // If editing, pass in an ID
  onSuccess?: () => void
}

/**
 * Generic form for creating or editing an Organization (“Org”) entity.
 */
export function OrgForm({ initialData, onSuccess }: OrgFormProps) {
  const [error, setError] = useState("")
  
  // Merge in any existing data for editing; otherwise defaults
  const defaultValues: OrgInputType = {
    slug: "",
    name: "",
    ...initialData,
  }

  // Determine if this is a create or update
  const endpoint = initialData?.id ? `/orgs/${initialData.id}` : "/orgs"
  const method = initialData?.id ? "put" : "post"

  const { mutate, isPending } = useApiClient.useMutation(method, endpoint, undefined, {
    onSuccess: () => {
      if (onSuccess) onSuccess()
      console.log("Organization form submitted successfully")
    },
    onError: (err: AxiosError) => {
      setError(err.message || "Failed to submit organization form")
    },
  })

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<OrgInputType>({
    resolver: zodResolver(schemas.OrganizationInput),
    defaultValues,
  })

  const onSubmit = (formData: OrgInputType) => {
    setError("")
    mutate(formData)
  }

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <div>
        <Label htmlFor="org-slug">Slug</Label>
        <Input
          id="org-slug"
          placeholder="unique-org-slug"
          {...register("slug")}
          aria-invalid={!!errors.slug}
        />
        {errors.slug && (
          <p className="text-sm text-red-500">{errors.slug.message}</p>
        )}
      </div>

      <div>
        <Label htmlFor="org-name">Name</Label>
        <Input
          id="org-name"
          placeholder="Organization Name"
          {...register("name")}
          aria-invalid={!!errors.name}
        />
        {errors.name && (
          <p className="text-sm text-red-500">{errors.name.message}</p>
        )}
      </div>

      {error && (
        <div className="text-sm text-red-500">{error}</div>
      )}

      <div className="flex justify-end gap-2">
        <Button variant="outline" type="button" onClick={() => setError("")}>
          Cancel
        </Button>
        <Button type="submit" disabled={isPending}>
          {isPending ? "Saving..." : "Save Org"}
        </Button>
      </div>
    </form>
  )
}