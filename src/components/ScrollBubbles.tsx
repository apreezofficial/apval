"use client";

import React, { useEffect, useState, useCallback } from "react";
import { motion, AnimatePresence } from "framer-motion";

interface Bubble {
    id: number;
    x: number;
    y: number;
    size: number;
    color: string;
}

const ScrollBubbles = () => {
    const [bubbles, setBubbles] = useState<Bubble[]>([]);

    const spawnBubble = useCallback(() => {
        const id = Date.now() + Math.random();
        // Spawn near the right edge (scrollbar area)
        const x = window.innerWidth - (Math.random() * 40 + 10);
        const y = window.scrollY + (Math.random() * window.innerHeight);
        const size = Math.random() * 15 + 5;
        const colors = ["#fc4100", "#ff4d8d", "#ffffff", "#ff8c66"];
        const color = colors[Math.floor(Math.random() * colors.length)];

        setBubbles((prev) => [...prev, { id, x, y, size, color }].slice(-20)); // Keep max 20

        setTimeout(() => {
            setBubbles((prev) => prev.filter((b) => b.id !== id));
        }, 1000);
    }, []);

    useEffect(() => {
        let lastScrollY = window.scrollY;
        const handleScroll = () => {
            const currentScrollY = window.scrollY;
            if (Math.abs(currentScrollY - lastScrollY) > 5) {
                spawnBubble();
                lastScrollY = currentScrollY;
            }
        };

        window.addEventListener("scroll", handleScroll, { passive: true });
        return () => window.removeEventListener("scroll", handleScroll);
    }, [spawnBubble]);

    return (
        <div className="fixed inset-0 pointer-events-none z-[100] overflow-hidden">
            <AnimatePresence>
                {bubbles.map((bubble) => (
                    <motion.div
                        key={bubble.id}
                        initial={{ opacity: 0, scale: 0, x: bubble.x, y: bubble.y - window.scrollY }}
                        animate={{
                            opacity: [0, 0.8, 0],
                            scale: [0.5, 1.5, 0.8],
                            y: bubble.y - window.scrollY - 100,
                            x: bubble.x - (Math.random() * 50 - 25)
                        }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 1, ease: "easeOut" }}
                        className="absolute rounded-full blur-[1px]"
                        style={{
                            width: bubble.size,
                            height: bubble.size,
                            backgroundColor: bubble.color,
                            boxShadow: `0 0 10px ${bubble.color}`,
                        }}
                    />
                ))}
            </AnimatePresence>
        </div>
    );
};

export default ScrollBubbles;
