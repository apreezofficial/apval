"use client";

import React, { useMemo } from "react";
import { motion } from "framer-motion";
import {
    Skull,
    Radio,
    Disc,
    Pizza,
    Glasses,
    Coffee,
    Ghost,
    Laptop,
    Pencil,
    Gamepad2,
    Headphones,
    Music,
    Camera,
    Star,
    Zap,
    Cookie,
    Heart,
    Smile,
    Rocket,
    Moon,
    Sun,
    Flame,
    Cloud,
    Mouse,
    Monitor,
    Utensils,
    Beer,
    Tent,
    CloudLightning,
    HeartPulse,
    Brain,
    Cat,
    Dog,
    Gamepad,
    Sparkles,
    Gem,
    Gift,
    Gamepad as GamepadIcon
} from "lucide-react";

const ICON_LIST = [
    Skull,
    Radio,
    Disc,
    Pizza,
    Glasses,
    Coffee,
    Ghost,
    Laptop,
    Pencil,
    Gamepad2,
    Headphones,
    Music,
    Camera,
    Star,
    Zap,
    Cookie,
    Heart,
    Smile,
    Rocket,
    Moon,
    Sun,
    Flame,
    Cloud,
    Mouse,
    Monitor,
    Utensils,
    Beer,
    Tent,
    CloudLightning,
    HeartPulse,
    Brain,
    Cat,
    Dog,
    Gamepad,
    Sparkles,
    Gem,
    Gift
];

const FloatingIcon = ({ Icon, index }: { Icon: any; index: number }) => {
    const randomValues = useMemo(() => ({
        top: Math.random() * 100,
        left: Math.random() * 100,
        size: Math.random() * 24 + 20, // 20px to 44px
        duration: Math.random() * 20 + 20, // 20s to 40s
        delay: Math.random() * 10,
        rotation: Math.random() * 360,
        scale: Math.random() * 0.4 + 0.6, // 0.6 to 1.0
        moveX: Math.random() * 200 - 100, // -100 to 100
        moveY: Math.random() * 200 - 100, // -100 to 100
    }), []);

    const isRed = index % 5 === 0;

    return (
        <motion.div
            initial={{
                top: `${randomValues.top}%`,
                left: `${randomValues.left}%`,
                opacity: 0,
                rotate: randomValues.rotation,
                scale: randomValues.scale
            }}
            animate={{
                y: [0, randomValues.moveY, 0],
                x: [0, randomValues.moveX, 0],
                rotate: [randomValues.rotation, randomValues.rotation + 180, randomValues.rotation + 360],
                opacity: [0.05, 0.2, 0.05],
            }}
            transition={{
                duration: randomValues.duration,
                repeat: Infinity,
                ease: "linear",
                delay: randomValues.delay,
            }}
            className="fixed pointer-events-none z-50"
            style={{
                width: randomValues.size,
                height: randomValues.size,
                color: isRed ? "#fc4100" : "white",
                filter: `drop-shadow(0 0 ${isRed ? '8px rgba(252,65,0,0.5)' : '5px rgba(255,255,255,0.2)'})`
            }}
        >
            <Icon size={randomValues.size} strokeWidth={1} />
        </motion.div>
    );
};

const Waves = () => {
    return (
        <div className="fixed inset-0 pointer-events-none z-0 overflow-hidden opacity-20">
            <div className="absolute bottom-[-50px] left-0 w-full h-[30vh]">
                <svg
                    className="w-full h-full fill-current text-white/5"
                    viewBox="0 0 1440 320"
                    preserveAspectRatio="none"
                >
                    <motion.path
                        animate={{
                            d: [
                                "M0,160L48,176C96,192,192,224,288,213.3C384,203,480,149,576,144C672,139,768,181,864,181.3C960,181,1056,139,1152,122.7C1248,107,1344,117,1392,122.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z",
                                "M0,224L48,213.3C96,203,192,181,288,186.7C384,192,480,224,576,218.7C672,213,768,171,864,149.3C960,128,1056,128,1152,144C1248,160,1344,192,1392,208L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z",
                                "M0,160L48,176C96,192,192,224,288,213.3C384,203,480,149,576,144C672,139,768,181,864,181.3C960,181,1056,139,1152,122.7C1248,107,1344,117,1392,122.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                            ]
                        }}
                        transition={{
                            duration: 20,
                            repeat: Infinity,
                            ease: "easeInOut"
                        }}
                    />
                </svg>
            </div>
            <div className="absolute bottom-[-30px] left-0 w-full h-[25vh] opacity-30">
                <svg
                    className="w-full h-full fill-current text-white/5"
                    viewBox="0 0 1440 320"
                    preserveAspectRatio="none"
                >
                    <motion.path
                        animate={{
                            d: [
                                "M0,96L48,112C96,128,192,160,288,144C384,128,480,64,576,80C672,96,768,192,864,224C960,256,1056,224,1152,197.3C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z",
                                "M0,192L48,170.7C96,149,192,107,288,96C384,85,480,107,576,144C672,181,768,235,864,245.3C960,256,1056,224,1152,192C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z",
                                "M0,96L48,112C96,128,192,160,288,144C384,128,480,64,576,80C672,96,768,192,864,224C960,256,1056,224,1152,197.3C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                            ]
                        }}
                        transition={{
                            duration: 25,
                            repeat: Infinity,
                            ease: "linear",
                            delay: 2
                        }}
                    />
                </svg>
            </div>
        </div>
    );
};

const BackgroundEffects = () => {
    // Generate a set of 40 icons
    const iconsToRender = useMemo(() => {
        return Array.from({ length: 7 }).map((_, i) => ({
            Icon: ICON_LIST[i % ICON_LIST.length],
            id: i
        }));
    }, []);

    return (
        <div className="fixed inset-0 pointer-events-none z-50 overflow-hidden select-none">
            <div className="absolute inset-0 bg-gradient-to-b from-transparent via-[#fc4100]/3 to-transparent pointer-events-none" />

            {iconsToRender.map((item) => (
                <FloatingIcon key={item.id} Icon={item.Icon} index={item.id} />
            ))}

            <Waves />
        </div>
    );
};

export default BackgroundEffects;
