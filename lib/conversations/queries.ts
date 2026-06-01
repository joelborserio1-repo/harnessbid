import {
  createSupabaseServerAuthClient,
  hasSupabaseEnv,
} from "@/lib/supabase/auth-server"

export type ConversationListItem = {
  id: string
  role: "buyer" | "seller"
  counterpartName: string
  enterprise: boolean
  listingTitle: string | null
  listingHref: string | null
  lastBody: string
  lastAt: string
  unread: number
}

export type ConversationMessage = {
  id: string
  mine: boolean
  body: string
  createdAt: string
  read: boolean
  attachmentUrl?: string | null
  attachmentType?: string | null
}

export type ConversationDetail = {
  id: string
  role: "buyer" | "seller"
  counterpartName: string
  enterprise: boolean
  listingTitle: string | null
  listingHref: string | null
  messages: ConversationMessage[]
}

type ConvRow = {
  id: string
  buyer_profile_id: string
  seller_account_id: string
  seller_owner_profile_id: string
  horse_listing_id: string | null
  marketplace_listing_id: string | null
  subject: string | null
  last_message_at: string
}

async function resolveLabels(
  supabase: Awaited<ReturnType<typeof createSupabaseServerAuthClient>>,
  convs: ConvRow[],
) {
  const sellerIds = [...new Set(convs.map((c) => c.seller_account_id))]
  const buyerIds = [...new Set(convs.map((c) => c.buyer_profile_id))]
  const horseIds = [...new Set(convs.map((c) => c.horse_listing_id).filter(Boolean) as string[])]
  const marketIds = [...new Set(convs.map((c) => c.marketplace_listing_id).filter(Boolean) as string[])]

  const [sellers, buyers, horses, markets] = await Promise.all([
    sellerIds.length
      ? supabase.from("seller_accounts").select("id, display_name, account_type").in("id", sellerIds)
      : Promise.resolve({ data: [] as Array<{ id: string; display_name: string; account_type: string }> }),
    buyerIds.length
      ? supabase.from("profiles").select("id, display_name, full_name").in("id", buyerIds)
      : Promise.resolve({ data: [] as Array<{ id: string; display_name: string | null; full_name: string | null }> }),
    horseIds.length
      ? supabase.from("horse_listings").select("id, title, slug, sale_mode").in("id", horseIds)
      : Promise.resolve({ data: [] as Array<{ id: string; title: string; slug: string; sale_mode: string }> }),
    marketIds.length
      ? supabase.from("marketplace_listings").select("id, title, slug").in("id", marketIds)
      : Promise.resolve({ data: [] as Array<{ id: string; title: string; slug: string }> }),
  ])

  return {
    sellers: new Map((sellers.data ?? []).map((s) => [s.id, s])),
    buyers: new Map((buyers.data ?? []).map((b) => [b.id, b])),
    horses: new Map((horses.data ?? []).map((h) => [h.id, h])),
    markets: new Map((markets.data ?? []).map((m) => [m.id, m])),
  }
}

function listingInfo(
  c: ConvRow,
  labels: Awaited<ReturnType<typeof resolveLabels>>,
): { title: string | null; href: string | null } {
  if (c.horse_listing_id) {
    const h = labels.horses.get(c.horse_listing_id)
    if (!h) return { title: "Horse listing", href: null }
    const href = h.sale_mode === "auction" ? `/auctions/${h.slug}` : `/horses/buy-now/${h.slug}`
    return { title: h.title, href }
  }
  if (c.marketplace_listing_id) {
    const m = labels.markets.get(c.marketplace_listing_id)
    return m ? { title: m.title, href: `/marketplace/${m.slug}` } : { title: "Marketplace listing", href: null }
  }
  return { title: c.subject, href: null }
}

