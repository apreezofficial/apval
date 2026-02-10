import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Heart, Sparkles, X, Share2, ArrowRight, Edit2, Loader2, Check } from 'lucide-react';
import { useRouter } from 'next/navigation';
import { apiPut } from '@/lib/api';

interface SuccessStepProps {
    link: string;
    onClose: () => void;
    templateId: string;
    valentineId: string;
    isPremiumUser: boolean;
    showToast: (msg: string, type: 'success' | 'error') => void;
}

export default function SuccessStep({ link, onClose, templateId, valentineId, isPremiumUser, showToast }: SuccessStepProps) {
    const router = useRouter();
    const isPremiumTemplate = templateId === 'valentine-motion-premium';
    const [currentLink, setCurrentLink] = useState(link);

    const [isEditingSlug, setIsEditingSlug] = useState(false);
    const [slugInput, setSlugInput] = useState('');
    const [savingSlug, setSavingSlug] = useState(false);

    const handleSaveSlug = async () => {
        if (!slugInput) return;
        setSavingSlug(true);
        try {
            const userId = JSON.parse(localStorage.getItem('user') || '{}').id;
            const res = await apiPut('/valentines', {
                id: valentineId,
                customSlug: slugInput,
                userId
            });

            if (res.success) {
                const newLink = `${window.location.origin}/v/${slugInput}`;
                setCurrentLink(newLink);
                setIsEditingSlug(false);
                showToast('Custom link claimed!', 'success');
            } else {
                showToast(res.error || 'Failed to save slug', 'error');
            }
        } catch (e: any) {
            showToast(e.message || 'Failed to save slug', 'error');
        } finally {
            setSavingSlug(false);
        }
    };

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
                        className={`w-full h-full ${isPremiumTemplate ? 'bg-[#D4AF37]/10' : 'bg-myRed/10'} rounded-[40px] flex items-center justify-center border border-white/5 relative z-10 shadow-2xl`}
                    >
                        <Heart className={`${isPremiumTemplate ? 'text-[#D4AF37]' : 'text-myRed'} w-14 h-14 fill-current`} />
                    </motion.div>
                    <div className="absolute -top-4 -right-4 w-12 h-12 bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl flex items-center justify-center animate-bounce">
                        <Sparkles className="w-6 h-6 text-myRed" />
                    </div>
                </div>

                <div className="space-y-3">
                    <h2 className="text-4xl font-black tracking-tight italic uppercase text-white">
                        {isPremiumTemplate ? 'Masterpiece Deployed!' : 'Asset Deployed!'}
                    </h2>
                    <p className="text-white/40 font-medium italic max-w-[300px] mx-auto leading-tight">
                        {isPremiumTemplate
                            ? 'Your premium cinematic experience is now live on the decentralized web.'
                            : 'Your interactive card is now live and ready to be received.'}
                    </p>
                </div>

                <div className="p-8 bg-white/[0.03] rounded-[32px] border border-white/5 space-y-6 relative overflow-hidden group">
                    <div className="space-y-3">
                        <div className="flex items-center justify-between">
                            <p className="text-[10px] font-black text-white/20 uppercase tracking-[0.4em] ml-2">Secure Discovery URL</p>
                            {isPremiumUser && !isEditingSlug && (
                                <button
                                    onClick={() => { setIsEditingSlug(true); setSlugInput(valentineId); }}
                                    className="text-[10px] font-bold text-myRed hover:text-white bg-myRed/10 hover:bg-myRed px-2 py-1 rounded-md transition-all flex items-center gap-1"
                                >
                                    <Edit2 size={10} /> Customize
                                </button>
                            )}
                        </div>

                        {isEditingSlug ? (
                            <motion.div
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                className="space-y-4 p-4 bg-black/40 rounded-[24px] border border-myRed/30 shadow-inner"
                            >
                                <div className="space-y-2">
                                    <p className="text-[8px] font-black text-myRed uppercase tracking-[.3em] text-left ml-2">Claim Your Unique URL</p>
                                    <div className="flex items-center gap-2 bg-white/[0.03] p-4 rounded-2xl border border-white/5 focus-within:border-myRed/50 transition-all">
                                        <span className="text-white/20 text-xs font-mono select-none">apval.xyz/v/</span>
                                        <input
                                            type="text"
                                            value={slugInput}
                                            onChange={(e) => setSlugInput(e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, ''))}
                                            className="flex-1 bg-transparent border-none outline-none text-white font-mono text-sm placeholder:text-white/10"
                                            placeholder="my-name"
                                            autoFocus
                                        />
                                    </div>
                                </div>

                                <div className="flex gap-2">
                                    <button
                                        onClick={() => setIsEditingSlug(false)}
                                        disabled={savingSlug}
                                        className="flex-1 py-3 bg-white/5 text-white/60 font-black uppercase tracking-widest text-[10px] rounded-xl hover:bg-white/10 transition-all"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        onClick={handleSaveSlug}
                                        disabled={savingSlug || !slugInput}
                                        className="flex-[2] py-3 bg-myRed text-white font-black uppercase tracking-widest text-[10px] rounded-xl shadow-[0_10px_20px_-5px_rgba(252,65,0,0.3)] hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                                    >
                                        {savingSlug ? (
                                            <>
                                                <Loader2 size={14} className="animate-spin" />
                                                <span>Saving...</span>
                                            </>
                                        ) : (
                                            <>
                                                <Check size={14} />
                                                <span>Lock Link</span>
                                            </>
                                        )}
                                    </button>
                                </div>
                            </motion.div>
                        ) : (
                            <div className="flex items-center gap-3 text-white font-medium bg-black/60 p-4 rounded-2xl border border-white/10 overflow-hidden text-sm group-hover:border-myRed/30 transition-colors">
                                <span className="truncate flex-1 text-left font-mono opacity-60 italic">{currentLink}</span>
                                <button
                                    onClick={() => {
                                        navigator.clipboard.writeText(currentLink);
                                        showToast('Secure link copied!', 'success');
                                    }}
                                    className="p-2 hover:bg-white/10 rounded-lg transition-colors"
                                >
                                    <Share2 className="w-4 h-4 text-myRed" />
                                </button>
                            </div>
                        )}
                    </div>

                    <button
                        onClick={() => window.open(currentLink, '_blank')}
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

