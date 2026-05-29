"use server"

import { revalidatePath } from "next/cache"
import { redirect } from "next/navigation"
import { createSupabaseServerAuthClient, hasSupabaseEnv } from "@/lib/supabase/auth-server"
import { isValidSlug, slugCandidates, slugify } from "@/lib/seller/slug"

export type ActionState = {
  error?: string
  success?: boolean
  message?: string
}

const NOT_CONFIGURED: ActionState = {
  error: "Authentication is not configured yet. Please try again later.",
}

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

function safeRedirectPath(value: FormDataEntryValue | null): string {
  const path = typeof value === "string" ? value : ""
  // Only allow same-origin absolute paths to avoid open-redirects.
  if (path.startsWith("/") && !path.startsWith("//")) return path
  return "/dashboard"
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

export async function completeOnboardingAction(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  if (!hasSupabaseEnv()) return NOT_CONFIGURED

  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return { error: "You must be signed in to complete onboarding." }

  // One seller account per user in this phase. If one already exists, treat
  // onboarding as complete and route to the dashboard.
  const { data: existing } = await supabase
    .from("seller_accounts")
    .select("id")
    .eq("owner_profile_id", user.id)
    .limit(1)
    .maybeSingle()
  if (existing) redirect("/dashboard")

  const isEnterprise = String(formData.get("accountType") ?? "individual") === "enterprise"

  const name = String(formData.get("name") ?? "").trim()
  const requestedSlug = slugify(String(formData.get("slug") ?? ""))
  const location = String(formData.get("location") ?? "").trim()
  const bio = String(formData.get("bio") ?? "").trim()
  const contactEmail = String(formData.get("contactEmail") ?? "").trim()
  const contactPhone = String(formData.get("contactPhone") ?? "").trim()
  const website = String(formData.get("website") ?? "").trim()
  const abn = String(formData.get("abn") ?? "").trim()

  // --- Validation ----------------------------------------------------------
  if (!name) {
    return { error: isEnterprise ? "Enter your company name." : "Enter your seller name." }
  }
  if (!location) {
    return { error: "Enter your location or region." }
  }
  if (requestedSlug && !isValidSlug(requestedSlug)) {
    return {
      error: "Username must be 3–48 characters, lowercase letters, numbers, and hyphens only.",
    }
  }
  if (contactEmail && !EMAIL_PATTERN.test(contactEmail)) {
    return { error: "Enter a valid contact email address." }
  }
  if (isEnterprise) {
    if (!contactEmail) return { error: "Enter a business email address." }
    if (!contactPhone) return { error: "Enter a contact phone number." }
  }

  const accountType = isEnterprise ? "enterprise" : "individual"
  const candidates = slugCandidates(requestedSlug || name, 4)

  // The DB enforces slug uniqueness; retry suffixed candidates on conflict.
  let createdSellerId: string | null = null
  let lastError: string | null = null

  for (const slug of candidates) {
    const { data, error } = await supabase
      .from("seller_accounts")
      .insert({
        owner_profile_id: user.id,
        display_name: name,
        slug,
        account_type: accountType,
        bio: bio || null,
        location_text: location || null,
        website_url: website || null,
        contact_email: contactEmail || user.email || null,
        contact_phone: contactPhone || null,
        metadata: {
          onboarding_completed_at: new Date().toISOString(),
          banner_placeholder: true,
          avatar_placeholder: true,
        },
      })
      .select("id")
      .single()

    if (!error && data) {
      createdSellerId = data.id
      break
    }

    lastError = error?.message ?? null
    if (error?.code !== "23505") break // only retry on slug uniqueness conflicts
  }

  if (!createdSellerId) {
    return {
      error: lastError ?? "Could not create the seller account. Please try again.",
    }
  }

  // Enterprise applicants get an enterprise_sellers row in "in_review" status.
  // Admin approval (moving to "active") is a later phase.
  if (isEnterprise) {
    await supabase.from("enterprise_sellers").insert({
      seller_account_id: createdSellerId,
      legal_name: name,
      trading_name: name,
      onboarding_status: "in_review",
      external_reference: abn || null,
      brand_settings: {
        website: website || null,
        logo_placeholder: true,
        banner_placeholder: true,
      },
    })
  }

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