/** Inbox: the current user's conversations sorted by latest activity. */
export async function getConversations(): Promise<ConversationListItem[]> {
  if (!hasSupabaseEnv()) return []
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return []

  const { data: convs } = await supabase
    .from("conversations")
    .select(
      "id, buyer_profile_id, seller_account_id, seller_owner_profile_id, horse_listing_id, marketplace_listing_id, subject, last_message_at",
    )
    .order("last_message_at", { ascending: false })
    .limit(100)
  if (!convs || convs.length === 0) return []

  const labels = await resolveLabels(supabase, convs)
  const { data: msgs } = await supabase
    .from("messages")
    .select("conversation_id, sender_profile_id, body, read_at, created_at")
    .in("conversation_id", convs.map((c) => c.id))
    .order("created_at", { ascending: true })

  const byConv = new Map<string, Array<{ sender: string; body: string; read: boolean; createdAt: string }>>()
  for (const m of msgs ?? []) {
    const arr = byConv.get(m.conversation_id) ?? []
    arr.push({ sender: m.sender_profile_id, body: m.body, read: m.read_at !== null, createdAt: m.created_at })
    byConv.set(m.conversation_id, arr)
  }

  return convs.map((c) => {
    const role: "buyer" | "seller" = c.buyer_profile_id === user.id ? "buyer" : "seller"
    const seller = labels.sellers.get(c.seller_account_id)
    const buyer = labels.buyers.get(c.buyer_profile_id)
    const counterpartName =
      role === "buyer"
        ? seller?.display_name ?? "Seller"
        : buyer?.display_name || buyer?.full_name || "Buyer"
    const thread = byConv.get(c.id) ?? []
    const last = thread[thread.length - 1]
    const { title, href } = listingInfo(c, labels)
    return {
      id: c.id,
      role,
      counterpartName,
      enterprise: role === "buyer" && seller?.account_type === "enterprise",
      listingTitle: title,
      listingHref: href,
      lastBody: last?.body ?? "",
      lastAt: last?.createdAt ?? c.last_message_at,
      unread: thread.filter((m) => m.sender !== user.id && !m.read).length,
    }
  })
}

/** A single conversation with its full message thread (participant-only). */
export async function getConversation(id: string): Promise<ConversationDetail | null> {
  if (!hasSupabaseEnv()) return null
  const supabase = await createSupabaseServerAuthClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()
  if (!user) return null

  const { data: c } = await supabase
    .from("conversations")
    .select(
      "id, buyer_profile_id, seller_account_id, seller_owner_profile_id, horse_listing_id, marketplace_listing_id, subject, last_message_at",
    )
    .eq("id", id)
    .maybeSingle()
  if (!c) return null // RLS denies non-participants

  const labels = await resolveLabels(supabase, [c])
  const { data: msgs } = await supabase
    .from("messages")
    .select("id, sender_profile_id, body, read_at, created_at, attachment_url, attachment_type")
    .eq("conversation_id", id)
    .order("created_at", { ascending: true })

  const role: "buyer" | "seller" = c.buyer_profile_id === user.id ? "buyer" : "seller"
  const seller = labels.sellers.get(c.seller_account_id)
  const buyer = labels.buyers.get(c.buyer_profile_id)
  const { title, href } = listingInfo(c, labels)

  return {
    id: c.id,
    role,
    counterpartName:
      role === "buyer"
        ? seller?.display_name ?? "Seller"
        : buyer?.display_name || buyer?.full_name || "Buyer",
    enterprise: role === "buyer" && seller?.account_type === "enterprise",
    listingTitle: title,
    listingHref: href,
    messages: (msgs ?? []).map((m) => ({
      id: m.id,
      mine: m.sender_profile_id === user.id,
      body: m.body,
      createdAt: m.created_at,
      read: m.read_at !== null,
      attachmentUrl: (m as { attachment_url?: string | null }).attachment_url ?? null,
      attachmentType: (m as { attachment_type?: string | null }).attachment_type ?? null,
    })),
  }
}
