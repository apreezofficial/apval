'use client';
import { motion, AnimatePresence } from 'framer-motion';
import { useState, createContext, useContext, useCallback } from 'react';
import { Heart, X, AlertTriangle } from 'lucide-react';

interface ModalOptions {
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    type?: 'danger' | 'info';
    onConfirm: () => void;
    onCancel?: () => void;
}

interface ModalContextType {
    confirm: (options: ModalOptions) => void;
}

const ModalContext = createContext<ModalContextType | undefined>(undefined);

export function ModalProvider({ children }: { children: React.ReactNode }) {
    const [options, setOptions] = useState<ModalOptions | null>(null);

    const confirm = useCallback((opts: ModalOptions) => {
        setOptions(opts);
    }, []);

    const handleConfirm = () => {
        if (options) {
            options.onConfirm();
            setOptions(null);
        }
    };

    const handleCancel = () => {
        if (options) {
            if (options.onCancel) options.onCancel();
            setOptions(null);
        }
    };

    return (
        <ModalContext.Provider value={{ confirm }}>
            {children}
            <AnimatePresence>
                {options && (
                    <div className="fixed inset-0 z-[10000] flex items-center justify-center p-6">
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            onClick={handleCancel}
                            className="absolute inset-0 bg-black/80 backdrop-blur-sm"
                        />
                        <motion.div
                            initial={{ opacity: 0, scale: 0.9, y: 20 }}
                            animate={{ opacity: 1, scale: 1, y: 0 }}
                            exit={{ opacity: 0, scale: 0.9, y: 20 }}
                            className="relative w-full max-w-md bg-[#0A0A0A] border border-white/10 rounded-[40px] p-10 shadow-3xl overflow-hidden"
                        >
                            {/* Decorative background */}
                            <div className="absolute top-0 right-0 w-32 h-32 bg-myRed/10 blur-[60px] rounded-full -translate-y-1/2 translate-x-1/2" />

                            <div className="relative z-10 space-y-8">
                                <div className="flex items-center gap-4">
                                    <div className={`w-14 h-14 rounded-2xl flex items-center justify-center ${options.type === 'danger' ? 'bg-myRed/10 text-myRed' : 'bg-blue-500/10 text-blue-500'
                                        }`}>
                                        {options.type === 'danger' ? <AlertTriangle size={28} /> : <Heart size={28} className="fill-current" />}
                                    </div>
                                    <h3 className="text-2xl font-bold tracking-tight text-white">{options.title}</h3>
                                </div>

                                <p className="text-white/40 font-medium leading-relaxed">
                                    {options.message}
                                </p>

                                <div className="flex gap-4 pt-4">
                                    <button
                                        onClick={handleCancel}
                                        className="flex-1 py-4 bg-white/5 text-white font-bold rounded-2xl hover:bg-white/10 transition-all border border-white/5"
                                    >
                                        {options.cancelText || 'Cancel'}
                                    </button>
                                    <button
                                        onClick={handleConfirm}
                                        className={`flex-1 py-4 font-bold rounded-2xl transition-all shadow-xl ${options.type === 'danger'
                                                ? 'bg-myRed text-white shadow-myRed/20 hover:bg-myRed/90'
                                                : 'bg-white text-black hover:bg-white/90'
                                            }`}
                                    >
                                        {options.confirmText || 'Confirm'}
                                    </button>
                                </div>
                            </div>

                            <button
                                onClick={handleCancel}
                                className="absolute top-6 right-6 text-white/20 hover:text-white transition-colors p-2"
                            >
                                <X size={20} />
                            </button>
                        </motion.div>
                    </div>
                )}
            </AnimatePresence>
        </ModalContext.Provider>
    );
}

export function useModal() {
    const context = useContext(ModalContext);
    if (!context) throw new Error('useModal must be used within a ModalProvider');
    return context;
}
