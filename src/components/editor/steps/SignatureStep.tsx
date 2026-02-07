
import { motion } from 'framer-motion';
import { Send, Phone, Sparkles, ArrowRight } from 'lucide-react';
import { EditorData } from '../types';

interface SignatureStepProps {
    data: EditorData;
    onUpdate: (data: Partial<EditorData>) => void;
    onFinish: () => void;
    onBack: () => void;
    loading?: boolean;
    showButtonText?: boolean;
    showWhatsapp?: boolean;
}

export default function SignatureStep({
    data,
    onUpdate,
    onFinish,
    onBack,
    loading,
    showButtonText = false,
    showWhatsapp = true
}: SignatureStepProps) {
    return (
        <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -20 }}
            className="space-y-8"
        >
            <div className="space-y-4">
                <h2 className="text-3xl font-medium tracking-tight text-white">Signature</h2>
                <p className="text-white/40 font-medium">Whom shall we say sent this?</p>
            </div>
            <div className="space-y-4">
                <div className="relative">
                    <Send className="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                    <input
                        type="text"
                        value={data.sender}
                        onChange={(e) => onUpdate({ sender: e.target.value })}
                        placeholder="Your Name / Nickname"
                        className="w-full pl-16 pr-6 py-5 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium shadow-inner"
                    />
                </div>
                {showButtonText && (
                    <div className="space-y-2">
                        <label className="text-[10px] uppercase tracking-widest text-white/40 ml-2">Button Text</label>
                        <input
                            type="text"
                            value={data.buttonText}
                            onChange={(e) => onUpdate({ buttonText: e.target.value })}
                            placeholder="Send Your Wishes"
                            className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm"
                        />
                    </div>
                )}
                {showWhatsapp && (
                    <div className="relative">
                        <Phone className="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                        <input
                            type="text"
                            value={data.whatsapp}
                            onChange={(e) => onUpdate({ whatsapp: e.target.value })}
                            placeholder="WhatsApp Number (e.g. +234...)"
                            className="w-full pl-16 pr-6 py-5 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium shadow-inner"
                        />
                    </div>
                )}
            </div>
            <div className="flex gap-4">
                <button onClick={onBack} className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all">Back</button>
                <button
                    disabled={!data.sender || (showWhatsapp && !data.whatsapp) || loading}
                    onClick={onFinish}
                    className="flex-[2] py-5 bg-myRed hover:bg-myRed/90 text-white font-bold rounded-2xl transition-all flex items-center justify-center gap-3 relative overflow-hidden disabled:opacity-50 shadow-[0_10px_20px_-5px_rgba(252,65,0,0.4)]"
                >
                    {loading && (
                        <motion.div
                            className="absolute inset-0 bg-white/10"
                            initial={{ x: '-100%' }}
                            animate={{ x: '100%' }}
                            transition={{ repeat: Infinity, duration: 1, ease: 'linear' }}
                        />
                    )}
                    <span>{loading ? 'Processing...' : 'Deploy Asset'}</span>
                    <Sparkles className="w-5 h-5" />
                </button>
            </div>
        </motion.div>
    );
}
