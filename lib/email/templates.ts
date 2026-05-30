/**
 * Branded HTML email templates for HarnessBid. Inline styles only (email
 * clients strip <style>/external CSS). Brand: navy #14294A, gold #E0A33C,
 * paper #F1ECE3. Pure functions — no I/O — so they're trivially testable.
 */

const NAVY = "#14294A"
const GOLD = "#E0A33C"
const PAPER = "#F1ECE3"
const INK = "#1B2B47"
const MUTED = "#5B6478"

function siteUrl(): string {
  return process.env.NEXT_PUBLIC_SITE_URL || "https://harnessbid.com"
}

/** Shared shell: header wordmark, content slot, footer. Returns full HTML doc. */
function shell(opts: { title: string; bodyHtml: string; preheader?: string }): string {
  const base = siteUrl()
  return `<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>${opts.title}</title></head>
<body style="margin:0;padding:0;background:${PAPER};font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:${INK};">
${opts.preheader ? `<div style="display:none;max-height:0;overflow:hidden;opacity:0;">${opts.preheader}</div>` : ""}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:${PAPER};padding:24px 0;">
<tr><td align="center">
  <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
    <tr><td style="padding:8px 24px 20px;">
      <a href="${base}" style="text-decoration:none;color:${NAVY};font-size:22px;font-weight:700;letter-spacing:0.5px;">HarnessBid</a>
      <span style="color:${MUTED};font-size:12px;"> &nbsp;Where champions change hands</span>
    </td></tr>
    <tr><td style="background:#ffffff;border:1px solid #e3e6ec;border-radius:6px;padding:28px 28px 24px;">
      ${opts.bodyHtml}
    </td></tr>
    <tr><td style="padding:18px 24px;color:${MUTED};font-size:12px;line-height:1.6;">
      You're receiving this because you have a HarnessBid account.
      <a href="${base}/dashboard/notifications" style="color:${NAVY};">Manage preferences</a> ·
      <a href="${base}" style="color:${NAVY};">HarnessBid</a>
    </td></tr>
  </table>
</td></tr></table>
</body></html>`
}

function button(label: string, href: string): string {
  return `<a href="${href}" style="display:inline-block;background:${GOLD};color:${NAVY};text-decoration:none;font-weight:600;font-size:14px;padding:11px 22px;border-radius:4px;">${label}</a>`
}

function heading(text: string): string {
  return `<h1 style="margin:0 0 12px;font-size:20px;color:${NAVY};font-weight:600;">${text}</h1>`
}

function para(text: string): string {
  return `<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:${INK};">${text}</p>`
}

export type RenderedEmail = { subject: string; html: string; text: string }

/* --- Saved-search alert ---------------------------------------------------- */
export function savedSearchAlertEmail(opts: {
  searchName: string
  matches: { title: string; href: string; price?: string; subtitle?: string }[]
}): RenderedEmail {
  const base = siteUrl()
  const rows = opts.matches
    .slice(0, 8)
    .map(
      (m) => `<tr><td style="padding:10px 0;border-bottom:1px solid #eef0f4;">
        <a href="${base}${m.href}" style="color:${NAVY};font-weight:600;font-size:15px;text-decoration:none;">${m.title}</a>
        ${m.subtitle ? `<div style="color:${MUTED};font-size:13px;margin-top:2px;">${m.subtitle}</div>` : ""}
        ${m.price ? `<div style="color:${INK};font-size:14px;margin-top:2px;">${m.price}</div>` : ""}
      </td></tr>`,
    )
    .join("")
  const body = `
    ${heading(`New matches for "${opts.searchName}"`)}
    ${para(`${opts.matches.length} listing${opts.matches.length === 1 ? "" : "s"} now match your saved search.`)}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">${rows}</table>
    <div style="margin-top:22px;">${button("View all matches", `${base}/search`)}</div>`
  return {
    subject: `New matches for "${opts.searchName}" — HarnessBid`,
    html: shell({ title: "Saved search matches", bodyHtml: body, preheader: `${opts.matches.length} new matches` }),
    text: `New matches for "${opts.searchName}":\n${opts.matches.map((m) => `- ${m.title} ${base}${m.href}`).join("\n")}`,
  }
}

/* --- Outbid notice --------------------------------------------------------- */
export function outbidEmail(opts: { horseName: string; auctionHref: string; currentBid: string }): RenderedEmail {
  const base = siteUrl()
  const body = `
    ${heading("You've been outbid")}
    ${para(`Another bidder has placed a higher bid on <strong>${opts.horseName}</strong>. The current bid is now <strong>${opts.currentBid}</strong>.`)}
    ${para("If you'd like to stay in the running, place a new bid before the auction closes.")}
    <div style="margin-top:8px;">${button("Place a new bid", `${base}${opts.auctionHref}`)}</div>`
  return {
    subject: `You've been outbid on ${opts.horseName} — HarnessBid`,
    html: shell({ title: "You've been outbid", bodyHtml: body, preheader: `Current bid ${opts.currentBid}` }),
    text: `You've been outbid on ${opts.horseName}. Current bid ${opts.currentBid}. Bid again: ${base}${opts.auctionHref}`,
  }
}

/* --- Auction won ----------------------------------------------------------- */
export function auctionWonEmail(opts: { horseName: string; auctionHref: string; finalBid: string }): RenderedEmail {
  const base = siteUrl()
  const body = `
    ${heading("Congratulations — you won the auction")}
    ${para(`Your bid of <strong>${opts.finalBid}</strong> won <strong>${opts.horseName}</strong>.`)}
    ${para("The seller will be in touch regarding settlement and collection. You can review the lot and message the seller from your dashboard.")}
    <div style="margin-top:8px;">${button("View your winning lot", `${base}${opts.auctionHref}`)}</div>`
  return {
    subject: `You won ${opts.horseName} — HarnessBid`,
    html: shell({ title: "Auction won", bodyHtml: body, preheader: `Winning bid ${opts.finalBid}` }),
    text: `Congratulations — your bid of ${opts.finalBid} won ${opts.horseName}. ${base}${opts.auctionHref}`,
  }
}

/* --- Seller: new enquiry --------------------------------------------------- */
export function enquiryReceivedEmail(opts: { listingTitle: string; messageHref: string; preview: string }): RenderedEmail {
  const base = siteUrl()
  const body = `
    ${heading("New enquiry on your listing")}
    ${para(`A buyer has enquired about <strong>${opts.listingTitle}</strong>:`)}
    <blockquote style="margin:0 0 16px;padding:12px 16px;background:${PAPER};border-left:3px solid ${GOLD};color:${INK};font-size:14px;">${opts.preview}</blockquote>
    <div style="margin-top:8px;">${button("Reply to buyer", `${base}${opts.messageHref}`)}</div>`
  return {
    subject: `New enquiry on ${opts.listingTitle} — HarnessBid`,
    html: shell({ title: "New enquiry", bodyHtml: body, preheader: opts.preview.slice(0, 90) }),
    text: `New enquiry on ${opts.listingTitle}: "${opts.preview}" — reply: ${base}${opts.messageHref}`,
  }
}
