# Premium Subscription System Implementation

## ✅ COMPLETED FILES

### 1. PremiumModal Component (`src/components/PremiumModal.tsx`)
- **Monnify-style payment modal**
- Shows tier comparison (Free vs Premium)
- Payment screen with account details
- Copy-to-clipboard for account number
- WhatsApp integration for proof of payment

### 2. Subscription Utilities (`src/lib/subscription.ts`)
- Subscription tier type definitions
- Limit constants:
  - Free: 3 valcards, no custom slugs
  - Premium: 10 valcards, custom slugs enabled
- Helper functions:
  - `getUserSubscriptionTier()`
  - `getUserLimits()`
  - `canCreateValentine()`
  - `canUseCustomSlug()`

### 3. Custom Slug Step (`src/components/editor/steps/CustomSlugStep.tsx`)
- Premium-only feature
- Shows upgrade prompt for free users
- URL-friendly slug input for premium users
- Preview URL display
- Skip option (uses random ID)

### 4. Updated Types (`src/components/editor/types.ts`)
- Added `customSlug?: string` to EditorData
- Added `'custom_slug'` to EditorStep type

## 🔧 REQUIRED INTEGRATIONS

### Next Steps for Full Implementation:

#### 1. Update DashboardClient.tsx
Add at the top of the component:
```typescript
import PremiumModal from '@/components/PremiumModal';
import { getUserLimits, canCreateValentine, canUseCustomSlug } from '@/lib/subscription';
import { Crown } from 'lucide-react';

// Inside component:
const [showPremiumModal, setShowPremiumModal] = useState(false);
const [premiumReason, setPremiumReason] = useState<'limit' | 'slug' | 'upgrade'>('upgrade');

const userLimits = getUserLimits(user);
const valentinesCount = valentines.length;
const canCreate = canCreateValentine(user, valentinesCount);
```

Add a premium status badge in the dashboard header:
```typescript
<div className="flex items-center gap-2 bg-gradient-to-r from-yellow-500 to-orange-600 text-white px-4 py-2 rounded-full">
    <Crown size={16} />
    <span className="text-sm font-bold">
        {user?.subscriptionTier === 'premium' ? 'Premium' : 'Free'} - {valentinesCount}/{userLimits.maxValentines} Cards
    </span>
</div>
```

Update the "Create New" button:
```typescript
onClick={() => {
    if (!canCreate) {
        setPremiumReason('limit');
        setShowPremiumModal(true);
    } else {
        setIsEditorOpen(true);
    }
}}
```

Add modal at the end of return:
```typescript
<PremiumModal 
    isOpen={showPremiumModal}
    onClose={() => setShowPremiumModal(false)}
    reason={premiumReason}
/>
```

#### 2. Update MultiStepEditor.tsx
Import and add custom_slug step to the feature map:
```typescript
import CustomSlugStep from './editor/steps/CustomSlugStep';
import { canUseCustomSlug } from '@/lib/subscription';
import PremiumModal from './PremiumModal';

// Add to state:
const [showPremiumModal, setShowPremiumModal] = useState(false);
const [user, setUser] = useState<any>(null);

// Load user in useEffect:
useEffect(() => {
    const storedUser = localStorage.getItem('user');
    if (storedUser) {
        setUser(JSON.parse(storedUser));
    }
}, []);

// Update featureMap to include custom_slug step (OPTIONAL, before signature):
const featureMap: Record<string, string[]> = {
    'valentine-motion-premium': ['recipient_headline', 'intro', 'content', 'attachment', 'audio', 'custom_slug', 'signature'],
    // ... add to others as desired
};

// In renderStep function, add case:
case 'custom_slug':
    return (
        <CustomSlugStep
            data={formData}
            onUpdate={updateField}
            onNext={nextStep}
            isPremium={user?.subscriptionTier === 'premium'}
            onUpgrade={() => setShowPremiumModal(true)}
        />
    );

// Add modal before closing tag:
<PremiumModal 
    isOpen={showPremiumModal}
    onClose={() => setShowPremiumModal(false)}
    reason="slug"
/>
```

#### 3. Update API Routes

**File: `src/app/api/valentines/route.ts`** (or wherever valentine creation happens)

Add validation:
```typescript
import { canCreateValentine } from '@/lib/subscription';

// In POST handler:
const userId = req.body.userId;
const customSlug = req.body.customSlug;

// Load user from users.json
const user = getUserFromDatabase(userId);

// Check creation limit
const userValentines = getValentinesByUserId(userId);
if (!canCreateValentine(user, userValentines.length)) {
    return Response.json(
        { error: 'Valentine creation limit reached. Upgrade to Premium for more!' },
        { status: 403 }
    );
}

// Check custom slug permission
if (customSlug && !canUseCustomSlug(user)) {
    return Response.json(
        { error: 'Custom slugs are a Premium feature' },
        { status: 403 }
    );
}

// Check slug uniqueness
if (customSlug && isSlugTaken(customSlug)) {
    return Response.json(
        { error: 'This slug is already taken' },
        { status: 409 }
    );
}
```

#### 4. Update users.json Structure

Add to existing users:
```json
{
  "76f63e52-4e1e-4b7c-aa03-a6d1dfc8cdd9": {
    "id": "76f63e52-4e1e-4b7c-aa03-a6d1dfc8cdd9",
    "email": "aa@aa.aa",
    "password": "aaaaaa01",
    "name": "aa",
    "createdAt": "2026-02-04T18:50:35.521Z",
    "subscriptionTier": "free",
    "valentinesCount": 0
  }
}
```

#### 5. Support Custom Slug Routing

**File: `src/app/v/[id]/page.tsx`**

Update to support both UUIDs and custom slugs:
```typescript
// The existing code should work, but ensure it looks up by both ID and customSlug
// In the API endpoint that fetches valentines, check both fields
```

## 💳 Payment Flow

1. User hits limit or tries to use custom slug
2. PremiumModal appears with:
   - Tier comparison
   - ₦1,000 pricing
3. User clicks "Upgrade Now"
4. Payment screen shows:
   - Bank: Moniepoint
   - Account: 9064779856
   - Name: Precious Adedokun
   - Amount: ₦1,000
5. User transfers money
6. User clicks "Send Proof" → Opens WhatsApp
7. **Manual activation**: You verify payment and update their user record:
   ```json
   "subscriptionTier": "premium"
   ```

## 📝 To-Do Checklist

- [ ] Integrate PremiumModal into DashboardClient
- [ ] Add premium badge to dashboard
- [ ] Update "Create New" button with limit check
- [ ] Integrate CustomSlugStep into MultiStepEditor
- [ ] Add custom_slug to feature maps (optional step)
- [ ] Update valentine creation API with limit validation
- [ ] Add slug uniqueness check
- [ ] Update users.json with subscription fields
- [ ] Test full flow: free limit → upgrade → custom slug creation
- [ ] Add admin panel to manually upgrade users (future)

## 🎯 Key Features

✅ Free tier: 3 valcards, random URLs
✅ Premium tier: ₦1,000 for 10 valcards + custom URLs
✅ Beautiful Monnify-style payment modal
✅ Copy-to-clipboard for easy payment
✅ WhatsApp integration for proof submission
✅ Graceful upgrade prompts throughout the app
