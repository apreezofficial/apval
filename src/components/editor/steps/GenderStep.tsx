
import { motion } from 'framer-motion';
import { Heart, Sparkles, ArrowRight } from 'lucide-react';
import { EditorData } from '../types';

interface GenderStepProps {
    data: EditorData;
    onUpdate: (data: Partial<EditorData>) => void;
    onNext: () => void;
    onBack: () => void;
}

export default function GenderStep({ data, onUpdate, onNext, onBack }: GenderStepProps) {
    return (
        <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -20 }}
            className="space-y-8"
        >
            <div className="space-y-4">
                <h2 className="text-3xl font-medium tracking-tight text-white">Vibe Selection</h2>
                <p className="text-white/40 font-medium">Who is this for? We'll tailor the theme.</p>
            </div>
            <div className="grid grid-cols-2 gap-4">
                <button
                    onClick={() => onUpdate({ gender: 'female' })}
                    className={`p-8 rounded-[32px] border transition-all flex flex-col items-center gap-4 ${data.gender === 'female' ? 'bg-pink-500/10 border-pink-500 text-pink-500' : 'bg-white/5 border-white/5 text-white/40 hover:bg-white/10'}`}
                >
                    <Heart className={`w-12 h-12 ${data.gender === 'female' ? 'fill-current' : ''}`} />
                    <span className="text-sm font-bold uppercase tracking-widest">For Her</span>
                </button>
                <button
                    onClick={() => onUpdate({ gender: 'male' })}
                    className={`p-8 rounded-[32px] border transition-all flex flex-col items-center gap-4 ${data.gender === 'male' ? 'bg-blue-500/10 border-blue-500 text-blue-500' : 'bg-white/5 border-white/5 text-white/40 hover:bg-white/10'}`}
                >
                    <Sparkles className={`w-12 h-12 ${data.gender === 'male' ? 'fill-current' : ''}`} />
                    <span className="text-sm font-bold uppercase tracking-widest">For Him</span>
                </button>
            </div>
            <div className="flex gap-4">
                <button onClick={onBack} className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all">Back</button>
                <button
                    onClick={onNext}
                    className="flex-[2] py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3 shadow-lg"
                >
                    <span>Continue</span>
                    <ArrowRight className="w-5 h-5" />
                </button>
            </div>
        </motion.div>
    );
}
