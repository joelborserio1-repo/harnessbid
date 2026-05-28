export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[]

export type Database = {
  public: {
    Tables: {
      auctions: {
        Row: {
          id: string
          horse_listing_id: string | null
          marketplace_listing_id: string | null
          sale_event_id: string | null
          status: Database["public"]["Enums"]["auction_status"]
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
        Insert: {
          id?: string
          horse_listing_id?: string | null
          marketplace_listing_id?: string | null
          sale_event_id?: string | null
          status?: Database["public"]["Enums"]["auction_status"]
          currency?: string
          starts_at: string
          ends_at: string
          soft_close_seconds?: number
          starting_bid: number
          reserve_price?: number | null
          bid_increment?: number
          current_bid?: number | null
          bid_count?: number
          winner_profile_id?: string | null
          reserve_met?: boolean
          settled_at?: string | null
          metadata?: Json
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          horse_listing_id?: string | null
          marketplace_listing_id?: string | null
          sale_event_id?: string | null
          status?: Database["public"]["Enums"]["auction_status"]
          currency?: string
          starts_at?: string
          ends_at?: string
          soft_close_seconds?: number
          starting_bid?: number
          reserve_price?: number | null
          bid_increment?: number
          current_bid?: number | null
          bid_count?: number
          winner_profile_id?: string | null
          reserve_met?: boolean
          settled_at?: string | null
          metadata?: Json
          created_at?: string
          updated_at?: string
        }
        Relationships: []
      }
      categories: {
        Row: {
          id: string
          parent_id: string | null
          category_type: Database["public"]["Enums"]["category_type"]
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
        Insert: {
          id?: string
          parent_id?: string | null
          category_type: Database["public"]["Enums"]["category_type"]
          name: string
          slug: string
          description?: string | null
          icon_name?: string | null
          sort_order?: number
          is_active?: boolean
          metadata?: Json
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          parent_id?: string | null
          category_type?: Database["public"]["Enums"]["category_type"]
          name?: string
          slug?: string
          description?: string | null
          icon_name?: string | null
          sort_order?: number
          is_active?: boolean
          metadata?: Json
          created_at?: string
          updated_at?: string
        }
        Relationships: []
      }
      horse_listings: {
        Row: {
          id: string
          seller_account_id: string
          category_id: string | null
          sale_event_id: string | null
          title: string
          slug: string
          status: Database["public"]["Enums"]["listing_status"]
          sale_mode: Database["public"]["Enums"]["sale_mode"]
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
          sex: Database["public"]["Enums"]["horse_sex"]
          gait: Database["public"]["Enums"]["horse_gait"]
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
        Insert: {
          id?: string
          seller_account_id: string
          category_id?: string | null
          sale_event_id?: string | null
          title: string
          slug: string
          status?: Database["public"]["Enums"]["listing_status"]
          sale_mode?: Database["public"]["Enums"]["sale_mode"]
          short_description?: string | null
          description?: string | null
          currency?: string
          asking_price?: number | null
          location_text?: string | null
          city?: string | null
          region?: string | null
          country_code?: string | null
          foaled_date?: string | null
          age_years?: number | null
          color?: string | null
          sex?: Database["public"]["Enums"]["horse_sex"]
          gait?: Database["public"]["Enums"]["horse_gait"]
          breed: string
          sire?: string | null
          dam?: string | null
          dam_sire?: string | null
          best_mile?: string | null
          starts?: number | null
          wins?: number | null
          places?: number | null
          earnings?: number | null
          vet_report_url?: string | null
          pedigree?: Json
          racing_record?: Json
          specs?: Json
          metadata?: Json
          view_count?: number
          watcher_count?: number
          published_at?: string | null
          sold_at?: string | null
          expires_at?: string | null
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          seller_account_id?: string
          category_id?: string | null
          sale_event_id?: string | null
          title?: string
          slug?: string
          status?: Database["public"]["Enums"]["listing_status"]
          sale_mode?: Database["public"]["Enums"]["sale_mode"]
          short_description?: string | null
          description?: string | null
          currency?: string
          asking_price?: number | null
          location_text?: string | null
          city?: string | null
          region?: string | null
          country_code?: string | null
          foaled_date?: string | null
          age_years?: number | null
          color?: string | null
          sex?: Database["public"]["Enums"]["horse_sex"]
          gait?: Database["public"]["Enums"]["horse_gait"]
          breed?: string
          sire?: string | null
          dam?: string | null
          dam_sire?: string | null
          best_mile?: string | null
          starts?: number | null
          wins?: number | null
          places?: number | null
          earnings?: number | null
          vet_report_url?: string | null
          pedigree?: Json
          racing_record?: Json
          specs?: Json
          metadata?: Json
          view_count?: number
          watcher_count?: number
          published_at?: string | null
          sold_at?: string | null
          expires_at?: string | null
          created_at?: string
          updated_at?: string
        }
        Relationships: []
      }
      listing_images: {
        Row: {
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
        Insert: {
          id?: string
          horse_listing_id?: string | null
          marketplace_listing_id?: string | null
          storage_bucket?: string | null
          storage_path?: string | null
          image_url?: string | null
          alt_text?: string | null
          position?: number
          is_primary?: boolean
          metadata?: Json
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          horse_listing_id?: string | null
          marketplace_listing_id?: string | null
          storage_bucket?: string | null
          storage_path?: string | null
          image_url?: string | null
          alt_text?: string | null
          position?: number
          is_primary?: boolean
          metadata?: Json
          created_at?: string
          updated_at?: string
        }
        Relationships: []
      }
      marketplace_listings: {
        Row: {
          id: string
          seller_account_id: string
          category_id: string | null
          title: string
          slug: string
          status: Database["public"]["Enums"]["listing_status"]
          sale_mode: Database["public"]["Enums"]["sale_mode"]
          description: string | null
          condition: Database["public"]["Enums"]["marketplace_condition"]
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
        Insert: {
          id?: string
          seller_account_id: string
          category_id?: string | null
          title: string
          slug: string
          status?: Database["public"]["Enums"]["listing_status"]
          sale_mode?: Database["public"]["Enums"]["sale_mode"]
          description?: string | null
          condition?: Database["public"]["Enums"]["marketplace_condition"]
          currency?: string
          price?: number | null
          accepts_offers?: boolean
          shipping_available?: boolean
          domestic_shipping_price?: number | null
          international_shipping_notes?: string | null
          brand?: string | null
          model?: string | null
          manufacture_year?: number | null
          location_text?: string | null
          city?: string | null
          region?: string | null
          country_code?: string | null
          specs?: Json
          metadata?: Json
          view_count?: number
          watcher_count?: number
          featured_until?: string | null
          published_at?: string | null
          sold_at?: string | null
          expires_at?: string | null
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          seller_account_id?: string
          category_id?: string | null
          title?: string
          slug?: string
          status?: Database["public"]["Enums"]["listing_status"]
          sale_mode?: Database["public"]["Enums"]["sale_mode"]
          description?: string | null
          condition?: Database["public"]["Enums"]["marketplace_condition"]
          currency?: string
          price?: number | null
          accepts_offers?: boolean
          shipping_available?: boolean
          domestic_shipping_price?: number | null
          international_shipping_notes?: string | null
          brand?: string | null
          model?: string | null
          manufacture_year?: number | null
          location_text?: string | null
          city?: string | null
          region?: string | null
          country_code?: string | null
          specs?: Json
          metadata?: Json
          view_count?: number
          watcher_count?: number
          featured_until?: string | null
          published_at?: string | null
          sold_at?: string | null
          expires_at?: string | null
          created_at?: string
          updated_at?: string
        }
        Relationships: []
      }
      seller_accounts: {
        Row: {
          id: string
          owner_profile_id: string
          account_type: Database["public"]["Enums"]["seller_account_type"]
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
          verification_status: Database["public"]["Enums"]["verification_status"]
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
        Insert: {
          id?: string
          owner_profile_id: string
          account_type?: Database["public"]["Enums"]["seller_account_type"]
          display_name: string
          slug: string
          bio?: string | null
          logo_url?: string | null
          website_url?: string | null
          contact_email?: string | null
          contact_phone?: string | null
          location_text?: string | null
          city?: string | null
          region?: string | null
          country_code?: string | null
          response_time_label?: string | null
          verification_status?: Database["public"]["Enums"]["verification_status"]
          rating?: number
          review_count?: number
          total_sales?: number
          total_listings?: number
          is_active?: boolean
          verified_at?: string | null
          metadata?: Json
          created_at?: string
          updated_at?: string
        }
        Update: {
          id?: string
          owner_profile_id?: string
          account_type?: Database["public"]["Enums"]["seller_account_type"]
          display_name?: string
          slug?: string
          bio?: string | null
          logo_url?: string | null
          website_url?: string | null
          contact_email?: string | null
          contact_phone?: string | null
          location_text?: string | null
          city?: string | null
          region?: string | null
          country_code?: string | null
          response_time_label?: string | null
          verification_status?: Database["public"]["Enums"]["verification_status"]
          rating?: number
          review_count?: number
          total_sales?: number
          total_listings?: number
          is_active?: boolean
          verified_at?: string | null
          metadata?: Json
          created_at?: string
          updated_at?: string
        }
        Relationships: []
      }
    }
    Views: Record<string, never>
    Functions: Record<string, never>
    Enums: {
      app_role: "buyer" | "seller" | "enterprise_seller" | "admin"
      auction_status: "draft" | "scheduled" | "live" | "extended" | "closed" | "settled" | "cancelled"
      category_type: "horse" | "marketplace" | "service" | "content"
      horse_gait: "pacer" | "trotter" | "dual_gaited" | "unknown"
      horse_sex: "colt" | "filly" | "gelding" | "mare" | "stallion" | "ridgling" | "unknown"
      listing_status: "draft" | "pending_review" | "published" | "paused" | "under_offer" | "sold" | "expired" | "rejected" | "archived"
      marketplace_condition: "new" | "excellent" | "good" | "fair" | "used" | "for_parts" | "not_applicable"
      sale_mode: "classified" | "buy_now" | "auction" | "private_treaty"
      seller_account_type: "individual" | "business" | "enterprise"
      verification_status: "unverified" | "pending" | "verified" | "rejected" | "suspended"
    }
    CompositeTypes: Record<string, never>
  }
}
