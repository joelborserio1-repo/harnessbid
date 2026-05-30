import type { Metadata, Viewport } from 'next'
import { Fraunces, Sora } from 'next/font/google'
import { Analytics } from '@vercel/analytics/next'
import './globals.css'

const sora = Sora({
  subsets: ["latin"],
  variable: '--font-sora',
  display: 'swap',
  weight: ['400', '500', '600', '700'],
})

// Display serif for headings — refined editorial gravitas (replaces Cinzel).
// Reuses the --font-cinzel variable so existing font-cinzel utilities map here.
const fraunces = Fraunces({
  subsets: ["latin"],
  variable: '--font-cinzel',
  display: 'swap',
  weight: ['400', '500', '600'],
})

export const metadata: Metadata = {
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL || 'https://harnessbid.com'),
  title: 'HarnessBid | Premium Harness Racing Marketplace & Auctions',
  description: 'The global marketplace for harness racing horses, equipment, and services. Buy, sell, and auction with trusted industry professionals worldwide.',
  generator: 'HarnessBid',
  keywords: ['harness racing', 'horse auctions', 'standardbred', 'racing equipment', 'bloodstock', 'trotters', 'pacers'],
  icons: {
    icon: '/logo.png',
    apple: '/logo.png',
  },
}

export const viewport: Viewport = {
  themeColor: '#00205F',
  width: 'device-width',
  initialScale: 1,
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode
}>) {
  return (
    <html lang="en" className={`${sora.variable} ${fraunces.variable} bg-background`} suppressHydrationWarning>
      <body className="font-sans antialiased">
        {children}
        {process.env.NODE_ENV === 'production' && <Analytics />}
      </body>
    </html>
  )
}
