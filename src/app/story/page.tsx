import { Metadata } from 'next';
import StoryClient from '@/components/StoryClient';

export const metadata: Metadata = {
    title: "The Design Codex | Our Stories",
    description: "Deep dive into the architectural intent and emotional journey behind our cinematic templates. Learn how we blend technology with romance.",
    alternates: {
        canonical: "/story",
    },
    openGraph: {
        title: "The Design Codex | Architectural Romance",
        description: "Explore the stories behind each Apval template. From Obsidian Minimalist to Golden Motion, discover the tech and intent behind your digital gifts.",
        type: "website",
    }
};

export default function StoryPage() {
    return <StoryClient />;
}
