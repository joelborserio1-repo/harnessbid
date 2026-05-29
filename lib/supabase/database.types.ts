export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[]

type Enums = {
  app_role: "buyer" | "seller" | "enterprise_seller" | "admin"
  auction_status: "draft" | "scheduled" | "live" | "extended" | "closed" | "settled" | "cancelled"
  category_type: "horse" | "marketplace" | "service" | "content"
  enquiry_status: "open" | "replied" | "closed" | "spam" | "archived"
  enterprise_tier: "standard" | "preferred" | "premier" | "strategic"
  onboarding_status: "draft" | "invited" | "in_review" | "active" | "paused" | "offboarded"
  horse_gait: "pacer" | "trotter" | "dual_gaited" | "unknown"
  horse_sex: "colt" | "filly" | "gelding" | "mare" | "stallion" | "ridgling" | "unknown"
  listing_status: "draft" | "pending_review" | "published" | "paused" | "under_offer" | "sold" | "expired" | "rejected" | "archived"
  marketplace_condition: "new" | "excellent" | "good" | "fair" | "used" | "for_parts" | "not_applicable"
  sale_mode: "classified" | "buy_now" | "auction" | "private_treaty"
  seller_account_type: "individual" | "business" | "enterprise"
  verification_status: "unverified" | "pending" | "verified" | "rejected" | "suspended"
}

type ProfileRow = {
  id: string
  email: string | null
  full_name: string | null
  display_name: string | null
  phone: string | null
  avatar_url: string | null
  role: Enums["app_role"]
  country_code: string | null
  timezone: string | null
  is_active: boolean
  last_seen_at: string | null
  metadata: Json
  created_at: string
  updated_at: string
}

type EnterpriseSellerRow = {
  id: string
  seller_account_id: string
  legal_name: string | null
  trading_name: string | null
  tier: Enums["enterprise_tier"]
  onboarding_status: Enums["onboarding_status"]
  account_manager_profile_id: string | null
  external_reference: string | null
  contract_starts_at: string | null
  contract_ends_at: string | null
  featured_until: string | null
  brand_settings: Json
  billing_settings: Json
  created_at: string
  updated_at: string
}

type WatchlistRow = {
  id: string
  profile_id: string
  horse_listing_id: string | null
  marketplace_listing_id: string | null
  auction_id: string | null
  note: string | null
  created_at: string
}

type EnquiryRow = {
  id: string
  sender_profile_id: string | null
  seller_account_id: string
  horse_listing_id: string | null
  marketplace_listing_id: string | null
  sale_event_id: string | null
  subject: string | null
  message: string
  contact_name: string | null
  contact_email: string | null
  contact_phone: string | null
  status: Enums["enquiry_status"]
  read_at: string | null
  replied_at: string | null
  metadata: Json
  created_at: string
  updated_at: string
}

type NotificationRow = {
  id: string
  profile_id: string
  type: string
  title: string
  body: string | null
  link_url: string | null
  related_entity_type: string | null
  related_entity_id: string | null
  read_at: string | null
  created_at: string
}

type SavedSearchRow = {
  id: string
  profile_id: string
  name: string
  search_type: string
  filters: Json
  alert_enabled: boolean
  created_at: string
  updated_at: string
}

type AuctionRow = {
  id: string
  horse_listing_id: string | null
  marketplace_listing_id: string | null
  sale_event_id: string | null
  status: Enums["auction_status"]
  currency: string
  starts_at: string
  ends_at: string
  soft_close_seconds: number
  starting_bid: number
  reserve_price: number | null
  bid_increment: number
  current_bid: number | null
  bid_count: number
  winner_profile_id: string | null
  reserve_met: boolean
  settled_at: string | null
  metadata: Json
  created_at: string
  updated_at: string
}

type CategoryRow = {
  id: string
  parent_id: string | null
  category_type: Enums["category_type"]
  name: string
  slug: string
  description: string | null
  icon_name: string | null
  sort_order: number
  is_active: boolean
  metadata: Json
  created_at: string
  updated_at: string
}

