'use client';
import { useEffect, useState } from 'react';
import Navbar from '@/components/Navbar';
import { motion, AnimatePresence } from 'framer-motion';
import { Heart, Plus, ExternalLink, Trash2, Edit2, Share2, X, MessageCircle, Sparkles } from 'lucide-react';
import Link from 'next/link';
import MultiStepEditor from '@/components/MultiStepEditor';
import Footer from '@/components/Footer';
import { useToast } from '@/components/Toast';
import { useModal } from '@/components/Modal';
import { apiGet, apiDelete } from '@/lib/api';

export default function DashboardClient() {
    const { showToast } = useToast();
    const { confirm } = useModal();
    const [user, setUser] = useState<any>(null);
    const [valentines, setValentines] = useState<any[]>([]);
    const [filter, setFilter] = useState<'all' | 'card' | 'website'>('all');
    const [isEditorOpen, setIsEditorOpen] = useState(false);
    const [editingValentine, setEditingValentine] = useState<any>(null);
    const [sharingValentine, setSharingValentine] = useState<any>(null);
    const [isShareModalOpen, setIsShareModalOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 6;

    const cardVariants = [
        { initial: { opacity: 0, scale: 0.8, rotate: -3 }, animate: { opacity: 1, scale: 1, rotate: 0 } },
        { initial: { opacity: 0, x: -50, filter: 'blur(5px)' }, animate: { opacity: 1, x: 0, filter: 'blur(0px)' } },
        { initial: { opacity: 0, y: 50, rotate: 3 }, animate: { opacity: 1, y: 0, rotate: 0 } },
    ];

    const fetchValentines = (userId: string) => {
        setIsLoading(true);
        apiGet(`/valentines/user/${userId}`)
            .then(data => {
                const sorted = Array.isArray(data) ? data.sort((a: any, b: any) =>
                    new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
                ) : [];
                setValentines(sorted);
                setIsLoading(false);
            })
            .catch(() => setIsLoading(false));
    };

    useEffect(() => {
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
            const parsedUser = JSON.parse(storedUser);
            setUser(parsedUser);
            fetchValentines(parsedUser.id);
        } else {
            window.location.href = '/login';
        }
    }, []);

    const filteredValentines = valentines.filter(v =>
        filter === 'all' || v.templateId === filter || (filter === 'card' && v.templateId !== 'amour')
    );

    const totalPages = Math.ceil(filteredValentines.length / itemsPerPage);
    const paginatedValentines = filteredValentines.slice(
        (currentPage - 1) * itemsPerPage,
        currentPage * itemsPerPage
    );

    useEffect(() => {
        setCurrentPage(1);
    }, [filter]);

    const handleEdit = (v: any) => {
        setEditingValentine(v);
        setIsEditorOpen(true);
    };

    const handleDelete = (id: string) => {
        confirm({
            title: 'Delete Asset?',
            message: 'This operation is irreversible. Your cinematic creation will be permanently removed from our delivery network.',
            confirmText: 'Delete Permanently',
            cancelText: 'Keep Asset',
            type: 'danger',
            onConfirm: async () => {
                try {
                    const result = await apiDelete(`/valentines?id=${id}`);
                    if (result.success) {
                        showToast('Asset successfully decommissioned', 'success');
                        fetchValentines(user.id);
                    } else {
                        showToast(result.error || 'Failed to delete asset', 'error');
                    }
                } catch (err) {
                    showToast('Network error during decommission', 'error');
                }
            }
        });
    };

    return (
        <main className="min-h-screen bg-[#050505] text-white">
            <Navbar />
            <div className="pt-32 px-6 md:px-20 max-w-7xl mx-auto min-h-[70vh]">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-16">
                    <div>
                        <h1 className="text-4xl md:text-5xl font-medium tracking-tight mb-2 text-white">
                            My Creations
                        </h1>
                        <p className="text-white/40">
                            Keep track of your heart's work and share the vibes
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-4">
                        <div className="flex items-center gap-1 p-1 bg-white/5 rounded-2xl border border-white/5">
                            {(['all', 'card', 'website'] as const).map((cat) => (
                                <button
                                    key={cat}
                                    onClick={() => setFilter(cat)}
                                    className={`px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all ${filter === cat
                                        ? 'bg-myRed text-white shadow-lg shadow-myRed/20'
                                        : 'text-white/40 hover:text-white hover:bg-white/5'
                                        }`}
                                >
                                    {cat}s
                                </button>
                            ))}
                        </div>
                        <Link
                            href="/templates"
                            className="inline-flex items-center gap-2 px-8 py-4 bg-myRed text-white rounded-full font-medium hover:bg-myRed/90 transition-all shadow-lg shadow-myRed/20"
                        >
                            <Plus className="w-5 h-5" />
                            <span>Create New</span>
                        </Link>
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-20">
                    {isLoading ? (
                        Array.from({ length: 6 }).map((_, i) => <SkeletonCard key={i} />)
                    ) : paginatedValentines.length > 0 ? paginatedValentines.map((v, index) => (
                        <motion.div
                            key={v.id}
                            initial={cardVariants[index % cardVariants.length].initial}
                            whileInView={cardVariants[index % cardVariants.length].animate}
                            viewport={{ once: true }}
                            transition={{ duration: 0.8, delay: (index % 6) * 0.1, ease: [0.16, 1, 0.3, 1] }}
                            className="bg-[#0A0A0A] border border-white/5 rounded-[32px] p-8 space-y-6 hover:border-myRed/20 transition-all group"
                        >
                            <div className="flex items-center justify-between">
                                <div className="w-12 h-12 bg-myRed/10 rounded-2xl flex items-center justify-center">
                                    <Heart className="text-myRed w-6 h-6 fill-current" />
                                </div>
                                <span className="text-[10px] font-bold uppercase tracking-widest text-white/20">
                                    Asset
                                </span>
                            </div>

                            <div>
                                <h3 className="text-xl font-bold mb-1 text-white">{v.headline} {v.recipient}</h3>
                                <p className="text-white/40 text-sm line-clamp-2 italic">{v.message}</p>
                            </div>

                            <div className="flex items-center gap-2 pt-4 border-t border-white/5">
                                <Link
                                    href={`/v/${v.id}`}
                                    className="flex-[2] flex items-center justify-center gap-2 py-3 bg-white/5 rounded-xl hover:bg-white/10 transition-all text-sm font-medium text-white"
                                    target="_blank"
                                >
                                    <ExternalLink className="w-4 h-4" />
                                    <span>View</span>
                                </Link>
                                <button
                                    onClick={() => handleEdit(v)}
                                    className="flex-1 p-3 bg-white/5 rounded-xl hover:bg-myRed/10 hover:text-myRed transition-all flex items-center justify-center text-white/40"
                                >
                                    <Edit2 className="w-4 h-4" />
                                </button>
                                <button
                                    onClick={() => {
                                        setSharingValentine(v);
                                        setIsShareModalOpen(true);
                                    }}
                                    className="p-3 bg-white/5 rounded-xl hover:bg-green-500/10 hover:text-green-500 transition-all text-white/40"
                                >
                                    <Share2 className="w-4 h-4" />
                                </button>
                                <button
                                    onClick={() => handleDelete(v.id)}
                                    className="p-3 bg-white/5 rounded-xl hover:bg-red-500/10 hover:text-red-500 transition-all text-white/40"
                                >
                                    <Trash2 className="w-4 h-4" />
                                </button>
                            </div>
                        </motion.div>
                    )) : (
                        <div className="col-span-full py-20 text-center border-2 border-dashed border-white/5 rounded-[40px]">
                            <p className="text-white/20">No creations yet. Start by choosing a template!</p>
                        </div>
                    )}
                </div>

                {!isLoading && totalPages > 1 && (
                    <div className="mt-16 mb-20 flex items-center justify-center gap-4">
                        <button
                            onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
                            disabled={currentPage === 1}
                            className="px-6 py-2 border border-white/5 rounded-xl text-xs font-bold uppercase tracking-widest disabled:opacity-20 hover:bg-white/5 transition-all text-white/40"
                        >
                            Previous
                        </button>
                        <span className="text-white/40 text-xs font-bold uppercase tracking-widest">
                            Page {currentPage} of {totalPages}
                        </span>
                        <button
                            onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
                            disabled={currentPage === totalPages}
                            className="px-6 py-2 border border-white/5 rounded-xl text-xs font-bold uppercase tracking-widest disabled:opacity-20 hover:bg-white/5 transition-all text-white/40"
                        >
                            Next
                        </button>
                    </div>
                )}
            </div>

            <Footer />

            <AnimatePresence>
                {isEditorOpen && (
                    <MultiStepEditor
                        templateId={editingValentine?.templateId || 'premium'}
                        editId={editingValentine?.id}
                        onClose={() => {
                            setIsEditorOpen(false);
                            setEditingValentine(null);
                            if (user) fetchValentines(user.id);
                        }}
                    />
                )}
            </AnimatePresence>

            <AnimatePresence>
                {isShareModalOpen && sharingValentine && (
                    <ShareModal
                        valentine={sharingValentine}
                        onClose={() => {
                            setIsShareModalOpen(false);
                            setSharingValentine(null);
                        }}
                    />
                )}
            </AnimatePresence>
        </main>
    );
}

