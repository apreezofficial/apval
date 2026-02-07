'use client';
import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ArrowLeft, ArrowRight, Share2, Heart, RotateCcw, MessageCircle } from 'lucide-react';
import Link from 'next/link';
import AmourView from '@/components/templates/AmourView';
import MinimalEliteView from '@/components/templates/MinimalEliteView';
import PremiumMotionView from '@/components/templates/PremiumMotionView';
import QuestValentineView from '@/components/templates/QuestValentineView';
import InteractiveDodgeView from '@/components/templates/InteractiveDodgeView';
import ClassicValentineView from '@/components/templates/ClassicValentineView';
import NotFoundUI from '@/components/NotFoundUI';

interface ValentineViewClientProps {
    initialData: any;
    id: string;
}

export default function ValentineViewClient({ initialData, id }: ValentineViewClientProps) {
    const [data, setData] = useState<any>(initialData);
    const [mounted, setMounted] = useState(false);
    const [viewStep, setViewStep] = useState(0);
    const [isLoading, setIsLoading] = useState(true);
    const [statusIndex, setStatusIndex] = useState(0);
    const [resetKey, setResetKey] = useState(0);

    const statuses = [
        "Initializing Secure Connection...",
        "Scanning Heartbeat Database...",
        "Decrypting Cinematic Assets...",
        "Authenticating Signature...",
        "Finalizing Experience..."
    ];

    useEffect(() => {
        if (isLoading) {
            const interval = setInterval(() => {
                setStatusIndex((prev) => (prev + 1) % statuses.length);
            }, 600);
            return () => clearInterval(interval);
        }
    }, [isLoading]);

    useEffect(() => {
        setMounted(true);

        const searchAsset = async () => {
            // Artificial delay to make it feel like it's searching
            await new Promise(resolve => setTimeout(resolve, 2500));

            if (!initialData && id) {
                try {
                    const res = await fetch(`/api/valentines/${id}`);
                    const result = await res.json();
                    if (!result.error) setData(result);
                    else setData({ error: true });
                } catch (error) {
                    setData({ error: true });
                }
            }
            setIsLoading(false);
        };

        searchAsset();
    }, [id, initialData]);

    useEffect(() => {
        if (data && !data.error && !isLoading) {
            document.title = `${data.sender} sent you a Valentine! | Apval`;
        }
    }, [data, isLoading]);

    const LoadingShimmer = () => (
        <main className="min-h-screen relative bg-[#050505] flex flex-col items-center justify-center p-6 text-white overflow-hidden">
            {/* Ambient Background Glow */}
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[100vw] h-[100vw] bg-myRed/5 rounded-full blur-[120px] pointer-events-none animate-pulse" />

            {/* Grid Pattern */}
            <div className="absolute inset-0 opacity-[0.03] pointer-events-none bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:40px_40px]" />

            <motion.div
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                className="relative z-10 w-full max-w-[400px] aspect-[9/19] bg-[#111] rounded-[60px] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.8)] border-[12px] border-[#1a1a1a] overflow-hidden flex flex-col"
            >
                {/* Notch */}
                <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[120px] h-[34px] bg-[#1a1a1a] rounded-b-3xl z-50 flex items-center justify-center">
                    <div className="w-12 h-1 bg-white/10 rounded-full" />
                </div>

                <div className="flex-1 p-10 pt-24 flex flex-col justify-between relative bg-gradient-to-b from-[#1a1a1a] to-[#050505]">
                    <div className="space-y-12">
                        {/* Shimmer Card */}
                        <div className="relative w-32 h-40 bg-white/5 rounded-[32px] overflow-hidden">
                            <motion.div
                                animate={{ x: ['-100%', '100%'] }}
                                transition={{ duration: 1.5, repeat: Infinity, ease: "linear" }}
                                className="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent"
                            />
                            <div className="absolute inset-0 flex items-center justify-center">
                                <Heart className="w-10 h-10 text-white/10" />
                            </div>
                        </div>

                        {/* Shimmer Text */}
                        <div className="space-y-4">
                            {[1, 2, 3].map((i) => (
                                <div key={i} className="relative h-10 bg-white/5 rounded-2xl overflow-hidden" style={{ width: `${100 - (i * 15)}%` }}>
                                    <motion.div
                                        animate={{ x: ['-100%', '100%'] }}
                                        transition={{ duration: 2, repeat: Infinity, ease: "linear", delay: i * 0.2 }}
                                        className="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent"
                                    />
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="space-y-6">
                        {/* Shimmer Progress */}
                        <div className="flex gap-2">
                            {[1, 2, 3, 4].map((i) => (
                                <div key={i} className="h-1 flex-1 relative bg-white/5 rounded-full overflow-hidden">
                                    {i - 1 <= statusIndex && (
                                        <motion.div
                                            initial={{ x: '-100%' }}
                                            animate={{ x: '0%' }}
                                            className="absolute inset-0 bg-myRed"
                                        />
                                    )}
                                </div>
                            ))}
                        </div>
                        {/* Shimmer Button */}
                        <div className="h-16 bg-white/5 rounded-2xl w-full border border-white/5 overflow-hidden relative">
                            <motion.div
                                animate={{ x: ['-100%', '100%'] }}
                                transition={{ duration: 1.5, repeat: Infinity, ease: "linear" }}
                                className="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent"
                            />
                        </div>
                    </div>
                </div>
            </motion.div>

            {/* Status Footer */}
            <div className="mt-12 flex flex-col items-center gap-4 relative z-10">
                <div className="relative">
                    <div className="w-8 h-8 border-2 border-myRed/20 rounded-full" />
                    <motion.div
                        animate={{ rotate: 360 }}
                        transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
                        className="absolute inset-0 w-8 h-8 border-2 border-myRed border-t-transparent rounded-full"
                    />
                </div>
                <div className="flex flex-col items-center gap-1 text-center">
                    <AnimatePresence mode="wait">
                        <motion.span
                            key={statusIndex}
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -10 }}
                            className="text-[10px] font-black uppercase tracking-[0.4em] text-white/60 h-4"
                        >
                            {statuses[statusIndex]}
                        </motion.span>
                    </AnimatePresence>
                    <span className="text-[8px] font-bold text-myRed/40 uppercase tracking-widest bg-myRed/5 px-3 py-1 rounded-full border border-myRed/10 mt-2 block">
                        ENDPOINT: {id}
                    </span>
                </div>
            </div>
        </main>
    );

    if (!mounted || isLoading) return <LoadingShimmer />;

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

    const handleReplay = () => {
        setViewStep(0);
        setResetKey(prev => prev + 1);
    };

    const renderContent = () => {
        if (data.templateId === 'amour') {
            return <AmourView key={resetKey} data={data} steps={steps} />;
        }

        if (data.templateId === 'minimal-elite-card') {
            return <MinimalEliteView key={resetKey} data={data} />;
        }

        if (data.templateId === 'valentine-motion-premium') {
            return <PremiumMotionView key={resetKey} data={data} />;
        }

        if (data.templateId === 'quest-valentine') {
            return <QuestValentineView key={resetKey} data={data} />;
        }

        if (data.templateId === 'interactive-dodge') {
            return <InteractiveDodgeView key={resetKey} data={data} />;
        }

        if (data.templateId === 'classic-valentine') {
            return <ClassicValentineView key={resetKey} data={data} />;
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

                                        {data.whatsapp && (
                                            <div className="pt-6">
                                                <a
                                                    href={`https://wa.me/${data.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(`Hey ${data.sender}! I just saw the cinematic valentine you created for me on Apval. It's beautiful! ❤️`)}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-flex items-center gap-2 px-6 py-3 bg-[#25D366] text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-xl shadow-green-500/20"
                                                >
                                                    <MessageCircle className="w-4 h-4 fill-current" />
                                                    <span>Reply on WhatsApp</span>
                                                </a>
                                            </div>
                                        )}
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
                    {data.musicUrl.includes('youtube.com') || data.musicUrl.includes('youtu.be') || data.musicUrl.includes('spotify.com') ? (
                        <iframe
                            src={`${data.musicUrl}${data.musicUrl.includes('?') ? '&' : '?'}autoplay=1`}
                            className="hidden"
                            allow="autoplay"
                        />
                    ) : (
                        <audio src={data.musicUrl} autoPlay loop className="hidden" />
                    )}
                    <div className="w-12 h-12 bg-white/10 backdrop-blur-3xl border border-white/20 rounded-full flex items-center justify-center animate-pulse">
                        <Heart size={20} className="text-red-500 fill-current" />
                    </div>
                </div>
            )}

            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="fixed bottom-10 right-10 z-[100] flex items-center gap-4"
            >
                <button
                    onClick={handleReplay}
                    className="flex items-center gap-3 px-6 py-4 bg-white/10 backdrop-blur-2xl border border-white/20 rounded-2xl group hover:bg-white transition-all shadow-2xl"
                >
                    <div className="p-2 bg-white/10 rounded-lg group-hover:bg-myRed/10 transition-colors">
                        <RotateCcw size={14} className="text-white group-hover:text-myRed transition-colors" />
                    </div>
                    <div className="flex flex-col items-start leading-none gap-1">
                        <span className="text-[10px] font-black uppercase tracking-widest text-white/40 group-hover:text-myRed transition-colors">Restart</span>
                        <span className="text-xs font-bold text-white group-hover:text-black transition-colors">Replay</span>
                    </div>
                </button>

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
