'use client';
import { motion, AnimatePresence } from 'framer-motion';
import { Heart, ChevronRight, Share2, Sparkles, User, MessageCircle } from 'lucide-react';
import { useState } from 'react';

interface AmourViewProps {
    data: any;
    steps: any[];
    isPreview?: boolean;
}

export default function AmourView({ data, steps, isPreview }: AmourViewProps) {
    const [viewStep, setViewStep] = useState(0);

    const nextStep = () => {
        if (viewStep < steps.length - 1) {
            setViewStep(viewStep + 1);
        }
    };

    return (
        <main className={`${isPreview ? 'w-full h-full relative' : 'fixed inset-0'} bg-[#050505] text-white overflow-hidden font-sans`}>
            {/* Cinematic Background */}
            <div className={`absolute inset-0 ${isPreview ? 'scale-150' : ''}`}>
                <img
                    src="/amour-bg.png"
                    className="w-full h-full object-cover opacity-60 scale-105"
                    alt="Background"
                />
                <div className="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/60" />
            </div>

            {/* Corner Metadata */}
            {!isPreview && (
                <>
                    <div className="absolute top-8 left-10 text-[10px] font-bold uppercase tracking-[0.3em] opacity-40">Website Design</div>
                    <div className="absolute top-8 left-1/2 -translate-x-1/2 text-[10px] font-bold uppercase tracking-[0.3em] opacity-40">UI/UX</div>
                    <div className="absolute top-8 right-10 text-[10px] font-bold uppercase tracking-[0.3em] opacity-40 text-right">By {data.sender || 'Apval'}</div>

                    <div className="absolute bottom-8 left-10 text-[10px] font-bold uppercase tracking-[0.3em] opacity-40">Landing Page</div>
                    <div className="absolute bottom-8 right-10 text-[10px] font-bold uppercase tracking-[0.3em] opacity-40">2026</div>
                </>
            )}

            {/* Navigation Bar */}
            <nav className={`absolute top-12 left-0 right-0 z-20 flex items-center justify-between ${isPreview ? 'px-10 scale-[0.6]' : 'px-20'}`}>
                <div className="flex items-center gap-2">
                    <span className="text-xl font-black tracking-tighter uppercase flex items-center gap-1">
                        AM<Heart size={16} className="fill-white" />UR
                    </span>
                </div>
                <div className="flex items-center gap-10 text-[11px] font-bold uppercase tracking-widest opacity-60">
                    <span className="cursor-pointer hover:opacity-100 transition-opacity">Home</span>
                    <span className="cursor-pointer hover:opacity-100 transition-opacity">Services</span>
                    <span className="cursor-pointer hover:opacity-100 transition-opacity">Portfolio</span>
                </div>
                <a
                    href={data.whatsapp ? `https://wa.me/${data.whatsapp.replace(/\D/g, '')}` : '#'}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-[11px] font-bold uppercase tracking-widest cursor-pointer hover:underline"
                >
                    Get in touch
                </a>
            </nav>

            {/* Main Content Container (Glassmorphic) */}
            <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
                <motion.div
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    className={`relative border border-white/20 bg-white/5 backdrop-blur-[40px] rounded-[2px] pointer-events-auto flex flex-col overflow-hidden shadow-2xl ${isPreview ? 'w-[95%] h-[85%] scale-[0.85]' : 'w-[90vw] h-[80vh]'}`}
                >
                    <AnimatePresence mode="wait">
                        <motion.div
                            key={viewStep}
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -20 }}
                            transition={{ duration: 0.8, ease: [0.16, 1, 0.3, 1] }}
                            className={`flex-1 flex flex-col items-center justify-center text-center relative ${isPreview ? 'p-10' : 'p-20'}`}
                        >
                            {/* Large Background Text (Interactive) */}
                            <div className="absolute inset-0 flex items-center justify-center z-0 pointer-events-none">
                                <span className="text-[25vw] font-black tracking-[-0.05em] text-white opacity-[0.08] leading-none mb-20 whitespace-nowrap">
                                    AM<span className="inline-block relative">
                                        O
                                    </span>UR
                                </span>
                            </div>

                            {/* Foreground Message Content */}
                            <div className="relative z-10 w-full max-w-4xl space-y-8">
                                {steps[viewStep].type === 'intro' && (
                                    <div className="space-y-4">
                                        <motion.p
                                            initial={{ x: -20, opacity: 0 }}
                                            animate={{ x: 0, opacity: 1 }}
                                            className="text-white text-4xl md:text-6xl font-medium tracking-tight"
                                        >
                                            Purpose Built For...
                                        </motion.p>
                                        <h1 className="text-white text-5xl md:text-7xl font-black tracking-tighter uppercase drop-shadow-2xl">
                                            {data.headline || 'Your Vision'}
                                        </h1>
                                        <p className="text-white/60 text-xl md:text-2xl font-medium pt-4">Prepared for {data.recipient}</p>
                                    </div>
                                )}

                                {steps[viewStep].type === 'message' && (
                                    <div className="space-y-12">
                                        <div className="w-20 h-20 bg-white/10 rounded-full flex items-center justify-center mx-auto border border-white/20">
                                            <MessageCircle className="w-10 h-10 text-white" />
                                        </div>
                                        <p className="text-white text-3xl md:text-5xl font-medium tracking-tight leading-[1.2] italic max-w-3xl mx-auto">
                                            {data.message}
                                        </p>
                                    </div>
                                )}

                                {steps[viewStep].type === 'image' && (
                                    <div className="space-y-8">
                                        <motion.div
                                            initial={{ scale: 0.8 }}
                                            animate={{ scale: 1 }}
                                            className="w-full max-w-lg aspect-square mx-auto rounded-3xl overflow-hidden border-[12px] border-white shadow-2xl rotate-2"
                                        >
                                            <img src={data.imageUrl} className="w-full h-full object-cover" alt="Memory" />
                                        </motion.div>
                                        <p className="text-white/40 uppercase tracking-[0.5em] text-[10px] font-black">Memory Uploaded</p>
                                    </div>
                                )}

                                {steps[viewStep].type === 'outro' && (
                                    <div className="space-y-8">
                                        <motion.div
                                            animate={{ scale: [1, 1.1, 1] }}
                                            transition={{ duration: 2, repeat: Infinity }}
                                            className="w-32 h-32 bg-white rounded-full flex items-center justify-center mx-auto shadow-2xl shadow-white/20"
                                        >
                                            <Heart className="text-black fill-current w-16 h-16" />
                                        </motion.div>
                                        <div className="space-y-2">
                                            <p className="text-white/40 uppercase tracking-[0.5em] text-[12px] font-black">Authorized by,</p>
                                            <h2 className="text-white text-5xl md:text-7xl font-black tracking-tighter uppercase drop-shadow-2xl">
                                                {data.sender}
                                            </h2>
                                        </div>
                                        {data.whatsapp && (
                                            <div className="pt-4">
                                                <a
                                                    href={`https://wa.me/${data.whatsapp.replace(/\D/g, '')}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-flex items-center gap-3 px-12 py-5 bg-white text-black text-xs font-black uppercase tracking-widest rounded-full hover:bg-white/90 transition-all shadow-[0_20px_40px_rgba(255,255,255,0.2)]"
                                                >
                                                    View Me / Connect
                                                </a>
                                            </div>
                                        )}
                                    </div>
                                )}
                            </div>
                        </motion.div>
                    </AnimatePresence>

                    {/* Progress & Navigation Bar (Inside Glass) */}
                    <div className="p-10 border-t border-white/10 flex items-center justify-between">
                        <div className="flex gap-2">
                            {steps.map((_, i) => (
                                <div
                                    key={i}
                                    className={`h-1 transition-all duration-500 rounded-full ${i === viewStep ? 'w-12 bg-white' : 'w-6 bg-white/20'}`}
                                />
                            ))}
                        </div>

                        <div className="flex items-center gap-6">
                            {viewStep < steps.length - 1 ? (
                                <button
                                    onClick={nextStep}
                                    className="px-10 py-5 bg-white text-black font-black uppercase tracking-widest text-xs rounded-full hover:bg-yellow-400 transition-all flex items-center gap-3 shadow-xl"
                                >
                                    <span>Continue Path</span>
                                    <ChevronRight size={16} />
                                </button>
                            ) : (
                                <button
                                    className="px-10 py-5 bg-yellow-400 text-black font-black uppercase tracking-widest text-xs rounded-full transition-all flex items-center gap-3 shadow-xl shadow-yellow-400/20"
                                >
                                    <span>Share Link</span>
                                    <Share2 size={16} />
                                </button>
                            )}
                        </div>
                    </div>
                </motion.div>
            </div>

            {/* Scroll Indicator */}
            <div className="absolute bottom-12 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-40">
                <span className="text-[10px] font-bold uppercase tracking-widest underline decoration-yellow-400 underline-offset-4">Scroll to explore</span>
                <motion.div
                    animate={{ y: [0, 10, 0] }}
                    transition={{ duration: 2, repeat: Infinity }}
                    className="w-0.5 h-10 bg-white/20"
                />
            </div>
        </main>
    );
}
