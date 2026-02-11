import { MetadataRoute } from 'next'
import { siteConfig } from '@/lib/config'

export default function sitemap(): MetadataRoute.Sitemap {
    const routes = [
        '',
        '/login',
        '/dashboard',
        '/developer',
        '/templates',
        '/support',
        '/faq',
        '/privacy',
        '/terms',
    ].map((route) => ({
        url: `${siteConfig.url}${route}`,
        lastModified: new Date(),
        changeFrequency: 'daily' as const,
        priority: route === '' ? 1 : 0.8,
    }))

    return routes
}
