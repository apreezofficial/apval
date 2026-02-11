'use client';
import { useState } from 'react';
import Navbar from '@/components/Navbar';
import { templates } from '@/data/templates';
import TemplateCard from '@/components/TemplateCard';
import MultiStepEditor from '@/components/MultiStepEditor';
import { motion, AnimatePresence } from 'framer-motion';
import Footer from '@/components/Footer';


export default function TemplatesClient() {
    const [selectedTemplateId, setSelectedTemplateId] = useState<string | null>(null);
    const [filter, setFilter] = useState<'all' | 'card' | 'website'>('all');

    const filteredTemplates = templates.filter(t => filter === 'all' || t.category === filter);

    const cardVariants = [
        { initial: { opacity: 0, scale: 0.9, y: 50 }, animate: { opacity: 1, scale: 1, y: 0 } },
        { initial: { opacity: 0, scale: 0.9, y: 30 }, animate: { opacity: 1, scale: 1, y: 0 } },
        { initial: { opacity: 0, scale: 0.9, y: 40 }, animate: { opacity: 1, scale: 1, y: 0 } },
    ];

    return (
        <main className="min-h-screen bg-[#050505] text-white">
            <Navbar />

            <div className="pt-40 px-6 md:px-20 max-w-7xl mx-auto mb-20">
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-10 mb-16">
                    <div className="space-y-4">
                        <h1 className="text-5xl md:text-7xl font-medium tracking-tighter leading-none">
                            The Complete<br />
                            <span className="text-myRed">Asset Gallery</span>
                        </h1>
                        <p className="max-w-xl text-xl text-white/40 font-medium tracking-tight">
                            Select architecture for your next deployment.
                        </p>
                    </div>

                    <div className="flex items-center gap-3 p-1.5 bg-white/5 rounded-2xl border border-white/5">
                        {['all', 'card', 'website'].map((cat) => (
                            <button
                                key={cat}
                                onClick={() => setFilter(cat as any)}
                                className={`px-6 py-2.5 rounded-xl transition-all text-sm font-bold ${filter === cat
                                    ? 'bg-myRed text-white shadow-[0_0_20px_rgba(252,65,0,0.3)]'
                                    : 'text-white/40 hover:text-white hover:bg-white/5'
                                    }`}
                            >
                                {cat.charAt(0).toUpperCase() + cat.slice(1) + 's'}
                            </button>
                        ))}
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    {filteredTemplates.map((t, index) => (
                        <motion.div
                            key={t.id}
                            initial={cardVariants[index % cardVariants.length].initial}
                            whileInView={cardVariants[index % cardVariants.length].animate}
                            viewport={{ once: true }}
                            transition={{ duration: 0.6, delay: (index % 6) * 0.1, ease: "easeOut" }}
                            onClick={() => setSelectedTemplateId(t.id)}
                            className="cursor-pointer"
                        >
                            <TemplateCard template={t} />
                        </motion.div>
                    ))}
                </div>
            </div>

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
