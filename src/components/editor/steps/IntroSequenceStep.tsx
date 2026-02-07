
import { motion } from 'framer-motion';
import { Sparkles, ArrowRight } from 'lucide-react';
import { EditorData } from '../types';

interface IntroSequenceStepProps {
    data: EditorData;
    onUpdate: (data: Partial<EditorData>) => void;
    onNext: () => void;
    onBack: () => void;
}

export default function IntroSequenceStep({ data, onUpdate, onNext, onBack }: IntroSequenceStepProps) {
    return (
        <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -20 }}
            className="space-y-8"
        >
            <div className="space-y-4">
                <h2 className="text-3xl font-medium tracking-tight text-white">Intro Sequence</h2>
                <p className="text-white/40 font-medium">Customize the cinematic opening quotes.</p>
            </div>
            <div className="space-y-4">
                <div className="grid grid-cols-1 gap-4">
                    <div className="space-y-2">
                        <label className="text-[10px] uppercase tracking-widest text-white/40 ml-2">Quote 1 (Main / Sub)</label>
                        <input
                            type="text"
                            value={data.introQuote1Line1}
                            onChange={(e) => onUpdate({ introQuote1Line1: e.target.value })}
                            placeholder="Love is not just a feeling..."
                            className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm shadow-inner focus:border-myRed/50"
                        />
                        <input
                            type="text"
                            value={data.introQuote1Line2}
                            onChange={(e) => onUpdate({ introQuote1Line2: e.target.value })}
                            placeholder="it's a journey of the soul."
                            className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm italic opacity-60 shadow-inner focus:border-myRed/50"
                        />
                    </div>
                    <div className="space-y-2">
                        <label className="text-[10px] uppercase tracking-widest text-white/40 ml-2">Quote 2 (Main / Sub)</label>
                        <input
                            type="text"
                            value={data.introQuote2Line1}
                            onChange={(e) => onUpdate({ introQuote2Line1: e.target.value })}
                            placeholder="To bring"
                            className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm shadow-inner focus:border-myRed/50"
                        />
                        <input
                            type="text"
                            value={data.introQuote2Line2}
                            onChange={(e) => onUpdate({ introQuote2Line2: e.target.value })}
                            placeholder="someone special into your life."
                            className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm italic opacity-60 shadow-inner focus:border-myRed/50"
                        />
                    </div>
                </div>
            </div>
            <div className="flex gap-4">
                <button onClick={onBack} className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all">Back</button>
                <button
                    disabled={!data.introQuote1Line1 || !data.introQuote1Line2 || !data.introQuote2Line1 || !data.introQuote2Line2}
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
