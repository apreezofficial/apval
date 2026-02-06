'use client';
import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ArrowLeft, ArrowRight, Share2, Heart } from 'lucide-react';
import Link from 'next/link';
import AmourView from '@/components/templates/AmourView';
import MinimalEliteView from '@/components/templates/MinimalEliteView';
import PremiumMotionView from '@/components/templates/PremiumMotionView';
import QuestValentineView from '@/components/templates/QuestValentineView';
import NotFoundUI from '@/components/NotFoundUI';

interface ValentineViewClientProps {
    initialData: any;
    id: string;
}

export default function ValentineViewClient({ initialData, id }: ValentineViewClientProps) {
    const [data, setData] = useState<any>(initialData);
    const [mounted, setMounted] = useState(false);
    const [viewStep, setViewStep] = useState(0);

    useEffect(() => {
        setMounted(true);
        if (!initialData && id) {
            fetch(`/api/valentines/${id}`)
                .then(res => res.json())
                .then(result => {
                    if (!result.error) setData(result);
                    else setData({ error: true });
                })
                .catch(() => setData({ error: true }));
        }
    }, [id, initialData]);

    if (!mounted) return null;

    if (!data || data.error) return (
        <NotFoundUI
            message="might have been deleted or the link is broken"
            path={`/v/${id}`}
        />
    );

    const steps = [
        { type: 'intro', text: `For ${data.recipient}` },
        { type: 'message', text: data.message },
        ...(data.imageUrl ? [{ type: 'image', text: '' }] : []),
        { type: 'outro', text: `Sent by ${data.sender}` }
    ];

    const nextStep = () => {
        if (viewStep < steps.length - 1) {
            setViewStep(viewStep + 1);
        }
    };

    const prevStep = () => {
        if (viewStep > 0) {
            setViewStep(viewStep - 1);
        }
    };

    const renderContent = () => {
        if (data.templateId === 'amour') {
            return <AmourView data={data} steps={steps} />;
        }

        if (data.templateId === 'minimal-elite-card') {
            return <MinimalEliteView data={data} />;
        }

        if (data.templateId === 'valentine-motion-premium') {
            return <PremiumMotionView data={data} />;
        }

        if (data.templateId === 'quest-valentine') {
            return <QuestValentineView data={data} />;
        }

        // Default Template (Premium Mockup)
        return (
            <main className="min-h-screen relative bg-[#FF99F1] flex flex-col items-center justify-center p-6 text-black overflow-hidden select-none">
                <div className="absolute inset-0 pointer-events-none">
                    {[...Array(20)].map((_, i) => (
                        <motion.div
                            key={i}
                            initial={{ y: "110%", x: `${Math.random() * 100}%`, scale: Math.random() * 0.5 + 0.5 }}
                            animate={{
                                y: "-10%",
                                x: [`${Math.random() * 100}%`, `${Math.random() * 100}%`],
                                rotate: [0, 360]
                            }}
                            transition={{
                                duration: Math.random() * 10 + 10,
                                repeat: Infinity,
                                ease: "linear"
                            }}
                            className="absolute opacity-40"
                        >
                            <div className="w-8 h-8 md:w-12 md:h-12 rounded-full bg-white/30 backdrop-blur-sm border border-white/50 flex items-center justify-center shadow-lg">
                                <Heart size={12} className="text-white fill-current" />
                            </div>
                        </motion.div>
                    ))}
                </div>

                <div className="relative z-10 w-full max-w-[400px] aspect-[9/19] bg-white rounded-[60px] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.3),inset_0_2px_4px_rgba(255,255,255,1)] border-[12px] border-black overflow-hidden flex flex-col">
                    <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[120px] h-[34px] bg-black rounded-b-3xl z-50 flex items-center justify-center">
                        <div className="w-12 h-1 bg-white/20 rounded-full" />
                    </div>

                    <div className="flex-1 p-8 pt-20 flex flex-col justify-between bg-gradient-to-b from-white to-[#FFECF8] relative">
                        <AnimatePresence mode="wait">
                            <motion.div
                                key={viewStep}
                                initial={{ x: 50, opacity: 0, filter: 'blur(10px)' }}
                                animate={{ x: 0, opacity: 1, filter: 'blur(0px)' }}
                                exit={{ x: -50, opacity: 0, filter: 'blur(10px)' }}
                                transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
                                className="flex-1 flex flex-col"
                            >
                                {steps[viewStep].type === 'intro' && (
                                    <div className="space-y-12">
                                        <div className="relative h-48 flex items-center justify-center">
                                            <motion.div
                                                animate={{ y: [0, -10, 0], rotate: [-12, -15, -12] }}
                                                transition={{ duration: 4, repeat: Infinity }}
                                                className="absolute -translate-x-10 w-32 h-40 bg-red-500 rounded-[32px] shadow-2xl flex items-center justify-center border-[6px] border-white"
                                            >
                                                <div className="w-16 h-20 border-[12px] border-gray-300 rounded-t-full absolute -top-14" />
                                                <Heart className="text-white w-10 h-10 fill-current" />
                                            </motion.div>
                                            <motion.div
                                                animate={{ y: [0, 10, 0], rotate: [12, 15, 12] }}
                                                transition={{ duration: 4, repeat: Infinity, delay: 0.5 }}
                                                className="absolute translate-x-10 w-32 h-40 bg-pink-100 rounded-[32px] shadow-2xl flex items-center justify-center border-[6px] border-white z-10"
                                            >
                                                <div className="w-16 h-20 border-[12px] border-gray-300 rounded-t-full absolute -top-14" />
                                                <Heart className="text-pink-400 w-10 h-10 fill-current" />
                                            </motion.div>
                                        </div>
                                        <h1 className="text-5xl font-black leading-none tracking-tighter break-words">
                                            {data.headline || 'We offer the best moments to'}<br />
                                            {data.recipient}.
                                        </h1>
                                    </div>
                                )}

                                {steps[viewStep].type === 'message' && (
                                    <div className="flex-1 flex flex-col justify-center gap-8">
                                        <div className="w-12 h-12 bg-pink-100 rounded-2xl flex items-center justify-center">
                                            <Share2 className="text-pink-500 w-6 h-6" />
                                        </div>
                                        <p className="text-2xl font-bold leading-tight tracking-tight text-gray-800 italic">
                                            {data.message}
                                        </p>
                                    </div>
                                )}

                                {steps[viewStep].type === 'image' && (
                                    <div className="flex-1 flex flex-col items-center justify-center gap-6">
                                        <motion.div
                                            initial={{ scale: 0.8 }}
                                            animate={{ scale: 1 }}
                                            className="w-full aspect-square rounded-[40px] overflow-hidden border-[8px] border-white shadow-2xl rotate-2"
                                        >
                                            <img src={data.imageUrl} className="w-full h-full object-cover" alt="Memory" />
                                        </motion.div>
                                        <p className="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400">Attached Memory</p>
                                    </div>
                                )}

                                {steps[viewStep].type === 'outro' && (
                                    <div className="flex-1 flex flex-col items-center justify-center text-center gap-8">
                                        <motion.div
                                            animate={{ scale: [1, 1.1, 1] }}
                                            transition={{ duration: 2, repeat: Infinity }}
                                            className="w-20 h-20 bg-red-500 rounded-full flex items-center justify-center shadow-xl shadow-red-500/30"
                                        >
                                            <Heart className="text-white fill-current w-10 h-10" />
                                        </motion.div>
                                        <div className="space-y-2">
                                            <p className="text-xs font-black uppercase tracking-[0.4em] text-gray-400">Sent with love,</p>
                                            <h2 className="text-4xl font-black italic tracking-tighter">
                                                From {data.sender}
                                            </h2>
                                        </div>
                                    </div>
                                )}
                            </motion.div>
                        </AnimatePresence>

                        <div className="flex gap-1 mt-8">
                            {steps.map((_, i) => (
                                <div key={i} className={`h-1 flex-1 rounded-full transition-all duration-500 ${i <= viewStep ? 'bg-black' : 'bg-black/5'}`} />
                            ))}
                        </div>

                        <button
                            onClick={nextStep}
                            className={`mt-6 w-full h-16 bg-black text-white rounded-2xl flex items-center justify-between px-8 group overflow-hidden transition-all ${viewStep === steps.length - 1 ? 'opacity-0 pointer-events-none' : 'opacity-100'}`}
                        >
                            <span className="font-bold text-sm">Continue reading</span>
                            <div className="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center group-hover:bg-white transition-colors">
                                <ArrowRight className="w-5 h-5 group-hover:text-black transition-colors" />
                            </div>
                        </button>
                    </div>
                </div>

                {viewStep > 0 && (
                    <button
                        onClick={prevStep}
                        className="fixed bottom-10 left-1/2 -translate-x-1/2 flex items-center gap-2 text-[10px] font-black uppercase tracking-widest opacity-40 hover:opacity-100 transition-opacity"
                    >
                        <ArrowLeft size={14} />
                        <span>Go Back</span>
                    </button>
                )}
            </main>
        );
    };

    return (
        <>
            {renderContent()}

            {data.musicUrl && (
                <div className="fixed top-10 right-10 z-[100] flex items-center gap-4">
                    <iframe
                        src={`${data.musicUrl}${data.musicUrl.includes('?') ? '&' : '?'}autoplay=1`}
                        className="hidden"
                        allow="autoplay"
                    />
                    <div className="w-12 h-12 bg-white/10 backdrop-blur-3xl border border-white/20 rounded-full flex items-center justify-center animate-pulse">
                        <Heart size={20} className="text-red-500 fill-current" />
                    </div>
                </div>
            )}

            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="fixed bottom-10 right-10 z-[100]"
            >
                <Link
                    href="/"
                    className="flex items-center gap-3 px-6 py-4 bg-white/10 backdrop-blur-2xl border border-white/20 rounded-2xl group hover:bg-white transition-all shadow-2xl"
                >
                    <div className="p-2 bg-red-500 rounded-lg group-hover:scale-110 transition-transform">
                        <Heart size={14} className="text-white fill-current" />
                    </div>
                    <div className="flex flex-col items-start leading-none gap-1">
                        <span className="text-[10px] font-black uppercase tracking-widest text-red-500 group-hover:text-red-500 transition-colors">Elite Rebrand</span>
                        <span className="text-xs font-bold text-white group-hover:text-black transition-colors">Deploy Yours</span>
                    </div>
                </Link>
            </motion.div>
        </>
    );
}
