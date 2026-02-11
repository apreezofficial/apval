'use client';
import { motion } from 'framer-motion';
import { Heart, Sparkles, Zap, Shield, Eye, Globe } from 'lucide-react';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';
import Link from 'next/link';

const stories = [
    {
        id: 'premium-mockup-card',
        name: 'The Premium Interactive',
        tagline: 'Playful Complexity, Solid Emotion.',
        description: 'Designed for those who believe love is a series of beautiful steps. This template features a unique multi-step navigation system that builds anticipation like a physical gift.',
        intent: 'To recreate the physical act of unfolding a love letter within a digital space.',
        elements: ['3D Heart Locks', 'Multi-step Progression', 'Soft Pastel Aesthetics'],
        bestFor: 'Playful relationships and sweet memories.',
        color: '#FF99F1',
        icon: <Heart className="w-6 h-6 fill-current" />
    },
    {
        id: 'amour',
        name: 'Amour Cinematic',
        tagline: 'Love as Art, Screens as Canvas.',
        description: 'Our flagship fullscreen experience. Amour blends glassmorphism with high-contrast cinematic typography to create a website that feels like a professional portfolio for your relationship.',
        intent: 'To transform personal messages into a high-end digital exhibition.',
        elements: ['Glassmorphic Layers', 'Cinematic Headers', 'Horizontal Scrolling'],
        bestFor: 'Milestone anniversaries and deep emotional declarations.',
        color: '#8BB0C1',
        icon: <Zap className="w-6 h-6" />
    },
    {
        id: 'minimal-elite-card',
        name: 'Minimal Elite',
        tagline: 'The Power of Refined Silence.',
        description: 'Inspired by obsidian glass and high-end automotive design. This template strips away everything but the essential, allowing your words to carry the full weight of your intent.',
        intent: 'To provide a space where silence and typography speak louder than grand visuals.',
        elements: ['Obsidian Glass Finish', 'Monospace Detailing', 'Subtle Framer Animations'],
        bestFor: 'Serious, profound, and modern romantic gestures.',
        color: '#ffffff',
        icon: <Shield className="w-6 h-6" />
    },
    {
        id: 'valentine-motion-premium',
        name: 'Premium Motion',
        tagline: 'Cinematic Orchestration.',
        description: 'A theatrical experience featuring gold-leaf accents and a reactive heart canvas. It starts with dynamic intro quotes that set the tone before revealing the core message.',
        intent: 'To choreograph the emotional journey of receiving a surprise.',
        elements: ['Luxury Gold Accents', 'Reactive Heart Canvas', 'Intro Orchestration'],
        bestFor: 'Surprising loved ones with a touch of luxury and movement.',
        color: '#A82424',
        icon: <Sparkles className="w-6 h-6" />
    }
];

