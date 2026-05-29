import { redirect } from "next/navigation"
import { AuthShell } from "@/components/auth/auth-shell"
import { RegisterForm } from "@/components/auth/register-form"
import { getSessionUser } from "@/lib/supabase/auth-server"

export default async function RegisterPage({
  searchParams,
}: {
  searchParams: Promise<{ redirect?: string }>
}) {
  const { redirect: redirectTo } = await searchParams
  const user = await getSessionUser()
  if (user) redirect(redirectTo || "/dashboard")

  return (
    <AuthShell mode="register">
      <RegisterForm redirectTo={redirectTo} />
    </AuthShell>
  )
}