function SkeletonCard() {
    return (
        <div className="bg-[#0A0A0A] border border-white/5 rounded-[32px] p-8 space-y-6 animate-pulse">
            <div className="flex items-center justify-between">
                <div className="w-12 h-12 bg-white/5 rounded-2xl" />
                <div className="w-20 h-3 bg-white/5 rounded-full" />
            </div>
            <div className="space-y-3">
                <div className="w-3/4 h-6 bg-white/5 rounded-lg" />
                <div className="w-full h-4 bg-white/5 rounded-lg" />
                <div className="w-1/2 h-4 bg-white/5 rounded-lg" />
            </div>
            <div className="flex gap-2 pt-4">
                <div className="flex-[2] h-12 bg-white/5 rounded-xl" />
                <div className="flex-1 h-12 bg-white/5 rounded-xl" />
                <div className="w-12 h-12 bg-white/5 rounded-xl" />
            </div>
        </div>
    );
}

function ShareModal({ valentine, onClose }: { valentine: any; onClose: () => void }) {
    const link = `${window.location.origin}/v/${valentine.id}`;
    const { showToast } = useToast();

    return (
        <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[200] flex items-center justify-center p-6 bg-black/90 backdrop-blur-2xl"
        >
            <motion.div
                initial={{ scale: 0.9, y: 20, opacity: 0 }}
                animate={{ scale: 1, y: 0, opacity: 1 }}
                exit={{ scale: 0.9, y: 20, opacity: 0 }}
                className="w-full max-w-md bg-[#0A0A0A] border border-white/10 rounded-[40px] p-10 space-y-8 relative shadow-2xl"
            >
                <button
                    onClick={onClose}
                    className="absolute top-6 right-6 p-2 hover:bg-white/5 rounded-full transition-colors text-white/40 hover:text-white"
                >
                    <X size={20} />
                </button>

                <div className="text-center space-y-4">
                    <div className="w-20 h-20 bg-myRed/10 rounded-3xl flex items-center justify-center mx-auto border border-myRed/20">
                        <Heart className="text-myRed w-10 h-10 fill-current" />
                    </div>
                    <div className="space-y-2">
                        <h2 className="text-2xl font-bold tracking-tight">Transmit Asset</h2>
                        <p className="text-white/40 text-sm italic">"{valentine.headline} {valentine.recipient}"</p>
                    </div>
                </div>

                <div className="p-6 bg-white/[0.03] rounded-3xl border border-white/5 space-y-4">
                    <p className="text-[10px] font-black text-white/20 uppercase tracking-[0.4em] ml-2">Secure Link</p>
                    <div className="flex items-center gap-3 bg-black/40 p-3 rounded-2xl border border-white/5">
                        <span className="flex-1 truncate text-xs font-mono opacity-60 px-2 italic">{link}</span>
                        <button
                            onClick={() => {
                                navigator.clipboard.writeText(link);
                                showToast('Link copied to clipboard', 'success');
                            }}
                            className="px-4 py-2 bg-myRed text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:scale-105 active:scale-95 transition-all shadow-lg shadow-myRed/20"
                        >
                            Copy
                        </button>
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <button
                        onClick={() => window.open(`https://wa.me/?text=${encodeURIComponent(`Check out this cinematic valentine I created for you: ${link}`)}`, '_blank')}
                        className="flex flex-col items-center gap-3 p-4 bg-white/5 rounded-2xl border border-white/5 hover:bg-green-500/10 hover:border-green-500/30 transition-all group/btn"
                    >
                        <div className="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center text-green-500 group-hover/btn:scale-110 transition-transform">
                            <MessageCircle className="w-5 h-5 fill-current" />
                        </div>
                        <span className="text-[8px] font-black uppercase tracking-widest text-white/40 group-hover/btn:text-white">WhatsApp</span>
                    </button>
                    <button
                        onClick={async () => {
                            if (navigator.share) {
                                try {
                                    await navigator.share({
                                        title: 'Cinematic Valentine',
                                        text: `Check out this cinematic valentine: ${valentine.headline}`,
                                        url: link,
                                    });
                                } catch (err) { }
                            } else {
                                navigator.clipboard.writeText(link);
                                showToast('Discovery URL Copied!', 'success');
                            }
                        }}
                        className="flex flex-col items-center gap-3 p-4 bg-white/5 rounded-2xl border border-white/5 hover:bg-myRed/10 hover:border-myRed/30 transition-all group/btn"
                    >
                        <div className="w-10 h-10 bg-myRed/20 rounded-full flex items-center justify-center text-myRed group-hover/btn:scale-110 transition-transform">
                            <Share2 className="w-5 h-5" />
                        </div>
                        <span className="text-[8px] font-black uppercase tracking-widest text-white/40 group-hover/btn:text-white">Universal</span>
                    </button>
                </div>
            </motion.div>
        </motion.div>
    );
}
