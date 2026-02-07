
import { motion } from 'framer-motion';
import { Sparkles, ArrowRight } from 'lucide-react';
import { EditorData } from '../types';

interface AttachmentStepProps {
    data: EditorData;
    onUpdate: (data: Partial<EditorData>) => void;
    onNext: () => void;
    onBack: () => void;
}

export default function AttachmentStep({ data, onUpdate, onNext, onBack }: AttachmentStepProps) {
    const handleImageUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            const reader = new FileReader();
            reader.onloadend = () => {
                onUpdate({ imageUrl: reader.result as string });
            };
            reader.readAsDataURL(file);
        }
    };

    return (
        <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            exit={{ opacity: 0, x: -20 }}
            className="space-y-8"
        >
            <div className="space-y-4">
                <h2 className="text-3xl font-medium tracking-tight text-white">Attachment</h2>
                <p className="text-white/40 font-medium">Upload a photo to seal the memory.</p>
            </div>
            <div className="space-y-4">
                <label className="flex flex-col items-center justify-center w-full aspect-video bg-white/[0.03] rounded-[32px] border-2 border-dashed border-white/5 hover:border-myRed/30 cursor-pointer transition-all group overflow-hidden shadow-inner">
                    {data.imageUrl ? (
                        <img src={data.imageUrl} className="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity" alt="Selected" />
                    ) : (
                        <div className="flex flex-col items-center gap-4">
                            <div className="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center group-hover:bg-myRed/20 transition-colors">
                                <Sparkles className="w-8 h-8 text-white/20 group-hover:text-myRed" />
                            </div>
                            <span className="text-xs font-bold text-white/20 uppercase tracking-widest">Select Image</span>
                        </div>
                    )}
                    <input type="file" className="hidden" accept="image/*" onChange={handleImageUpload} />
                </label>
            </div>
            <div className="flex gap-4">
                <button onClick={onBack} className="flex-1 py-5 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all">Back</button>
                <button
                    onClick={onNext}
                    className="flex-[2] py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3 shadow-lg"
                >
                    <span>{data.imageUrl ? 'Confirm Image' : 'Skip'}</span>
                    <ArrowRight className="w-5 h-5" />
                </button>
            </div>
        </motion.div>
    );
}
