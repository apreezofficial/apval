'use client';
import { motion, AnimatePresence } from 'framer-motion';
import { CheckCircle2, AlertCircle, X, Info } from 'lucide-react';
import { useState, useEffect, createContext, useContext, useCallback } from 'react';

type ToastType = 'success' | 'error' | 'info';

interface Toast {
    id: string;
    message: string;
    type: ToastType;
}

interface ToastContextType {
    showToast: (message: string, type?: ToastType) => void;
}

const ToastContext = createContext<ToastContextType | undefined>(undefined);

export function ToastProvider({ children }: { children: React.ReactNode }) {
    const [toasts, setToasts] = useState<Toast[]>([]);

    const showToast = useCallback((message: string, type: ToastType = 'success') => {
        const id = Math.random().toString(36).substring(2, 9);
        setToasts((prev) => [...prev, { id, message, type }]);
        setTimeout(() => {
            setToasts((prev) => prev.filter((t) => t.id !== id));
        }, 4000);
    }, []);

    return (
        <ToastContext.Provider value={{ showToast }}>
            {children}
            <div className="fixed bottom-8 right-8 z-[9999] flex flex-col gap-3 pointer-events-none">
                <AnimatePresence mode="popLayout">
                    {toasts.map((toast) => (
                        <motion.div
                            key={toast.id}
                            initial={{ opacity: 0, x: 50, scale: 0.9 }}
                            animate={{ opacity: 1, x: 0, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.9, transition: { duration: 0.2 } }}
                            className="pointer-events-auto"
                        >
                            <div className="bg-[#0A0A0A] border border-white/10 backdrop-blur-2xl px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 min-w-[300px]">
                                <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${toast.type === 'success' ? 'bg-green-500/10 text-green-500' :
                                        toast.type === 'error' ? 'bg-myRed/10 text-myRed' :
                                            'bg-blue-500/10 text-blue-500'
                                    }`}>
                                    {toast.type === 'success' && <CheckCircle2 size={20} />}
                                    {toast.type === 'error' && <AlertCircle size={20} />}
                                    {toast.type === 'info' && <Info size={20} />}
                                </div>
                                <div className="flex-1">
                                    <p className="text-white font-bold text-sm leading-tight">{toast.message}</p>
                                </div>
                                <button
                                    onClick={() => setToasts((prev) => prev.filter((t) => t.id !== toast.id))}
                                    className="text-white/20 hover:text-white transition-colors"
                                >
                                    <X size={16} />
                                </button>
                            </div>
                        </motion.div>
                    ))}
                </AnimatePresence>
            </div>
        </ToastContext.Provider>
    );
}

export function useToast() {
    const context = useContext(ToastContext);
    if (!context) throw new Error('useToast must be used within a ToastProvider');
    return context;
}
