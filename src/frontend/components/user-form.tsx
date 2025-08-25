import {Controller, useForm} from "react-hook-form"
import {z} from "zod"
import {zodResolver} from "@hookform/resolvers/zod"
import {Button} from "@/components/ui/button"
import {Input} from "@/components/ui/input"
import { Checkbox } from "@/components/ui/checkbox.tsx"
import {Label} from "@/components/ui/label"
import {useState} from "react"
import {useApiClient} from "@/api/apiClient"
import {schemas} from "@/api/schemas"
import type {AxiosError} from "axios"
import {useNavigate, useOutletContext, useParams} from "react-router";
import {AllRoles} from "@/lib/roles.ts";

type UserInputType = z.infer<typeof schemas.UserInput>

interface UserFormProps {
  initialData?: Partial<UserInputType> & { id?: number }
  onSuccess?: () => void
}

/**
 * Generic form for creating or editing a User entity
 */
export function UserForm({ initialData, onSuccess }: UserFormProps) {
  const [error, setError] = useState("")
  const navigate = useNavigate();
  const params = useParams();
  const { invalidate } = useOutletContext();
  
  // Prepare default values (e.g., for editing)
  const defaultValues: UserInputType = {
    email: "",
    name: "",
    password: "",
    phone: null,
    bio: null,
    email_verified: false,
    org_id: undefined,
    tenant_id: undefined,
    role: [],
    ...initialData, // Merge in any existing user data for editing
  }

  /**
   * Decide which endpoint + method to call based on presence of an ID:
   * - If an ID is present => "PUT /users/:id" (or your actual update route)
   * - Otherwise => "POST /users" (or your actual create route)
   */
  const endpoint = initialData?.id ? `/users/:id` : "/users"
  const method = initialData?.id ? "put" : "post"

  // Setup the mutation
  const { mutate, isPending } = useApiClient.useMutation(
    method,
    endpoint,
    (initialData?.id ? { params: { id: initialData?.id } } : undefined) as any,
    {
      onSuccess: () => {
        invalidate()
        navigate(`/admin/${params.tenantId}/user`)
        if (onSuccess) onSuccess()
      },
      onError: (err: AxiosError) => {
        setError(err.message || "Failed to submit user form")
      }
    })

  const form = useForm<UserInputType>({
    resolver: zodResolver(schemas.UserInput),
    defaultValues,
  })

  const { formState: { errors } } = form

  const onSubmit = (formData: UserInputType) => {
    setError("")
    mutate(formData)
  }

  const handleCancel = () => {
    setError("")
    navigate(`/admin/${params.tenantId}/category`)
  }

  return (
    <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
      <div>
        <Label htmlFor="email">Email</Label>
        <Input
          id="email"
          type="email"
          placeholder="user@example.com"
          {...form.register("email")}
          aria-invalid={!!errors.email}
        />
        {errors.email && (
          <p className="text-sm text-red-500">{errors.email.message}</p>
        )}
      </div>

      <div>
        <Label htmlFor="name">Name</Label>
        <Input
          id="name"
          type="text"
          placeholder="John Doe"
          {...form.register("name")}
          aria-invalid={!!errors.name}
        />
        {errors.name && (
          <p className="text-sm text-red-500">{errors.name.message}</p>
        )}
      </div>

      {/* Only show password field if creating or if you allow password resets */}
      <div>
        <Label htmlFor="password">Password</Label>
        <Input
          id="password"
          type="password"
          placeholder="•••••••"
          {...form.register("password")}
          aria-invalid={!!errors.password}
        />
        {errors.password && (
          <p className="text-sm text-red-500">{errors.password.message}</p>
        )}
      </div>

      <div>
        <Label htmlFor="phone">Phone (optional)</Label>
        <Input
          id="phone"
          type="text"
          placeholder="(555) 123-4567"
          {...form.register("phone")}
          aria-invalid={!!errors.phone}
        />
        {errors.phone && (
          <p className="text-sm text-red-500">{errors.phone.message}</p>
        )}
      </div>

      <div>
        <Label htmlFor="bio">Bio (optional)</Label>
        <Input
          id="bio"
          type="text"
          placeholder="Short personal bio"
          {...form.register("bio")}
          aria-invalid={!!errors.bio}
        />
        {errors.bio && (
          <p className="text-sm text-red-500">{errors.bio.message}</p>
        )}
      </div>

      <div>
        <Label htmlFor="org_id">Organization ID (optional)</Label>
        <Input
          id="org_id"
          type="number"
          {...form.register("org_id", { valueAsNumber: true })}
          aria-invalid={!!errors.org_id}
        />
        {errors.org_id && (
          <p className="text-sm text-red-500">{errors.org_id.message}</p>
        )}
      </div>

      <div>
        <Label htmlFor="tenant_id">Tenant ID (optional)</Label>
        <Input
          id="tenant_id"
          type="number"
          {...form.register("tenant_id", { valueAsNumber: true })}
          aria-invalid={!!errors.tenant_id}
        />
        {errors.tenant_id && (
          <p className="text-sm text-red-500">{errors.tenant_id.message}</p>
        )}
      </div>

      <div>
        <div className="flex flex-wrap gap-4">
          {AllRoles.map((item) =>
            <Controller
              key={item}
              control={form.control}
              name="role"
              render={({ field: {onChange, value} }) => (
                <div className="flex flex-row items-center gap-2">
                  <Checkbox
                    id={item}
                    checked={Array.isArray(value) && value.includes(item)}
                    onCheckedChange={(checked) => {
                      if (checked) {
                        onChange([...(Array.isArray(value) ? value : []), item]);
                      } else {
                        onChange(
                          (Array.isArray(value) ? value : []).filter(
                            (val: string) => val !== item
                          )
                        );
                      }
                    }}
                  />
                  <Label htmlFor={item}>{item}</Label>
                </div>
              )}
            />
          )}
        </div>

        {errors.tenant_id && (
          <p className="text-sm text-red-500">{errors.tenant_id.message}</p>
        )}
      </div>


      {error && (
        <div className="text-center text-sm text-red-500">
          {error}
        </div>
      )}

      <div className="flex justify-end gap-2">
        <Button variant="outline" type="button" onClick={handleCancel}>
          Cancel
        </Button>
        <Button type="submit" disabled={isPending}>
          {isPending ? "Saving..." : "Save User"}
        </Button>
      </div>
    </form>
  )
}