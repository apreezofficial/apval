
import { motion } from 'framer-motion';
import { MessageCircle, ArrowRight } from 'lucide-react';
import { EditorData } from '../types';

interface ContentStepProps {
    data: EditorData;
    onUpdate: (data: Partial<EditorData>) => void;
    onNext: () => void;
    onBack: () => void;
    title?: string;
    description?: string;
    showExtraFields?: boolean;
}

export default function ContentStep({
    data,
    onUpdate,
    onNext,
    onBack,
    title = "The Content",
    description = "Write something unforgettable.",
    showExtraFields = false
}: ContentStepProps) {
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
                <div className="relative">
                    <MessageCircle className="absolute left-6 top-6 w-5 h-5 text-white/20" />
                    <textarea
                        rows={5}
                        value={data.message}
                        onChange={(e) => onUpdate({ message: e.target.value })}
                        placeholder="My heart belongs strictly to you..."
                        className="w-full pl-16 pr-6 py-5 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium resize-none shadow-inner"
                    />
                </div>
                {showExtraFields && (
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <label className="text-[10px] uppercase tracking-widest text-white/40 ml-2">Happy Text</label>
                            <input
                                type="text"
                                value={data.happyText}
                                onChange={(e) => onUpdate({ happyText: e.target.value })}
                                placeholder="Happy"
                                className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm"
                            />
                        </div>
                        <div className="space-y-2">
                            <label className="text-[10px] uppercase tracking-widest text-white/40 ml-2">Valentine Text</label>
                            <input
                                type="text"
                                value={data.valentineText}
                                onChange={(e) => onUpdate({ valentineText: e.target.value })}
                                placeholder="Valentine's"
                                className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm"
                            />
                        </div>
                    </div>
                )}
            </div>
            <div className="flex gap-4">
                <button onClick={onBack} className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all">Back</button>
                <button
                    disabled={!data.message}
                    onClick={onNext}
                    className="flex-[2] py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3 disabled:opacity-50 shadow-lg"
                >
                    <span>Continue</span>
                    <ArrowRight className="w-5 h-5" />
                </button>
            </div>
        </motion.div>
    );
}
