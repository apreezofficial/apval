
import { motion } from 'framer-motion';
import { Heart, Sparkles, X, Share2, ArrowRight } from 'lucide-react';
import { useRouter } from 'next/navigation';

interface SuccessStepProps {
    link: string;
    onClose: () => void;
    templateId: string;
    showToast: (msg: string, type: 'success' | 'error') => void;
}

export default function SuccessStep({ link, onClose, templateId, showToast }: SuccessStepProps) {
    const router = useRouter();
    const isPremium = templateId === 'valentine-motion-premium';

    return (
        <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            className="absolute inset-0 z-[100] flex flex-col items-center justify-center p-8 bg-black/95 backdrop-blur-3xl"
        >
            <button
                onClick={() => {
                    onClose();
                    router.push('/dashboard');
                }}
                className="absolute top-10 right-10 p-3 bg-white/5 hover:bg-white/10 rounded-full transition-all border border-white/10 group"
            >
                <X className="w-6 h-6 text-white/40 group-hover:text-white" />
            </button>

            <div className="absolute inset-0 pointer-events-none overflow-hidden">
                {[...Array(24)].map((_, i) => (
                    <motion.div
                        key={i}
                        initial={{
                            top: "100%",
                            left: `${Math.random() * 100}%`,
                            scale: Math.random() * 0.5 + 0.5,
                            opacity: 0,
                            rotate: Math.random() * 360
                        }}
                        animate={{
                            top: "-10%",
                            opacity: [0, 1, 1, 0],
                            rotate: 360 + (Math.random() * 360),
                            x: (Math.random() - 0.5) * 300
                        }}
                        transition={{
                            duration: Math.random() * 4 + 3,
                            repeat: Infinity,
                            delay: Math.random() * 3,
                            ease: "linear"
                        }}
                        className="absolute"
                    >
                        <Heart className={`${i % 2 === 0 ? 'text-myRed/40' : 'text-[#D4AF37]/40'} fill-current w-6 h-6`} />
                    </motion.div>
                ))}
            </div>

            <motion.div
                initial={{ scale: 0.8, y: 30, opacity: 0 }}
                animate={{ scale: 1, y: 0, opacity: 1 }}
                transition={{ type: "spring", damping: 15, stiffness: 100 }}
                className="w-full max-w-md space-y-8 text-center relative z-10"
            >
                <div className="relative mx-auto w-32 h-32 mb-8">
                    <motion.div
                        animate={{
                            scale: [1, 1.1, 1],
                            rotate: [0, 5, -5, 0]
                        }}
                        transition={{ duration: 4, repeat: Infinity }}
                        className={`w-full h-full ${isPremium ? 'bg-[#D4AF37]/10' : 'bg-myRed/10'} rounded-[40px] flex items-center justify-center border border-white/5 relative z-10 shadow-2xl`}
                    >
                        <Heart className={`${isPremium ? 'text-[#D4AF37]' : 'text-myRed'} w-14 h-14 fill-current`} />
                    </motion.div>
                    <div className="absolute -top-4 -right-4 w-12 h-12 bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl flex items-center justify-center animate-bounce">
                        <Sparkles className="w-6 h-6 text-myRed" />
                    </div>
                </div>

                <div className="space-y-3">
                    <h2 className="text-4xl font-black tracking-tight italic uppercase text-white">
                        {isPremium ? 'Masterpiece Deployed!' : 'Asset Deployed!'}
                    </h2>
                    <p className="text-white/40 font-medium italic max-w-[300px] mx-auto leading-tight">
                        {isPremium
                            ? 'Your premium cinematic experience is now live on the decentralized web.'
                            : 'Your interactive card is now live and ready to be received.'}
                    </p>
                </div>

                <div className="p-8 bg-white/[0.03] rounded-[32px] border border-white/5 space-y-6 relative overflow-hidden group">
                    <div className="space-y-3">
                        <p className="text-[10px] font-black text-white/20 uppercase tracking-[0.4em] text-left ml-2">Secure Discovery URL</p>
                        <div className="flex items-center gap-3 text-white font-medium bg-black/60 p-4 rounded-2xl border border-white/10 overflow-hidden text-sm group-hover:border-myRed/30 transition-colors">
                            <span className="truncate flex-1 text-left font-mono opacity-60 italic">{link}</span>
                            <button
                                onClick={() => {
                                    navigator.clipboard.writeText(link);
                                    showToast('Secure link copied!', 'success');
                                }}
                                className="p-2 hover:bg-white/10 rounded-lg transition-colors"
                            >
                                <Share2 className="w-4 h-4 text-myRed" />
                            </button>
                        </div>
                    </div>

                    <button
                        onClick={() => window.open(link, '_blank')}
                        className="w-full py-4 bg-white text-black font-black uppercase tracking-widest rounded-2xl hover:bg-myRed hover:text-white transition-all shadow-xl flex items-center justify-center gap-3"
                    >
                        <span>Preview Live</span>
                        <ArrowRight className="w-4 h-4" />
                    </button>
                </div>
            </motion.div>
        </motion.div>
    );
}

import { ArrowRight } from 'lucide-react';
