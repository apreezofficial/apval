import { Metadata } from 'next';
import TemplatesClient from '@/components/TemplatesClient';

export const metadata: Metadata = {
    title: "Asset Gallery | Premium Templates",
    description: "Browse our exclusive collection of cinematic Valentine templates. From 3D interactive cards to fullscreen cinematic experiences.",
    alternates: {
        canonical: "/templates",
    },
    openGraph: {
        title: "Asset Gallery | Apval",
        description: "Browse our exclusive collection of cinematic Valentine templates.",
        url: "/templates",
    },
};

export default function TemplatesPage() {
    return <TemplatesClient />;
}