type HorseListingRow = {
  id: string
  seller_account_id: string
  category_id: string | null
  sale_event_id: string | null
  title: string
  slug: string
  status: Enums["listing_status"]
  sale_mode: Enums["sale_mode"]
  short_description: string | null
  description: string | null
  currency: string
  asking_price: number | null
  location_text: string | null
  city: string | null
  region: string | null
  country_code: string | null
  foaled_date: string | null
  age_years: number | null
  color: string | null
  sex: Enums["horse_sex"]
  gait: Enums["horse_gait"]
  breed: string
  sire: string | null
  dam: string | null
  dam_sire: string | null
  best_mile: string | null
  starts: number | null
  wins: number | null
  places: number | null
  earnings: number | null
  vet_report_url: string | null
  pedigree: Json
  racing_record: Json
  specs: Json
  metadata: Json
  view_count: number
  watcher_count: number
  published_at: string | null
  sold_at: string | null
  expires_at: string | null
  created_at: string
  updated_at: string
}

type ListingImageRow = {
  id: string
  horse_listing_id: string | null
  marketplace_listing_id: string | null
  storage_bucket: string | null
  storage_path: string | null
  image_url: string | null
  alt_text: string | null
  position: number
  is_primary: boolean
  metadata: Json
  created_at: string
  updated_at: string
}

type MarketplaceListingRow = {
  id: string
  seller_account_id: string
  category_id: string | null
  title: string
  slug: string
  status: Enums["listing_status"]
  sale_mode: Enums["sale_mode"]
  description: string | null
  condition: Enums["marketplace_condition"]
  currency: string
  price: number | null
  accepts_offers: boolean
  shipping_available: boolean
  domestic_shipping_price: number | null
  international_shipping_notes: string | null
  brand: string | null
  model: string | null
  manufacture_year: number | null
  location_text: string | null
  city: string | null
  region: string | null
  country_code: string | null
  specs: Json
  metadata: Json
  view_count: number
  watcher_count: number
  featured_until: string | null
  published_at: string | null
  sold_at: string | null
  expires_at: string | null
  created_at: string
  updated_at: string
}

type SellerAccountRow = {
  id: string
  owner_profile_id: string
  account_type: Enums["seller_account_type"]
  display_name: string
  slug: string
  bio: string | null
  logo_url: string | null
  website_url: string | null
  contact_email: string | null
  contact_phone: string | null
  location_text: string | null
  city: string | null
  region: string | null
  country_code: string | null
  response_time_label: string | null
  verification_status: Enums["verification_status"]
  rating: number
  review_count: number
  total_sales: number
  total_listings: number
  is_active: boolean
  verified_at: string | null
  metadata: Json
  created_at: string
  updated_at: string
}

// Read-only integration phase: Insert/Update mirror Row so the schema satisfies
// @supabase/supabase-js GenericTable. Regenerate via `npm run supabase:types`
// once the Supabase CLI is available to produce exact write shapes + relationships.
type TableFrom<Row> = {
  Row: Row
  Insert: Partial<Row>
  Update: Partial<Row>
  Relationships: []
}

export type Database = {
  public: {
    Tables: {
      profiles: TableFrom<ProfileRow>
      enterprise_sellers: TableFrom<EnterpriseSellerRow>
      watchlists: TableFrom<WatchlistRow>
      enquiries: TableFrom<EnquiryRow>
      notifications: TableFrom<NotificationRow>
      saved_searches: TableFrom<SavedSearchRow>
      auctions: TableFrom<AuctionRow>
      categories: TableFrom<CategoryRow>
      horse_listings: TableFrom<HorseListingRow>
      listing_images: TableFrom<ListingImageRow>
      marketplace_listings: TableFrom<MarketplaceListingRow>
      seller_accounts: TableFrom<SellerAccountRow>
    }
    Views: {
      [_ in never]: never
    }
    Functions: {
      [_ in never]: never
    }
    Enums: Enums
    CompositeTypes: {
      [_ in never]: never
    }
  }
}
