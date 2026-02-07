
import { motion } from 'framer-motion';
import { User, Sparkles, ArrowRight } from 'lucide-react';
import { EditorData } from '../types';

interface RecipientStepProps {
    data: EditorData;
    onUpdate: (data: Partial<EditorData>) => void;
    onNext: () => void;
    showHeadline?: boolean;
    title?: string;
    description?: string;
}

export default function RecipientStep({
    data,
    onUpdate,
    onNext,
    showHeadline = true,
    title = "Recipient & Headline",
    description = "To whom is this message assigned?"
}: RecipientStepProps) {
    return (
        <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -20 }}
            className="space-y-8"
        >
            <div className="space-y-4">
                <h2 className="text-3xl font-medium tracking-tight text-white">{title}</h2>
                <p className="text-white/40 font-medium">{description}</p>
            </div>
            <div className="space-y-4">
                {showHeadline && (
                    <div className="relative">
                        <Sparkles className="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                        <input
                            type="text"
                            value={data.headline}
                            onChange={(e) => onUpdate({ headline: e.target.value })}
                            placeholder="Headline (e.g. For my love)"
                            className="w-full pl-16 pr-6 py-5 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium"
                        />
                    </div>
                )}
                <div className="relative">
                    <User className="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-white/20" />
                    <input
                        type="text"
                        value={data.recipient}
                        onChange={(e) => onUpdate({ recipient: e.target.value })}
                        placeholder="Recipient Name"
                        className="w-full pl-16 pr-6 py-5 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium"
                    />
                </div>
            </div>
            <button
                disabled={!data.recipient || (showHeadline && !data.headline)}
                onClick={onNext}
                className="w-full py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3 disabled:opacity-50"
            >
                <span>Continue</span>
                <ArrowRight className="w-5 h-5" />
            </button>
        </motion.div>
    );
}
