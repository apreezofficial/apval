// User subscription tiers
export type SubscriptionTier = 'free' | 'premium';

export interface User {
    id: string;
    email: string;
    password: string;
    name: string;
    createdAt: string;
    subscriptionTier?: SubscriptionTier;
    valentinesCount?: number;
}

export interface SubscriptionLimits {
    maxValentines: number;
    customSlug: boolean;
}

export const SUBSCRIPTION_LIMITS: Record<SubscriptionTier, SubscriptionLimits> = {
    free: {
        maxValentines: 3,
        customSlug: false,
    },
    premium: {
        maxValentines: 10,
        customSlug: true,
    },
};

export function getUserSubscriptionTier(user: User | null): SubscriptionTier {
    return user?.subscriptionTier || 'free';
}

export function getUserLimits(user: User | null): SubscriptionLimits {
    const tier = getUserSubscriptionTier(user);
    return SUBSCRIPTION_LIMITS[tier];
}

export function canCreateValentine(user: User | null, currentCount: number): boolean {
    const limits = getUserLimits(user);
    return currentCount < limits.maxValentines;
}

export function canUseCustomSlug(user: User | null): boolean {
    const limits = getUserLimits(user);
    return limits.customSlug;
}
