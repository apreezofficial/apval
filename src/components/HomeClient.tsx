'use client';
import { useState } from 'react';
import Navbar from "@/components/Navbar";
import Hero from "@/components/Hero";
import PartnerLogos from "@/components/PartnerLogos";
import Mission from "@/components/Mission";
import { templates } from "@/data/templates";
import TemplateCard from "@/components/TemplateCard";
import MultiStepEditor from "@/components/MultiStepEditor";
import Footer from "@/components/Footer";
import { AnimatePresence } from 'framer-motion';
import { ArrowRight } from 'lucide-react';
import Link from 'next/link';

export default function HomeClient() {
    const [selectedTemplateId, setSelectedTemplateId] = useState<string | null>(null);
    const [filter, setFilter] = useState<'all' | 'card' | 'website'>('all');

    const filteredTemplates = templates.filter(t => filter === 'all' || t.category === filter);

    return (
        <main className="min-h-screen bg-[#050505]">
            <Navbar />
            <Hero />
            <PartnerLogos />

            <section id="templates" className="py-32 px-6">
                <div className="max-w-7xl mx-auto text-center">
                    <div className="mb-20 space-y-8">
                        <div className="space-y-4">
                            <h2 className="text-4xl md:text-6xl font-medium tracking-tighter text-white">Ready for <span className="text-myRed">Deployment</span>.</h2>
                            <p className="text-white/40 font-medium tracking-tight uppercase text-xs tracking-[0.3em]">Select Architecture</p>
                        </div>

                        <div className="flex items-center justify-center gap-3 p-1.5 bg-white/5 rounded-2xl border border-white/5 w-fit mx-auto">
                            {['all', 'card', 'website'].map((cat) => (
                                <button
                                    key={cat}
                                    onClick={() => setFilter(cat as any)}
                                    className={`px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all ${filter === cat
                                        ? 'bg-myRed text-white shadow-[0_10px_20px_rgba(252,65,0,0.3)]'
                                        : 'text-white/40 hover:text-white hover:bg-white/5'
                                        }`}
                                >
                                    {cat}s
                                </button>
                            ))}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                        {filteredTemplates.slice(0, 3).map(t => (
                            <div key={t.id} onClick={() => setSelectedTemplateId(t.id)} className="cursor-pointer">
                                <TemplateCard template={t} />
                            </div>
                        ))}
                    </div>

                    <div className="mt-20">
                        <Link
                            href="/templates"
                            className="inline-flex items-center gap-2 text-white/40 hover:text-white transition-all font-bold uppercase tracking-[0.3em] text-xs border-b border-white/10 pb-2"
                        >
                            Explore Full Gallery <ArrowRight className="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </section>

            <Mission />

            <Footer />

            <AnimatePresence>
                {selectedTemplateId && (
                    <MultiStepEditor
                        templateId={selectedTemplateId}
                        onClose={() => setSelectedTemplateId(null)}
                    />
                )}
            </AnimatePresence>
        </main>
    );
}
