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
    const [passkey, setPasskey] = useState('');
    const [loginError, setLoginError] = useState(false);

    const [viewingReceipt, setViewingReceipt] = useState<string | null>(null);
    const [isAuthenticated, setIsAuthenticated] = useState(false);
    const [stats, setStats] = useState({ valentines: 0, users: 0, pending: 0, premium: 0 });

    const fetchDashboardData = async () => {
        try {
            const { apiGet } = await import('@/lib/api');
            const [reqData, statsData] = await Promise.all([
                apiGet('/admin/requests'),
                apiGet('/admin/stats')
            ]);
            setRequests(reqData);
            setStats(statsData);
        } catch (err: any) {
            showToast(err.response?.data?.error || 'Failed to fetch dashboard data', 'error');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        // Check if already authenticated via session/local state? 
        // For simplicity, we just use local state here. Ideally use cookie/session.
        // But since we want "better still ask for the passkey", we default to locked.
        setLoading(false);
    }, []);

    const handleLogin = (e: React.FormEvent) => {
        e.preventDefault();
        if (passkey === 'aaaaaa01') {
            setIsAuthenticated(true);
            setLoading(true);
            fetchDashboardData();
        } else {
            setLoginError(true);
            showToast('Invalid passkey', 'error');
        }
    };

    const handleApprove = async (req: UpgradeRequest) => {
        if (!confirm(`Are you sure you want to approve user ${req.userEmail}?`)) return;

        try {
            const { apiPost } = await import('@/lib/api');
            await apiPost('/admin/approve', { requestId: req.id, userId: req.userId });
            showToast('User approved successfully!', 'success');
            setRequests(prev => prev.filter(r => r.id !== req.id));
        } catch (err: any) {
            showToast(err.response?.data?.error || 'Failed to approve user', 'error');
        }
    };

    const handleReject = async (req: UpgradeRequest) => {
        if (!confirm(`Are you sure you want to REJECT request from ${req.userEmail}?`)) return;

        try {
            const { apiPost } = await import('@/lib/api');
            await apiPost('/admin/reject', { requestId: req.id });
            showToast('Request rejected', 'success');
            setRequests(prev => prev.filter(r => r.id !== req.id));
        } catch (err: any) {
            showToast(err.response?.data?.error || 'Failed to reject request', 'error');
        }
    };

    if (!isAuthenticated) {
        return (
            <div className="min-h-screen bg-[#050505] flex items-center justify-center text-white px-4">
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="max-w-md w-full bg-[#0A0A0A] border border-white/10 p-8 rounded-3xl text-center space-y-6 shadow-2xl"
                >
                    <div className="w-16 h-16 bg-myRed/10 rounded-full flex items-center justify-center mx-auto border border-myRed/20">
                        <User size={32} className="text-myRed" />
                    </div>
                    <div>
                        <h1 className="text-2xl font-black tracking-tight mb-2">Admin Access</h1>
                        <p className="text-white/40 text-sm">Enter passkey to manage requests</p>
                    </div>

                    <form onSubmit={handleLogin} className="space-y-4">
                        <input
                            type="password"
                            value={passkey}
                            onChange={(e) => {
                                setPasskey(e.target.value);
                                setLoginError(false);
                            }}
                            placeholder="Enter passkey..."
                            className={`w-full bg-white/5 border ${loginError ? 'border-red-500' : 'border-white/10'} rounded-xl px-4 py-3 outline-none focus:border-myRed/50 transition-colors text-center font-bold tracking-widest placeholder:tracking-normal`}
                        />
                        <button
                            type="submit"
                            className="w-full bg-myRed hover:bg-myRed/90 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-myRed/20"
                        >
                            Unlock Dashboard
                        </button>
                    </form>

                    <button onClick={() => router.push('/')} className="text-xs text-white/20 hover:text-white/40 transition-colors uppercase tracking-widest font-bold">
                        Return Home
                    </button>
                </motion.div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-[#050505] text-white p-6 md:p-12 relative">
            <div className="max-w-7xl mx-auto space-y-12">
                {/* Header */}
                <header className="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-8 border-b border-white/10">
                    <div>
                        <h1 className="text-4xl font-black tracking-tighter mb-2">Admin Dashboard</h1>
                        <p className="text-white/40 font-medium">Global romantic network oversight.</p>
                    </div>
                    <div className="flex items-center gap-3">
                        <button onClick={fetchDashboardData} className="p-3 bg-white/5 rounded-2xl hover:bg-white/10 transition-colors border border-white/5 flex items-center gap-2 text-xs font-bold uppercase tracking-widest">
                            <Clock size={16} className="text-myRed" />
                            Refresh Data
                        </button>
                        <button onClick={() => router.push('/')} className="p-3 bg-white/5 rounded-2xl hover:bg-white/10 transition-colors border border-white/5">
                            <Home size={20} />
                        </button>
                    </div>
                </header>

                {/* Stats Grid */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    {[
                        { label: 'Total Valentines', value: stats.valentines, icon: FileText, color: 'text-myRed' },
                        { label: 'Active Users', value: stats.users, icon: User, color: 'text-blue-500' },
                        { label: 'Premium Tier', value: stats.premium, icon: Sparkles, color: 'text-yellow-500' },
                        { label: 'Pending Upgrades', value: stats.pending, icon: Clock, color: 'text-orange-500' },
                    ].map((s, i) => (
                        <motion.div
                            key={i}
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: i * 0.1 }}
                            className="bg-[#0A0A0A] border border-white/5 p-6 rounded-[32px] space-y-2"
                        >
                            <s.icon className={`w-5 h-5 ${s.color}`} />
                            <div className="text-2xl font-black tracking-tighter">{s.value}</div>
                            <div className="text-[10px] font-black uppercase tracking-widest text-white/20">{s.label}</div>
                        </motion.div>
                    ))}
                </div>

                <div className="flex items-center gap-3">
                    <div className="w-1.5 h-1.5 rounded-full bg-myRed animate-pulse" />
                    <h2 className="text-xs font-black uppercase tracking-[0.3em] text-white/40">Live Upgrade Requests</h2>
                </div>

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
