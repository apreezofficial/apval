'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { motion, AnimatePresence } from 'framer-motion';
import { useToast } from '@/components/Toast';
import { Check, X, Eye, Trash2, Home, User, Clock, FileText, Image as ImageIcon } from 'lucide-react';

interface UpgradeRequest {
    id: string;
    userId: string;
    userEmail: string;
    receipt: string; // base64
    timestamp: string;
    status: 'pending' | 'approved' | 'rejected';
}

export default function AdminDashboardClient() {
    const router = useRouter();
    const { showToast } = useToast();
    const [requests, setRequests] = useState<UpgradeRequest[]>([]);
    const [loading, setLoading] = useState(true);
    const [viewingReceipt, setViewingReceipt] = useState<string | null>(null);
    const [isAuthenticated, setIsAuthenticated] = useState(false);

    const fetchRequests = async () => {
        try {
            const res = await fetch('/api/admin/requests');
            if (res.ok) {
                const data = await res.json();
                setRequests(data);
            } else {
                showToast('Failed to fetch requests', 'error');
            }
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        // Authenticate
        const userStr = localStorage.getItem('user');
        if (!userStr) {
            router.push('/login');
            return;
        }

        try {
            const user = JSON.parse(userStr);
            if (user.email !== 'aa@aa.aa') {
                setIsAuthenticated(false);
                setLoading(false);
                return;
            }
            setIsAuthenticated(true);
            fetchRequests();
        } catch (e) {
            router.push('/login');
        }
    }, [router]);

    const handleApprove = async (req: UpgradeRequest) => {
        if (!confirm(`Are you sure you want to approve user ${req.userEmail}?`)) return;

        try {
            const res = await fetch('/api/admin/approve', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ requestId: req.id, userId: req.userId })
            });

            if (res.ok) {
                showToast('User approved successfully!', 'success');
                setRequests(prev => prev.filter(r => r.id !== req.id));
            } else {
                showToast('Failed to approve user', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('An error occurred', 'error');
        }
    };

    const handleReject = async (req: UpgradeRequest) => {
        if (!confirm(`Are you sure you want to REJECT request from ${req.userEmail}?`)) return;

        try {
            const res = await fetch('/api/admin/reject', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ requestId: req.id })
            });

            if (res.ok) {
                showToast('Request rejected', 'success');
                setRequests(prev => prev.filter(r => r.id !== req.id));
            } else {
                showToast('Failed to reject request', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('An error occurred', 'error');
        }
    };

    if (!isAuthenticated && !loading) {
        return (
            <div className="min-h-screen bg-[#050505] flex items-center justify-center text-white">
                <div className="text-center space-y-4">
                    <h1 className="text-4xl font-black text-myRed">Access Denied</h1>
                    <p className="text-white/60">You do not have permission to view this page.</p>
                    <button onClick={() => router.push('/')} className="px-6 py-2 bg-white/10 rounded-full hover:bg-white/20 transition-colors">
                        Return Home
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-[#050505] text-white p-6 md:p-12 relative">
            <div className="max-w-7xl mx-auto space-y-12">
                {/* Header */}
                <header className="flex items-center justify-between pb-8 border-b border-white/10">
                    <div>
                        <h1 className="text-3xl font-black tracking-tight mb-2">Admin Dashboard</h1>
                        <p className="text-white/40">Manage premium upgrade requests</p>
                    </div>
                    <button onClick={() => router.push('/')} className="p-3 bg-white/5 rounded-full hover:bg-white/10 transition-colors">
                        <Home size={20} />
                    </button>
                </header>

                {loading ? (
                    <div className="flex justify-center items-center h-64">
                        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-myRed"></div>
                    </div>
                ) : requests.length === 0 ? (
                    <div className="flex flex-col items-center justify-center h-64 text-center space-y-4 bg-white/5 rounded-3xl border border-dashed border-white/10">
                        <FileText size={48} className="text-white/20" />
                        <p className="text-white/40 font-medium">No pending requests</p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <AnimatePresence>
                            {requests.map((req) => (
                                <motion.div
                                    key={req.id}
                                    initial={{ opacity: 0, scale: 0.9 }}
                                    animate={{ opacity: 1, scale: 1 }}
                                    exit={{ opacity: 0, scale: 0.9 }}
                                    className="bg-[#0A0A0A] border border-white/10 rounded-3xl p-6 space-y-6 shadow-2xl overflow-hidden"
                                >
                                    <div className="flex items-center justify-between">
                                        <div className="px-3 py-1 bg-yellow-500/10 text-yellow-500 text-xs font-bold rounded-full border border-yellow-500/20">
                                            PENDING
                                        </div>
                                        <div className="flex items-center gap-1 text-xs text-white/40">
                                            <Clock size={12} />
                                            <span>{new Date(req.timestamp).toLocaleDateString()}</span>
                                        </div>
                                    </div>

                                    <div className="space-y-4">
                                        <div className="flex items-center gap-3">
                                            <div className="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center">
                                                <User size={20} className="text-white/60" />
                                            </div>
                                            <div>
                                                <p className="font-bold text-sm text-white/90 truncate max-w-[200px]">{req.userEmail}</p>
                                                <p className="text-xs text-white/40 font-mono">{req.userId.slice(0, 8)}...</p>
                                            </div>
                                        </div>

                                        <div
                                            className="relative aspect-video bg-black/40 rounded-xl overflow-hidden border border-white/10 group cursor-pointer"
                                            onClick={() => setViewingReceipt(req.receipt)}
                                        >
                                            <img src={req.receipt} alt="Receipt" className="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity" />
                                            <div className="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/40">
                                                <Eye className="text-white drop-shadow-lg" />
                                            </div>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-3 pt-2">
                                        <button
                                            onClick={() => handleReject(req)}
                                            className="py-3 px-4 bg-red-500/10 hover:bg-red-500/20 text-red-500 font-bold rounded-xl border border-red-500/20 transition-all flex items-center justify-center gap-2 text-sm"
                                        >
                                            <X size={16} />
                                            Reject
                                        </button>
                                        <button
                                            onClick={() => handleApprove(req)}
                                            className="py-3 px-4 bg-green-500/10 hover:bg-green-500/20 text-green-500 font-bold rounded-xl border border-green-500/20 transition-all flex items-center justify-center gap-2 text-sm"
                                        >
                                            <Check size={16} />
                                            Approve
                                        </button>
                                    </div>
                                </motion.div>
                            ))}
                        </AnimatePresence>
                    </div>
                )}
            </div>

            {/* Receipt Modal */}
            <AnimatePresence>
                {viewingReceipt && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        className="fixed inset-0 z-[200] bg-black/90 backdrop-blur-xl flex items-center justify-center p-4 cursor-pointer"
                        onClick={() => setViewingReceipt(null)}
                    >
                        <button className="absolute top-6 right-6 p-2 bg-white/10 rounded-full hover:bg-white/20 transition-colors text-white">
                            <X size={24} />
                        </button>
                        <motion.img
                            initial={{ scale: 0.9 }}
                            animate={{ scale: 1 }}
                            src={viewingReceipt}
                            alt="Receipt Full"
                            className="max-w-full max-h-[90vh] rounded-2xl shadow-2xl border border-white/10"
                            onClick={(e) => e.stopPropagation()}
                        />
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}
