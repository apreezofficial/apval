'use client';
import Link from 'next/link';
import { motion, AnimatePresence } from 'framer-motion';
import { ChevronDown, X, Menu, ArrowRight, Instagram, Twitter, Facebook } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function Navbar() {
    const [user, setUser] = useState<any>(null);
    const [isLightMode, setIsLightMode] = useState(false);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const [isLegalOpen, setIsLegalOpen] = useState(false);
    const [isMobileLegalOpen, setIsMobileLegalOpen] = useState(false);

    useEffect(() => {
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
            const parsedUser = JSON.parse(storedUser);
            setUser(parsedUser);

            // Verify session with server if user exists
            if (parsedUser.id) {
                const checkSession = async () => {
                    try {
                        const res = await fetch(`/api/user?id=${parsedUser.id}`);
                        if (!res.ok) {
                            // Session invalid or user deleted
                            handleLogout();
                        }
                    } catch (e) {
                        // Network error, ignore for now to keep offline/flaky ux stable
                    }
                };
                checkSession();
            }
        }

        const lightPages = ['/developer', '/faq', '/privacy', '/terms'];
        setIsLightMode(lightPages.includes(window.location.pathname));
    }, []);

    useEffect(() => {
        if (isMobileMenuOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'unset';
        }
        return () => { document.body.style.overflow = 'unset'; };
    }, [isMobileMenuOpen]);

    const handleLogout = () => {
        localStorage.removeItem('user');
        setUser(null);
        window.location.reload();
    };

    const textColor = isLightMode ? 'text-black' : 'text-white';
    const subTextColor = isLightMode ? 'text-black/60' : 'text-white/60';
    const navBg = isLightMode ? 'bg-white/95' : 'bg-black/80';
    const borderColor = isLightMode ? 'border-black/5' : 'border-white/10';
    const mobileBtnBg = 'bg-myRed text-white';

    const menuItems = [
        { name: 'Home', href: '/' },
        { name: 'Templates', href: '/templates' },
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'About', href: '/developer' },
    ];

    return (
        <nav className="fixed top-0 left-0 right-0 z-[100] px-4 py-6 md:px-6 pointer-events-none">
            <div className="max-w-7xl mx-auto flex justify-center pointer-events-auto">
                <motion.div
                    initial={{ y: -20, opacity: 0 }}
                    animate={{ y: 0, opacity: 1 }}
                    className={`flex items-center justify-between w-full max-w-6xl px-8 py-4 rounded-full backdrop-blur-xl shadow-lg border ${borderColor} ${navBg}`}
                >
                    {/* Logo - Updated blue hover to myRed */}
                    <Link className={`text-[1.3rem] font-grotesk font-black tracking-tight ${textColor} hover:text-myRed transition-colors z-[110]`} href="/">
                        Apval<span className="text-myRed">.</span>
                    </Link>

                    {/* Desktop Menu */}
                    <div className="hidden md:flex items-center space-x-8">
                        {menuItems.map((item) => (
                            <Link
                                key={item.name}
                                className={`font-semibold text-[0.85rem] ${textColor} hover:text-myRed transition-colors`}
                                href={item.href}
                            >
                                {item.name}
                            </Link>
                        ))}

                        {/* Legal Dropdown Desktop */}
                        <div
                            className="relative"
                            onMouseEnter={() => setIsLegalOpen(true)}
                            onMouseLeave={() => setIsLegalOpen(false)}
                        >
                            <button className={`flex items-center gap-1 font-semibold text-[0.85rem] ${textColor} hover:text-myRed transition-colors`}>
                                Legal <ChevronDown size={14} className={`transition-transform duration-300 ${isLegalOpen ? 'rotate-180' : ''}`} />
                            </button>
                            <AnimatePresence>
                                {isLegalOpen && (
                                    <motion.div
                                        initial={{ opacity: 0, y: 10, scale: 0.95 }}
                                        animate={{ opacity: 1, y: 0, scale: 1 }}
                                        exit={{ opacity: 0, y: 10, scale: 0.95 }}
                                        className={`absolute top-full right-0 mt-3 w-56 py-3 rounded-[1.5rem] border ${borderColor} ${navBg} shadow-2xl backdrop-blur-2xl z-[120]`}
                                    >
                                        <div className="px-2 space-y-1">
                                            <Link href="/privacy" className={`flex items-center justify-between px-4 py-3 text-[0.8rem] font-semibold rounded-xl hover:bg-myRed/10 hover:text-myRed transition-all ${textColor}`}>
                                                Privacy Policy <ArrowRight size={12} className="text-myRed" />
                                            </Link>
                                            <Link href="/terms" className={`flex items-center justify-between px-4 py-3 text-[0.8rem] font-semibold rounded-xl hover:bg-myRed/10 hover:text-myRed transition-all ${textColor}`}>
                                                Terms & Conditions <ArrowRight size={12} className="text-myRed" />
                                            </Link>
                                            <Link href="/faq" className={`flex items-center justify-between px-4 py-3 text-[0.8rem] font-semibold rounded-xl hover:bg-myRed/10 hover:text-myRed transition-all ${textColor}`}>
                                                Help & FAQ <ArrowRight size={12} className="text-myRed" />
                                            </Link>
                                        </div>
                                    </motion.div>
                                )}
                            </AnimatePresence>
                        </div>
                    </div>

                    <div className="flex items-center gap-4">
                        <div className="hidden md:flex items-center gap-4">
                            {user ? (
                                <>
                                    <span className={`text-[0.8rem] font-semibold ${subTextColor}`}>Hi, {user.name}</span>
                                    <button
                                        onClick={handleLogout}
                                        className={`text-[0.8rem] font-bold ${textColor} hover:text-myRed transition-colors px-4 py-2 border ${borderColor} rounded-full`}
                                    >
                                        Logout
                                    </button>
                                </>
                            ) : (
                                <Link
                                    href="/login"
                                    className={`bg-myRed text-white text-[0.8rem] font-bold py-2.5 px-6 rounded-full hover:bg-myRed/90 transition-all shadow-lg shadow-myRed/20`}
                                >
                                    Login
                                </Link>
                            )}
                            <Link href="/support" className="bg-white/5 border border-white/10 text-white/60 hover:text-white text-[0.8rem] font-bold py-2.5 px-6 rounded-full transition-all">
                                Support
                            </Link>
                        </div>

                        {/* Mobile Toggle Button */}
                        <button
                            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
                            className={`p-3 rounded-full md:hidden z-[110] transition-all active:scale-95 shadow-lg ${mobileBtnBg}`}
                        >
                            {isMobileMenuOpen ? <X size={20} /> : <Menu size={20} />}
                        </button>
                    </div>
                </motion.div>
            </div>

            <AnimatePresence>
                {isMobileMenuOpen && (
                    <motion.div
                        initial={{ clipPath: 'circle(0% at 92% 3rem)' }}
                        animate={{ clipPath: 'circle(150% at 92% 3rem)' }}
                        exit={{ clipPath: 'circle(0% at 92% 3rem)' }}
                        transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
                        className="fixed inset-0 z-[105] bg-myRed md:hidden flex flex-col items-center justify-center p-8 pointer-events-auto"
                    >
                        <div className="w-full max-w-sm flex flex-col h-full justify-between py-12">
                            {/* Mobile Links Container */}
                            <div className="flex flex-col space-y-3 mt-16 overflow-y-auto max-h-[70vh] py-4">
                                {menuItems.map((item, i) => (
                                    <motion.div
                                        key={item.name}
                                        initial={{ opacity: 0, y: 15 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        transition={{ delay: 0.1 * i + 0.3 }}
                                    >
                                        <Link
                                            href={item.href}
                                            onClick={() => setIsMobileMenuOpen(false)}
                                            className="text-[2.2rem] font-black tracking-tight text-white/90 hover:text-white transition-colors"
                                        >
                                            {item.name}<span className="text-black/10">.</span>
                                        </Link>
                                    </motion.div>
                                ))}

                                {/* Mobile Legal Dropdown Section */}
                                <motion.div
                                    initial={{ opacity: 0, y: 15 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ delay: 0.7 }}
                                    className="pt-2"
                                >
                                    <button
                                        onClick={() => setIsMobileLegalOpen(!isMobileLegalOpen)}
                                        className="flex items-center gap-3 text-[2.2rem] font-black tracking-tight text-white/90"
                                    >
                                        Legal <ChevronDown size={28} className={`transition-transform duration-300 ${isMobileLegalOpen ? 'rotate-180' : ''}`} />
                                    </button>

                                    <AnimatePresence>
                                        {isMobileLegalOpen && (
                                            <motion.div
                                                initial={{ height: 0, opacity: 0 }}
                                                animate={{ height: 'auto', opacity: 1 }}
                                                exit={{ height: 0, opacity: 0 }}
                                                className="overflow-hidden flex flex-col pl-4 mt-2 space-y-2 border-l-2 border-white/20"
                                            >
                                                <Link href="/privacy" onClick={() => setIsMobileMenuOpen(false)} className="text-lg font-bold text-white/70 py-1">Privacy Policy</Link>
                                                <Link href="/terms" onClick={() => setIsMobileMenuOpen(false)} className="text-lg font-bold text-white/70 py-1">Terms & Conditions</Link>
                                                <Link href="/faq" onClick={() => setIsMobileMenuOpen(false)} className="text-lg font-bold text-white/70 py-1">Help & FAQ</Link>
                                            </motion.div>
                                        )}
                                    </AnimatePresence>
                                </motion.div>
                            </div>

                            <motion.div
                                initial={{ opacity: 0 }}
                                animate={{ opacity: 1 }}
                                transition={{ delay: 0.8 }}
                                className="pt-8 border-t border-white/10"
                            >
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex space-x-6">
                                        <Twitter className="text-white/60" size={20} />
                                        <Instagram className="text-white/60" size={20} />
                                        <Facebook className="text-white/60" size={20} />
                                    </div>
                                    {user ? (
                                        <button
                                            onClick={handleLogout}
                                            className="text-white font-bold underline"
                                        >
                                            Logout
                                        </button>
                                    ) : (
                                        <Link href="/login" onClick={() => setIsMobileMenuOpen(false)} className="text-white font-bold underline">
                                            Login / Start
                                        </Link>
                                    )}
                                </div>
                                <p className="text-white/40 text-[0.6rem] font-bold uppercase tracking-[0.2em]">
                                    Enterprise-grade Romance.
                                </p>
                            </motion.div>
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>
        </nav>
    );
}
