'use client';
import { motion, AnimatePresence } from 'framer-motion';
import { Heart, Lock as LucideLock, Sparkles } from 'lucide-react';

interface MinimalEliteViewProps {
    data: any;
    isPreview?: boolean;
}

export default function MinimalEliteView({ data, isPreview }: MinimalEliteViewProps) {
    return (
        <div className={`w-full h-full ${isPreview ? 'relative' : 'fixed inset-0'} bg-[#FFF9F2] flex items-center justify-center p-8 overflow-hidden font-serif`}>
            {/* Silk/Champagne Gradient Background */}
            <div className="absolute inset-0 bg-[radial-gradient(circle_at_50%_0%,#F3E9DC,transparent_70%)]" />
            <div className="absolute inset-0 opacity-[0.4] bg-[linear-gradient(135deg,#FFF9F2_25%,transparent_25%,transparent_50%,#FFF9F2_50%,#FFF9F2_75%,transparent_75%,transparent)] bg-[size:100px_100px]" />

            {/* Royal Graphic Elements */}
            <div className="absolute inset-0 pointer-events-none flex items-center justify-center opacity-5">
                <div className="w-[150%] h-[150%] border-[2px] border-[#3D2B1F] rounded-full" />
                <div className="absolute w-[130%] h-[130%] border-[1px] border-[#3D2B1F] rounded-full" />
            </div>

            <motion.div
                initial={{ opacity: 0, y: 30 }}
                animate={{ opacity: 1, y: 0 }}
                className={`w-full max-w-[380px] aspect-[9/16] bg-white/60 backdrop-blur-xl rounded-[60px] shadow-[0_40px_100px_-20px_rgba(61,43,31,0.2)] border border-[#C5A059]/30 p-12 flex flex-col items-center justify-between text-center relative z-10 ${isPreview ? 'scale-[0.85]' : ''}`}
            >
                {/* Header Decoration */}
                <div className="w-full flex justify-between items-center opacity-40">
                    <Sparkles className="w-4 h-4 text-[#C5A059]" />
                    <div className="h-px flex-1 mx-4 bg-[#C5A059]/40" />
                    <Sparkles className="w-4 h-4 text-[#C5A059]" />
                </div>

                {/* Central Love Lock Motif (Royale Edition) */}
                <div className="relative">
                    <motion.div
                        animate={{ y: [0, -15, 0] }}
                        transition={{ duration: 5, repeat: Infinity, ease: "easeInOut" }}
                        className="relative"
                    >
                        {/* Shackle */}
                        <div className="absolute -top-10 left-1/2 -translate-x-1/2 w-16 h-16 border-[8px] border-[#3D2B1F] rounded-t-full" />

                        {/* Lock Body */}
                        <div className="w-28 h-24 bg-gradient-to-br from-[#C5A059] via-[#E2C68E] to-[#8E6E3E] rounded-[24px] flex items-center justify-center relative shadow-2xl border-2 border-white/20">
                            <LucideLock className="text-[#3D2B1F]/80 w-12 h-12" />

                            {/* Embedded Heart */}
                            <motion.div
                                animate={{ scale: [1, 1.3, 1] }}
                                transition={{ duration: 2, repeat: Infinity }}
                                className="absolute -bottom-3 -right-3 w-12 h-12 bg-[#3D2B1F] rounded-full flex items-center justify-center border-4 border-[#C5A059] shadow-xl"
                            >
                                <Heart className="text-[#C5A059] w-5 h-5 fill-current" />
                            </motion.div>
                        </div>
                    </motion.div>
                </div>

                {/* Content */}
                <div className="space-y-8">
                    <div className="space-y-3">
                        <span className="text-[11px] font-black uppercase tracking-[0.5em] text-[#3D2B1F]/60">Eternally Sealed</span>
                        <h2 className="text-4xl font-light text-[#3D2B1F] tracking-tight leading-tight">
                            {data.headline && <span className="block text-sm font-medium opacity-60 mb-1">{data.headline}</span>}
                            {data.recipient || 'Your Love'}
                        </h2>
                    </div>

                    <div className="relative px-4">
                        <div className="absolute -left-2 top-0 text-5xl text-[#C5A059]/20 font-serif leading-none italic">"</div>
                        <p className="text-base font-medium text-[#3D2B1F]/90 leading-relaxed italic py-2">
                            {data.message || 'Our love is a sacred architecture, designed for eternity and locked with the key of fate.'}
                        </p>
                        <div className="absolute -right-2 bottom-0 text-5xl text-[#C5A059]/20 font-serif leading-none italic">"</div>
                    </div>
                </div>

                {/* Royal Signature */}
                <div className="w-full pt-10 border-t border-[#3D2B1F]/10 flex flex-col items-center gap-3">
                    <p className="text-[10px] font-black uppercase tracking-[0.4em] text-[#C5A059]">Authorized By</p>
                    <p className="text-xl font-bold text-[#3D2B1F] tracking-tighter uppercase">{data.sender || 'Sender Name'}</p>
                </div>
            </motion.div>

            {/* Noble Floaties */}
            <AnimatePresence>
                {[...Array(6)].map((_, i) => (
                    <motion.div
                        key={i}
                        initial={{ opacity: 0, y: 100 }}
                        animate={{ opacity: [0, 0.15, 0], y: -800, x: Math.sin(i) * 150 }}
                        transition={{ duration: 15 + i, repeat: Infinity, delay: i * 3 }}
                        className="absolute bottom-0 text-[#C5A059]"
                    >
                        <Heart size={24 + i * 4} fill="currentColor" />
                    </motion.div>
                ))}
            </AnimatePresence>
        </div>
    );
}
