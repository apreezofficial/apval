import { Metadata } from 'next';
import HomeClient from '@/components/HomeClient';
import { siteConfig } from '@/lib/config';

export const metadata: Metadata = {
  title: "Spread the Love | Elite Digital Romance",
  description: "Create and share beautiful, unique Valentine surprises with our premium cinematic templates. Deploy your love asset in seconds.",
  alternates: {
    canonical: "/",
  },
  openGraph: {
    title: "Spread the Love | Apval",
    description: "Create and share beautiful, unique Valentine surprises. Deploy your love asset in seconds.",
    url: siteConfig.url,
  },
};

export default function Home() {
  return <HomeClient />;
}
