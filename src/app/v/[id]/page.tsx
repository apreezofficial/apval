import { Metadata, ResolvingMetadata } from 'next';
import ValentineViewClient from '@/components/ValentineViewClient';
import { siteConfig } from '@/lib/config';
import { templates } from '@/data/templates';

interface Props {
    params: Promise<{ id: string }>;
}

async function getValentineData(id: string) {
    const res = await fetch(`${siteConfig.url}/api/valentines/${id}`, { cache: 'no-store' });
    if (!res.ok) return null;
    return res.json();
}

export async function generateMetadata(
    { params }: Props,
    parent: ResolvingMetadata
): Promise<Metadata> {
    const { id } = await params;
    const data = await getValentineData(id);

    if (!data || data.error) {
        return {
            title: "Asset Not Found | Apval",
        };
    }

    const templateName = templates.find(t => t.id === data.templateId)?.name || "Interactive Valentine";
    const title = `${data.sender} sent you a ${templateName}!`;
    const description = `Click to view the beautiful cinematic experience ${data.sender} created for ${data.recipient}.`;

    return {
        title,
        description,
        alternates: {
            canonical: `/v/${id}`,
        },
        openGraph: {
            title,
            description,
            url: `${siteConfig.url}/v/${id}`,
            siteName: siteConfig.name,
            images: [
                {
                    url: data.imageUrl || siteConfig.ogImage,
                    width: 1200,
                    height: 630,
                    alt: "Valentine Asset Preview",
                },
            ],
            type: 'website',
        },
        twitter: {
            card: 'summary_large_image',
            title,
            description,
            images: [data.imageUrl || siteConfig.ogImage],
        },
    };
}

export default async function ValentinePage({ params }: Props) {
    const { id } = await params;
    const data = await getValentineData(id);

    return <ValentineViewClient initialData={data} id={id} />;
}
