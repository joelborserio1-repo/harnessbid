import { redirect } from "next/navigation"
import { AuthShell } from "@/components/auth/auth-shell"
import { LoginForm } from "@/components/auth/login-form"
import { getSessionUser } from "@/lib/supabase/auth-server"

export default async function LoginPage({
  searchParams,
}: {
  searchParams: Promise<{ redirect?: string }>
}) {
  const { redirect: redirectTo } = await searchParams
  const user = await getSessionUser()
  if (user) redirect(redirectTo || "/dashboard")

  return (
    <AuthShell mode="login">
      <LoginForm redirectTo={redirectTo} />
    </AuthShell>
  )
}
