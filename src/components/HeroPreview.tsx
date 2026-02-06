'use client';
import { motion, AnimatePresence } from 'framer-motion';
import { useState, useEffect } from 'react';
import { Heart, Share2, ArrowRight } from 'lucide-react';

export default function HeroPreview() {
    const [viewStep, setViewStep] = useState(0);

    const data = {
        recipient: "PHP",
        headline: "To the best stack",
        message: "Dedicated to my babe, the best stack on earth, php to the world",
        sender: "ap",
        imageUrl: "/php.gif" // Placeholder for the actual image which is base64 and too large
    };

    const steps = [
        { type: 'intro', text: `For ${data.recipient}` },
        { type: 'message', text: data.message },
        { type: 'image', text: '' },
        { type: 'outro', text: `Sent by ${data.sender}` }
    ];

    useEffect(() => {
        const currentDuration = steps[viewStep].type === 'image' ? 6000 : 4000;
        const timer = setTimeout(() => {
            setViewStep((prev) => (prev + 1) % steps.length);
        }, currentDuration);
        return () => clearTimeout(timer);
    }, [viewStep, steps.length]);

    return (
        <div className="relative w-full h-full group">
            {/* Background Glow */}
            <div className="absolute -inset-4 bg-myRed/20 rounded-[60px] blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-700" />

            <div className="relative w-full h-full bg-[#111] rounded-[60px] border-[12px] border-[#1a1a1a] overflow-hidden shadow-[0_50px_100px_-20px_rgba(0,0,0,0.5)]">
                {/* Notch */}
                <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[120px] h-[34px] bg-[#1a1a1a] rounded-b-3xl z-50 flex items-center justify-center">
                    <div className="w-12 h-1 bg-white/10 rounded-full" />
                </div>

                <div className="h-full flex flex-col bg-gradient-to-b from-[#1a1a1a] to-[#050505] text-white p-8 pt-20">
                    <AnimatePresence mode="wait">
                        <motion.div
                            key={viewStep}
                            initial={{ x: 20, opacity: 0, filter: 'blur(10px)' }}
                            animate={{ x: 0, opacity: 1, filter: 'blur(0px)' }}
                            exit={{ x: -20, opacity: 0, filter: 'blur(10px)' }}
                            transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
                            className="flex-1 flex flex-col"
                        >
                            {steps[viewStep].type === 'intro' && (
                                <div className="space-y-8">
                                    <div className="relative h-32">
                                        <motion.div
                                            animate={{ y: [0, -10, 0], rotate: [-12, -15, -12] }}
                                            transition={{ duration: 4, repeat: Infinity }}
                                            className="absolute left-0 w-24 h-32 bg-myRed rounded-3xl flex items-center justify-center border-4 border-white/10"
                                        >
                                            <Heart className="text-white w-8 h-8 fill-current" />
                                        </motion.div>
                                    </div>
                                    <h1 className="text-4xl font-black leading-none tracking-tighter italic uppercase text-white">
                                        {data.headline}<br />
                                        <span className="text-myRed">{data.recipient}.</span>
                                    </h1>
                                </div>
                            )}

                            {steps[viewStep].type === 'message' && (
                                <div className="flex-1 flex flex-col justify-center gap-6">
                                    <div className="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center border border-white/10">
                                        <Share2 className="text-myRed w-6 h-6" />
                                    </div>
                                    <p className="text-2xl font-black italic tracking-tighter uppercase leading-tight text-white/90">
                                        {data.message}
                                    </p>
                                </div>
                            )}

                            {steps[viewStep].type === 'image' && (
                                <div className="flex-1 flex flex-col items-center justify-center gap-4">
                                    <motion.div
                                        initial={{ scale: 0.9, rotate: -2 }}
                                        animate={{ scale: 1, rotate: 2 }}
                                        transition={{ duration: 1.5 }}
                                        className="w-full aspect-square rounded-[32px] overflow-hidden border-4 border-white/5 shadow-2xl"
                                    >
                                        <img src={data.imageUrl} className="w-full h-full object-cover" alt="Visual" />
                                    </motion.div>
                                    <p className="text-[10px] font-black uppercase tracking-[0.4em] text-white/20">Asset Loaded</p>
                                </div>
                            )}

                            {steps[viewStep].type === 'outro' && (
                                <div className="flex-1 flex flex-col items-center justify-center text-center gap-6">
                                    <motion.div
                                        animate={{ scale: [1, 1.1, 1] }}
                                        transition={{ duration: 2, repeat: Infinity }}
                                        className="w-16 h-16 bg-myRed rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(252,65,0,0.4)]"
                                    >
                                        <Heart className="text-white fill-current w-8 h-8" />
                                    </motion.div>
                                    <div className="space-y-1">
                                        <p className="text-[10px] font-black uppercase tracking-[0.4em] text-white/20">Transmitted by</p>
                                        <h2 className="text-3xl font-black italic tracking-tighter uppercase">
                                            {data.sender}
                                        </h2>
                                    </div>
                                </div>
                            )}
                        </motion.div>
                    </AnimatePresence>

                    {/* Progress Dots */}
                    <div className="flex gap-2 mb-12">
                        {steps.map((_, i) => (
                            <div
                                key={i}
                                className={`h-1 flex-1 rounded-full transition-all duration-500 ${i === viewStep ? 'bg-myRed' : 'bg-white/10'}`}
                            />
                        ))}
                    </div>

                    {/* Simulation Button */}
                    <div className="w-full h-14 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-between px-6 opacity-40">
                        <span className="text-[10px] font-black uppercase tracking-widest">Processing...</span>
                        <ArrowRight className="w-4 h-4" />
                    </div>
                </div>
            </div>

            {/* Floating Elements for Premium Feel */}
            <motion.div
                animate={{ y: [0, -20, 0], x: [0, 10, 0] }}
                transition={{ duration: 5, repeat: Infinity, ease: "easeInOut" }}
                className="absolute -bottom-6 -left-6 p-4 bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl z-20"
            >
                <div className="flex items-center gap-3">
                    <div className="w-3 h-3 bg-green-500 rounded-full animate-pulse" />
                    <span className="text-[8px] font-black uppercase tracking-widest text-white/40">Active Instance</span>
                </div>
            </motion.div>
        </div>
    );
}
