'use client';
import { useState } from 'react';
import { motion } from 'framer-motion';
import { Heart, Mail, Lock, User, ArrowRight } from 'lucide-react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useToast } from './Toast';

export default function RegisterClient() {
    const { showToast } = useToast();
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const router = useRouter();

    const handleRegister = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        try {
            const res = await fetch('/api/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email, password }),
            });
            const data = await res.json();
            if (data.id) {
                localStorage.setItem('user', JSON.stringify(data));
                showToast(`Welcome to Apval, ${data.name}!`, 'success');
                router.push('/');
            } else {
                showToast(data.error || 'Registration failed', 'error');
            }
        } catch (err) {
            showToast('Network error during registration', 'error');
        } finally {
            setLoading(false);
        }
    };

    return (
        <main className="min-h-screen bg-[#050505] flex items-center justify-center p-6 relative overflow-hidden">
            <div className="absolute inset-0 opacity-[0.03] pointer-events-none bg-[linear-gradient(to_right,#ffffff_1px,transparent_1px),linear-gradient(to_bottom,#ffffff_1px,transparent_1px)] bg-[size:40px_40px]" />
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[60vw] h-[60vw] bg-myRed/10 rounded-full blur-[150px] pointer-events-none" />

            <motion.div
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                className="w-full max-w-md relative z-10"
            >
                <div className="text-center mb-10">
                    <Link href="/" className="inline-flex items-center gap-2 mb-8 group">
                        <div className="w-12 h-12 bg-myRed rounded-2xl flex items-center justify-center shadow-lg transform group-hover:rotate-6 transition-transform">
                            <Heart className="text-white w-6 h-6 fill-current" />
                        </div>
                        <span className="text-white font-bold text-2xl tracking-tight uppercase">Ap<span className="text-myRed">val</span></span>
                    </Link>
                    <h1 className="text-4xl font-bold text-white tracking-tight">Create Account</h1>
                    <p className="text-white/40 mt-2 font-medium">Join the elite world of digital romance</p>
                </div>

                <div className="bg-[#0A0A0A] border border-white/5 p-10 rounded-[40px] shadow-2xl space-y-8">
                    <form onSubmit={handleRegister} className="space-y-6">
                        <div className="space-y-2">
                            <label className="text-xs font-bold text-white/30 uppercase tracking-widest flex items-center gap-2">
                                <User className="w-4 h-4" /> Full Name
                            </label>
                            <input
                                type="text"
                                required
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium transition-all"
                                placeholder="John Doe"
                            />
                        </div>

                        <div className="space-y-2">
                            <label className="text-xs font-bold text-white/30 uppercase tracking-widest flex items-center gap-2">
                                <Mail className="w-4 h-4" /> Email Address
                            </label>
                            <input
                                type="email"
                                required
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium transition-all"
                                placeholder="you@enterprise.com"
                            />
                        </div>

                        <div className="space-y-2">
                            <label className="text-xs font-bold text-white/30 uppercase tracking-widest flex items-center gap-2">
                                <Lock className="w-4 h-4" /> Password
                            </label>
                            <input
                                type="password"
                                required
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                className="w-full px-6 py-4 bg-white/[0.03] rounded-2xl border border-white/5 focus:border-myRed/50 outline-none text-white font-medium transition-all"
                                placeholder="••••••••"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={loading}
                            className="w-full py-5 bg-myRed text-white font-bold rounded-2xl hover:bg-myRed/90 transition-all flex items-center justify-center gap-3 shadow-[0_10px_30px_rgba(252,65,0,0.2)]"
                        >
                            <span>{loading ? 'Processing...' : 'Register'}</span>
                            <ArrowRight className="w-5 h-5" />
                        </button>
                    </form>

                    <div className="text-center">
                        <p className="text-white/40 text-sm font-medium">
                            Already have an account? <Link href="/login" className="text-myRed hover:underline">Sign In</Link>
                        </p>
                    </div>
                </div>
            </motion.div>
        </main>
    );
}
