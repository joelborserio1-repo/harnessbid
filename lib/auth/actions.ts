"use server"

import { revalidatePath } from "next/cache"
import { redirect } from "next/navigation"
import { createSupabaseServerAuthClient, hasSupabaseEnv } from "@/lib/supabase/auth-server"

export type ActionState = {
  error?: string
  success?: boolean
  message?: string
}

const NOT_CONFIGURED: ActionState = {
  error: "Authentication is not configured yet. Please try again later.",
}

function safeRedirectPath(value: FormDataEntryValue | null): string {
  const path = typeof value === "string" ? value : ""
  // Only allow same-origin absolute paths to avoid open-redirects.
  if (path.startsWith("/") && !path.startsWith("//")) return path
  return "/dashboard"
}

function slugify(value: string): string {
  return value
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
}

export async function signInAction(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  if (!hasSupabaseEnv()) return NOT_CONFIGURED

  const email = String(formData.get("email") ?? "").trim()
  const password = String(formData.get("password") ?? "")

  if (!email || !password) {
    return { error: "Enter your email and password." }
  }

  const supabase = await createSupabaseServerAuthClient()
  const { error } = await supabase.auth.signInWithPassword({ email, password })

  if (error) {
    return { error: "Invalid email or password." }
  }

  revalidatePath("/", "layout")
  redirect(safeRedirectPath(formData.get("redirect")))
}

export async function signUpAction(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  if (!hasSupabaseEnv()) return NOT_CONFIGURED

  const fullName = String(formData.get("fullName") ?? "").trim()
  const email = String(formData.get("email") ?? "").trim()
  const password = String(formData.get("password") ?? "")
  const countryRaw = String(formData.get("country") ?? "").trim()

  if (!email || !password) {
    return { error: "Enter your email and password." }
  }
  if (password.length < 8) {
    return { error: "Password must be at least 8 characters." }
  }

  const supabase = await createSupabaseServerAuthClient()
  const { data, error } = await supabase.auth.signUp({
    email,
    password,
    options: {
      data: { full_name: fullName || null },
    },
  })

  if (error) {
    return { error: error.message }
  }

  // If email confirmation is enabled there is no session yet; the profile row
  // is created by the on_auth_user_created trigger. When confirmation is off,
  // we have a session and upsert the profile here as a resilient fallback.
  if (data.user && data.session) {
    await supabase.from("profiles").upsert(
      {
        id: data.user.id,
        email,
        full_name: fullName || null,
        display_name: fullName || email.split("@")[0],
        country_code: countryRaw ? countryRaw.slice(0, 2).toUpperCase() : null,
        role: "buyer",
      },
      { onConflict: "id" },
    )
    revalidatePath("/", "layout")
    redirect(safeRedirectPath(formData.get("redirect")))
  }

  // No session => email confirmation required.
  return {
    success: true,
    message: "Check your email to confirm your account before signing in.",
  }
}

export async function signOutAction(): Promise<void> {
  if (hasSupabaseEnv()) {
    const supabase = await createSupabaseServerAuthClient()
    await supabase.auth.signOut()
  }
  revalidatePath("/", "layout")
  redirect("/")
}

export async function createSellerAccountAction(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  if (!hasSupabaseEnv()) return NOT_CONFIGURED

  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { error: "You must be signed in to create a seller account." }

  const displayName = String(formData.get("displayName") ?? "").trim()
  const accountTypeRaw = String(formData.get("accountType") ?? "individual")
  const bio = String(formData.get("bio") ?? "").trim()
  const location = String(formData.get("location") ?? "").trim()
  const contactEmail = String(formData.get("contactEmail") ?? "").trim()

  if (!displayName) {
    return { error: "Enter a seller or business name." }
  }

  const accountType =
    accountTypeRaw === "business" ? "business" : "individual"

  const baseSlug = slugify(displayName) || `seller-${user.id.slice(0, 8)}`

  // The DB enforces slug uniqueness; retry with a suffix on conflict (23505).
  let lastError: string | null = null
  for (let attempt = 0; attempt < 3; attempt++) {
    const slug =
      attempt === 0 ? baseSlug : `${baseSlug}-${Math.random().toString(36).slice(2, 6)}`

    const { error } = await supabase.from("seller_accounts").insert({
      owner_profile_id: user.id,
      display_name: displayName,
      slug,
      account_type: accountType,
      bio: bio || null,
      location_text: location || null,
      contact_email: contactEmail || user.email || null,
    })

    if (!error) {
      // Promote the profile role so the dashboard reflects seller status.
      await supabase
        .from("profiles")
        .update({ role: "seller" })
        .eq("id", user.id)
        .eq("role", "buyer")

      revalidatePath("/dashboard")
      revalidatePath("/", "layout")
      redirect("/dashboard")
    }

    lastError = error.message
    if (error.code !== "23505") break
  }

  return {
    error: lastError ?? "Could not create the seller account. Please try again.",
  }
}

export async function requestEnterpriseAction(
  _prevState: ActionState,
  _formData: FormData,
): Promise<ActionState> {
  if (!hasSupabaseEnv()) return NOT_CONFIGURED

  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { error: "You must be signed in." }

  const { data: account } = await supabase
    .from("seller_accounts")
    .select("id, metadata")
    .eq("owner_profile_id", user.id)
    .order("created_at", { ascending: true })
    .limit(1)
    .maybeSingle()

  if (!account) {
    return { error: "Create a seller account first to request enterprise access." }
  }

  const metadata = {
    ...((account.metadata ?? {}) as Record<string, unknown>),
    enterprise_requested: true,
    enterprise_requested_at: new Date().toISOString(),
  }

  const { error } = await supabase
    .from("seller_accounts")
    .update({ metadata })
    .eq("id", account.id)

  if (error) {
    return { error: "Could not submit your enterprise request. Please try again." }
  }

  revalidatePath("/dashboard")
  return {
    success: true,
    message: "Enterprise request received. Our team will be in touch to begin onboarding.",
  }
}