export default function StoryClient() {
    return (
        <main className="min-h-screen bg-[#050505] text-white selection:bg-myRed selection:text-white">
            <Navbar />

            {/* Hero Section */}
            <section className="relative pt-40 pb-20 px-6 overflow-hidden">
                <div className="absolute inset-0 opacity-10 pointer-events-none">
                    <div className="absolute top-0 left-1/2 -translate-x-1/2 w-full h-[500px] bg-gradient-to-b from-myRed/20 via-transparent to-transparent blur-[120px]" />
                </div>

                <div className="max-w-7xl mx-auto text-center relative z-10">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 mb-8"
                    >
                        <Heart className="w-4 h-4 text-myRed fill-current" />
                        <span className="text-[10px] font-black uppercase tracking-[0.3em] text-white/40">The Design Codex</span>
                    </motion.div>

                    <motion.h1
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.1 }}
                        className="text-5xl md:text-8xl font-black italic tracking-tighter uppercase mb-6 leading-[0.9]"
                    >
                        Behind every <span className="text-myRed">Story.</span>
                    </motion.h1>

                    <motion.p
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.2 }}
                        className="max-w-2xl mx-auto text-white/40 font-medium text-lg"
                    >
                        Architectural deep dives into our cinematic templates. Learn the intent, the tech, and the soul behind each design.
                    </motion.p>
                </div>
            </section>

            {/* Template Stories */}
            <section className="px-6 pb-40">
                <div className="max-w-7xl mx-auto space-y-32">
                    {stories.map((story, index) => (
                        <motion.div
                            key={story.id}
                            initial={{ opacity: 0, y: 40 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.8 }}
                            className={`flex flex-col ${index % 2 === 0 ? 'md:flex-row' : 'md:flex-row-reverse'} gap-16 items-center`}
                        >
                            {/* Visual Representative */}
                            <div className="flex-1 w-full">
                                <div className="relative aspect-video rounded-[40px] overflow-hidden group">
                                    <div
                                        className="absolute inset-0 opacity-20"
                                        style={{ backgroundColor: story.color }}
                                    />
                                    <div className="absolute inset-0 flex items-center justify-center">
                                        <div className="relative">
                                            <motion.div
                                                animate={{ scale: [1, 1.1, 1] }}
                                                transition={{ duration: 4, repeat: Infinity }}
                                                className="w-32 h-32 rounded-[32px] flex items-center justify-center shadow-2xl transition-all duration-500 group-hover:scale-110"
                                                style={{ backgroundColor: story.color, color: index === 2 ? '#000' : '#fff' }}
                                            >
                                                {story.icon}
                                            </motion.div>
                                            <div className="absolute -inset-8 bg-white/10 blur-[40px] rounded-full -z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                                        </div>
                                    </div>
                                    <div className="absolute bottom-8 left-8">
                                        <p className="text-[10px] font-black uppercase tracking-[0.5em] text-white/20">Template Reference</p>
                                        <h3 className="text-2xl font-black italic uppercase tracking-tighter">{story.name}</h3>
                                    </div>
                                </div>
                            </div>

                            {/* Text Content */}
                            <div className="flex-1 space-y-8">
                                <div className="space-y-4">
                                    <h2 className="text-4xl md:text-5xl font-black tracking-tight leading-none uppercase italic group">
                                        {story.tagline}
                                    </h2>
                                    <p className="text-white/60 text-lg leading-relaxed font-medium">
                                        {story.description}
                                    </p>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-white/5">
                                    <div className="space-y-2">
                                        <div className="flex items-center gap-2 text-myRed">
                                            <Zap size={16} />
                                            <span className="text-[10px] font-black uppercase tracking-widest">Architectural Intent</span>
                                        </div>
                                        <p className="text-white/40 text-sm font-medium italic">"{story.intent}"</p>
                                    </div>
                                    <div className="space-y-2">
                                        <div className="flex items-center gap-2 text-myRed">
                                            <Eye size={16} />
                                            <span className="text-[10px] font-black uppercase tracking-widest">Best For</span>
                                        </div>
                                        <p className="text-white/40 text-sm font-medium">{story.bestFor}</p>
                                    </div>
                                </div>

                                <div className="flex flex-wrap gap-2">
                                    {story.elements.map((el) => (
                                        <span key={el} className="px-4 py-2 bg-white/5 border border-white/5 rounded-full text-[10px] font-bold uppercase tracking-widest text-white/40">
                                            {el}
                                        </span>
                                    ))}
                                </div>

                                <div className="pt-8">
                                    <Link
                                        href="/templates"
                                        className="inline-flex items-center gap-4 text-myRed font-black uppercase tracking-widest text-[10px] group transition-all"
                                    >
                                        <span>Use this template</span>
                                        <div className="w-8 h-8 rounded-full border border-myRed/20 flex items-center justify-center group-hover:bg-myRed group-hover:text-white transition-all">
                                            <ArrowRight size={14} className="group-hover:translate-x-0.5 transition-transform" />
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </motion.div>
                    ))}
                </div>
            </section>

            {/* CTA */}
            <section className="px-6 py-40 bg-white/5 relative overflow-hidden">
                <div className="absolute bottom-0 right-0 w-[500px] h-[500px] bg-myRed/10 blur-[150px] rounded-full translate-x-1/2 translate-y-1/2" />
                <div className="max-w-4xl mx-auto text-center relative z-10 space-y-10">
                    <h2 className="text-4xl md:text-6xl font-black uppercase italic tracking-tighter leading-none">
                        Ready to write<br /><span className="text-myRed">Your Story?</span>
                    </h2>
                    <Link
                        href="/templates"
                        className="inline-flex items-center gap-4 px-12 py-6 bg-white text-black font-black uppercase tracking-widest text-xs rounded-full hover:scale-105 transition-all shadow-[0_20px_40px_rgba(0,0,0,0.3)]"
                    >
                        <span>Start Creating</span>
                        <ArrowRight size={16} />
                    </Link>
                </div>
            </section>

            <Footer />
        </main>
    );
}

function ArrowRight({ size, className }: { size: number, className?: string }) {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round" className={className}>
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
    );
}
