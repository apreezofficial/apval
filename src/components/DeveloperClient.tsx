'use client';
import { motion } from 'framer-motion';
import Link from 'next/link';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';

export default function DeveloperClient() {
    return (
        <main className="min-h-screen bg-white text-black font-geist antialiased">
            <style jsx global>{`
                @import url('https://fonts.googleapis.com/css2?family=Onest:wght@300;400;500;600;700&display=swap');
                
                body {
                    font-family: 'Geist', 'Onest', sans-serif;
                    background-color: white !important;
                    color: black !important;
                }
            `}</style>

            <Navbar />

            <div className="mt-0">
                <header className="flex bg-[#fdeae6] items-center justify-center h-[21rem] md:h-screen md:max-h-[750px] lg:min-h-screen">
                    <div className="text-center">
                        <motion.div
                            initial="hidden"
                            animate="visible"
                            className="text-[1.5rem] md:text-[2.2rem] font-semibold tracking-[-1px] text-[#1a1a1a] flex justify-center flex-wrap"
                        >
                            {"The Developer.".split("").map((char, index) => (
                                <motion.span
                                    key={index}
                                    variants={{
                                        hidden: { opacity: 0, y: 10 },
                                        visible: {
                                            opacity: 1,
                                            y: 0,
                                            transition: {
                                                duration: 0.1,
                                                delay: index * 0.1,
                                                ease: "easeOut"
                                            }
                                        }
                                    }}
                                    className={char === " " ? "mr-2" : ""}
                                >
                                    {char}
                                </motion.span>
                            ))}
                        </motion.div>
                    </div>
                </header>

                <div className="flex justify-center mt-[3rem]">
                    <div className="p-2 my_fixed_width">
                        <motion.section
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.6 }}
                        >
                            <h4 className="text-[1rem] my-5 font-semibold">Quick Links</h4>
                            <div className="border-l-2 border-[#1a1a1a] flex flex-col pl-4 py-4 mb-9 text-[0.9rem] space-y-2">
                                <Link href="#"><span className="bg-[#1a1a1a] text-white px-3 inline-flex py-2 rounded-lg space-x-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="1.05rem" height="1.05rem" fill="white">
                                        <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                        <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                        <path d="M8 13H12" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                        <path d="M8 17H16" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                    </svg>
                                    <span>About Me</span>
                                </span></Link>
                                <Link href="/faq"><span className="ml-2 text-[0.8rem] inline-flex py-2 rounded-lg space-x-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="0.9rem" height="0.9rem" fill="black">
                                        <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                        <path d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                        <path d="M8 13H12" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                        <path d="M8 17H16" stroke="#292D32" strokeWidth="1.5" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round"></path>
                                    </svg>
                                    <span>Frequently Asked Questions</span>
                                </span></Link>
                            </div>
                        </motion.section>

                        <h2 className="text-[1.3rem] font-semibold">My Mission</h2>
                        <p className="text-[0.9rem] my-2 mb-11">I am on a mission to engineer high-performance, secure, and user-centric digital experiences. At Apval, I apply my expertise in backend architecture and secure infrastructures to ensure every expression of love is not only beautiful but robustly protected and effortlessly delivered.</p>

                        <h2 className="text-[1.3rem] font-semibold">My Story</h2>
                        <p className="text-[0.9rem] my-2 mb-11">As a Full Stack Developer, my journey has been defined by a deep fascination with how complex systems work under the hood. From crafting secure API designs to optimizing database integrity, I&#x27;ve dedicated my career to building infrastructures that empower users. Apval is the culmination of that passion—a fusion of technical precision and digital romance.</p>

                        <h2 className="text-[1.3rem] font-semibold">My Values</h2>
                        <p className="text-[0.9rem] my-2 mb-11">My work is guided by the principles of technical integrity, architectural efficiency, and relentless security. I believe that true innovation happens when security is baked into the design, allowing for seamless, trustworthy interactions that delight users without compromise.</p>

                        <h2 className="text-[1.3rem] font-semibold">The Vision</h2>
                        <p className="text-[0.9rem] my-2 mb-11">I envision Apval as the gold standard for secure digital gifting and romantic assets. By leveraging cutting-edge technology and secure design patterns, my goal is to provide a platform where creativity and security coexist, making every digital surprise a premium, unforgettable moment.</p>

                        <h2 className="text-[1.3rem] font-semibold">Get in Touch</h2>
                        <p className="text-[0.9rem] my-2 mb-11">I&#x27;m always open to discussing technical architecture, cybersecurity, or collaboration ideas. Whether you have feedback on Apval or just want to talk tech, feel free to <a href="https://preciousadedokun.com.ng/contact" target="_blank" rel="noopener noreferrer" className="underline">contact me</a>. Let&#x27;s build something secure and beautiful together.</p>
                    </div>
                </div>
            </div>

            <Footer />
        </main>
    );
}
