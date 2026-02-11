
import { motion } from 'framer-motion';
import { Sparkles, ArrowRight } from 'lucide-react';
import { EditorData } from '../types';

interface IntroMessagesStepProps {
    data: EditorData;
    onUpdate: (data: Partial<EditorData>) => void;
    onNext: () => void;
    onBack: () => void;
}

export default function IntroMessagesStep({ data, onUpdate, onNext, onBack }: IntroMessagesStepProps) {
    return (
        <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -20 }}
            className="space-y-8"
        >
            <div className="space-y-4">
                <h2 className="text-3xl font-medium tracking-tight text-white">The Build Up</h2>
                <p className="text-white/40 font-medium">Set the stage with 4 cinematic messages.</p>
            </div>
            <div className="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                {data.introMessages.map((msg: string, idx: number) => (
                    <div key={idx} className="space-y-2">
                        <label className="text-[10px] uppercase tracking-widest text-white/40 ml-2">Message {idx + 1}</label>
                        <input
                            type="text"
                            value={msg}
                            onChange={(e) => {
                                const newMsgs = [...data.introMessages];
                                newMsgs[idx] = e.target.value;
                                onUpdate({ introMessages: newMsgs });
                            }}
                            placeholder={`Message ${idx + 1}...`}
                            className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 outline-none text-white text-sm shadow-inner focus:border-myRed/50"
                        />
                    </div>
                ))}
            </div>
            <div className="flex gap-4">
                <button onClick={onBack} className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all">Back</button>
                <button
                    disabled={data.introMessages.some((m: string) => !m)}
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
