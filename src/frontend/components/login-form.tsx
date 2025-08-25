import {Button} from "@/components/ui/button"
import {Card, CardContent, CardDescription, CardHeader, CardTitle,} from "@/components/ui/card"
import {Input} from "@/components/ui/input"
import {Label} from "@/components/ui/label"
import {useForm} from "react-hook-form"
import {schemas} from "@/api/schemas"
import {zodResolver} from "@hookform/resolvers/zod"
import {useState} from "react"
import {useApiClient} from "@/api/apiClient"
import {useNavigate} from "react-router"
import type {z} from "zod"
import {useAuthStore} from "@/store/useAuthStore"

// More descriptive than “typed”
type VerifyUserPasswordData = z.infer<typeof schemas.verifyUserPassword_Body>

const isSuccessResponse = (value: any): boolean => {
  return value !== null
    && "access_token" in value
    && "refresh_token" in value
    && "token_type" in value
    && "expires_in" in value
    && "user" in value
};


export function LoginForm() {
  const [error, setError] = useState("")
  const navigate = useNavigate()
  const {
    setAccessToken,
    setRefreshToken,
    setTokenType,
    setExpiresIn,
    setUser
  } = useAuthStore()

  const { mutate, isPending } = useApiClient.useMutation("post", "/auth/login", undefined, {
    onSuccess: (response) => {
      if (isSuccessResponse(response)) {
        setAccessToken(response.access_token)
        setRefreshToken(response.refresh_token)
        setTokenType(response.token_type)
        setExpiresIn(response.expires_in)
        setUser(response.user)

        navigate("/admin")
      }
    },
    onError: (error) => {
      console.error(error) // Log actual error for debugging

      // Check if it's an AxiosError (network/HTTP error)
      if (error && typeof error === 'object' && 'cause' in error) {
        const axiosError = error.cause as AxiosError
        if (axiosError?.response?.status === 401) {
          setError("Invalid credentials")
          return
        }
        if (axiosError?.response?.status >= 500) {
          setError("Server error. Please try again later.")
          return
        }
      }

      if (error && typeof error === 'object' && 'message' in error) {
        setError(error.message as string)
        return
      }

      // Fallback error message
      setError("An error occurred. Please try again.")
    },

  })

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<VerifyUserPasswordData>({
    resolver: zodResolver(schemas.verifyUserPassword_Body),
  })

  const onSubmit = (formData: VerifyUserPasswordData) => {
    setError("")
    mutate(formData)
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Login to your account</CardTitle>
        <CardDescription>
          Enter your email below to login to your account
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit(onSubmit)}>
          <div className="flex flex-col gap-6">

            {/* Email Field */}
            <div className="grid gap-3">
              <Label htmlFor="email">Email</Label>
              <Input
                id="email"
                type="email"
                placeholder="m@example.com"
                {...register("email")}
                aria-invalid={!!errors.email}
              />
              {errors.email && (
                <p className="text-sm text-red-500">{errors.email.message}</p>
              )}
            </div>

            {/* Password Field */}
            <div className="grid gap-3">
              <div className="flex items-center">
                <Label htmlFor="password">Password</Label>
                <a
                  href="#"
                  className="ml-auto inline-block text-sm underline-offset-4 hover:underline"
                >
                  Forgot your password?
                </a>
              </div>
              <Input
                id="password"
                type="password"
                placeholder="Enter your password"
                {...register("password")}
                aria-invalid={!!errors.password}
              />
              {errors.password && (
                <p className="text-sm text-red-500">{errors.password.message}</p>
              )}
            </div>

            {/* Error Notification */}
            {error && (
              <div className="text-sm text-red-500 text-center">
                {error}
              </div>
            )}

            {/* Submit */}
            <div className="flex flex-col gap-3">
              <Button type="submit" className="w-full" disabled={isPending}>
                {isPending ? "Logging in..." : "Login"}
              </Button>
            </div>
          </div>
        </form>
      </CardContent>
    </Card>
  )
}