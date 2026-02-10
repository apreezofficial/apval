export const siteConfig = {
    name: "Apval",
    description: "Spread your love with cinematic digital experiences. Create, customize, and deploy interactive Valentine assets in seconds.",
    url: process.env.NEXT_PUBLIC_APP_URL || "https://apval.pxxl.pro",
    apiUrl: typeof window === 'undefined'
        ? (process.env.Internal_API_URL || "http://localhost:3000/api")
        : "/api", // Proxy through local API route
    ogImage: "/og-image.png",
    links: {
        twitter: "https://x.com/apcodesphere",
        instagram: "https://instagram.com/apcodesphere",
    },
    author: "Precious Adedokun",
};
