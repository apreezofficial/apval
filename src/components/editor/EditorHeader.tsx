
import { Heart, X } from 'lucide-react';

interface EditorHeaderProps {
    step: number;
    totalSteps: number;
    onClose: () => void;
}

export default function EditorHeader({ step, totalSteps, onClose }: EditorHeaderProps) {
    return (
        <div className="p-8 border-b border-white/5 flex items-center justify-between">
            <div className="flex items-center gap-3">
                <div className="w-8 h-8 bg-myRed rounded-lg flex items-center justify-center">
                    <Heart className="text-white w-4 h-4 fill-current" />
                </div>
                <span className="text-xs font-bold uppercase tracking-widest text-white/40">
                    Step {step} of {totalSteps}
                </span>
            </div>
            <button onClick={onClose} className="p-2 hover:bg-white/5 rounded-full transition-colors">
                <X className="w-5 h-5 text-white/40" />
            </button>
        </div>
    );
}
