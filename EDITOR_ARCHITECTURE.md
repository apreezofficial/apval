# 🎨 Multi-Step Editor - Modular Architecture

## Overview
The Multi-Step Editor has been refactored into a **modular, component-based architecture** where each template has its own dedicated editor configuration and all step components are reusable across templates.

## 📁 New Structure

```
src/components/editor/
├── types.ts                          # Shared TypeScript interfaces
├── EditorHeader.tsx                  # Shared header component
├── EditorPreview.tsx                 # Shared preview wrapper
├── steps/                            # Reusable step components
│   ├── RecipientStep.tsx            # Recipient & headline input
│   ├── ContentStep.tsx              # Message content input
│   ├── AttachmentStep.tsx           # Image upload
│   ├── MusicStep.tsx                # Music search & selection
│   ├── SignatureStep.tsx            # Sender info & WhatsApp
│   ├── GenderStep.tsx               # Gender/vibe selection
│   ├── IntroMessagesStep.tsx        # Build-up messages
│   ├── IntroSequenceStep.tsx        # Cinematic quotes
│   └── SuccessStep.tsx              # Deployment success screen
└── templates/                        # Template-specific editors
    ├── AmourEditor.tsx              # Amour Cinematic
    ├── MinimalEliteEditor.tsx       # Minimal Elite
    ├── PremiumMotionEditor.tsx      # Premium Motion Card
    ├── QuestValentineEditor.tsx     # Quest Valentine
    ├── InteractiveDodgeEditor.tsx   # Interactive Proposal
    ├── ClassicValentineEditor.tsx   # Classic Proposal
    └── PremiumMockupEditor.tsx      # Premium Interactive Card
```

## 🎯 Key Benefits

### 1. **Customizable Text & Labels**
Every step component accepts props for customizing:
- **Titles** - Main heading text
- **Descriptions** - Subtitle/helper text
- **Placeholders** - Input field hints
- **Button text** - Action labels

**Example:**
```tsx
<RecipientStep 
    data={data} 
    onUpdate={onUpdate} 
    onNext={onNext} 
    showHeadline={false}
    title="Who's the Lucky One?"
    description="Who will you ask?"
/>
```

### 2. **Reusable Components**
All step components are **template-agnostic** and can be used across different templates:
- `RecipientStep` - Used in 6 templates
- `MusicStep` - Used in 6 templates
- `SignatureStep` - Used in all templates
- `ContentStep` - Used in 5 templates

### 3. **Template-Specific Editors**
Each template has its own editor file that:
- Defines the step sequence
- Configures step-specific options
- Customizes text and labels
- Controls which fields are shown

## 📝 Template Configurations

### Amour Cinematic
**Steps:** Recipient & Headline → Content → Attachment → Music → Signature
```tsx
// src/components/editor/templates/AmourEditor.tsx
- Shows headline input
- Requires WhatsApp number
- Includes image upload
```

### Minimal Elite
**Steps:** Recipient & Headline → Content → Signature
```tsx
// src/components/editor/templates/MinimalEliteEditor.tsx
- Shows headline input
- NO WhatsApp required
- NO image or music
```

### Premium Motion Card
**Steps:** Recipient & Headline → Intro Sequence → Content → Attachment → Music → Signature
```tsx
// src/components/editor/templates/PremiumMotionEditor.tsx
- Shows headline input
- Includes cinematic intro quotes
- Shows extra fields (Happy Text, Valentine Text)
- Includes button text customization
```

### Quest Valentine
**Steps:** Recipient → Content → Music → Signature
```tsx
// src/components/editor/templates/QuestValentineEditor.tsx
- NO headline input
- Custom title: "Who is the Quest for?"
- Custom content title: "The Quest Content"
```

### Interactive Proposal
**Steps:** Recipient → Gender → Build-Up Messages → Music → Signature
```tsx
// src/components/editor/templates/InteractiveDodgeEditor.tsx
- NO headline input
- Custom title: "Who's the Lucky One?"
- Includes gender/vibe selection
- 4 cinematic intro messages
```

### Classic Proposal
**Steps:** Recipient → Content → Music → Signature
```tsx
// src/components/editor/templates/ClassicValentineEditor.tsx
- NO headline input
- Custom title: "Who's the Special One?"
- Custom content title: "The Question"
```

### Premium Interactive Card (Default)
**Steps:** Recipient → Content → Attachment → Music → Signature
```tsx
// src/components/editor/templates/PremiumMockupEditor.tsx
- NO headline input
- Standard flow for default template
```

## 🔧 How to Customize

### Adding a New Template
1. Create a new editor file in `src/components/editor/templates/`
2. Import the step components you need
3. Define the step sequence in a switch statement
4. Customize text, labels, and options for each step

**Example:**
```tsx
import { EditorData, EditorStep } from '../types';
import RecipientStep from '../steps/RecipientStep';
import ContentStep from '../steps/ContentStep';
import SignatureStep from '../steps/SignatureStep';

export default function MyNewEditor({ data, step, features, onUpdate, onNext, onBack, onFinish, loading }) {
    const currentFeature = features[step - 1];

    switch (currentFeature) {
        case 'recipient':
            return <RecipientStep 
                data={data} 
                onUpdate={onUpdate} 
                onNext={onNext} 
                showHeadline={false}
                title="Custom Title Here"
                description="Custom description here"
            />;
        case 'content':
            return <ContentStep 
                data={data} 
                onUpdate={onUpdate} 
                onNext={onNext} 
                onBack={onBack}
                title="Your Message"
                description="Write something special"
            />;
        case 'signature':
            return <SignatureStep 
                data={data} 
                onUpdate={onUpdate} 
                onFinish={onFinish} 
                onBack={onBack} 
                loading={loading}
                showWhatsapp={true}
            />;
        default:
            return null;
    }
}
```

### Customizing Step Text
Each step component accepts these customization props:

**RecipientStep:**
- `title` - Main heading
- `description` - Subtitle
- `showHeadline` - Show/hide headline input

**ContentStep:**
- `title` - Main heading
- `description` - Subtitle
- `showExtraFields` - Show Happy/Valentine text inputs

**SignatureStep:**
- `showButtonText` - Show button text input
- `showWhatsapp` - Show WhatsApp input

**MusicStep:**
- All text is built-in but can be extended

## 🎨 Music Step Features

The `MusicStep` component includes:
- **iTunes Search API** integration for song search
- **Pre-defined mood tracks** (Romantic Piano, Lofi Love, etc.)
- **Manual URL override** for custom links
- **Real-time search** with loading states
- **Album artwork** display for search results

## ✅ All Templates Restored

All templates are now fully functional:
- ✅ Premium Interactive Card
- ✅ Amour Cinematic
- ✅ Minimal Elite
- ✅ Premium Motion Card
- ✅ Quest Valentine
- ✅ Interactive Proposal
- ✅ Classic Proposal

## 🚀 Next Steps

To add more customization:
1. Add new props to step components
2. Update template editors to use new props
3. Modify step component JSX to use prop values
4. Test across all templates

**Example - Adding custom button text to ContentStep:**
```tsx
// In ContentStep.tsx
interface ContentStepProps {
    // ... existing props
    buttonText?: string;
}

// In JSX
<span>{buttonText || 'Continue'}</span>

// In template editor
<ContentStep 
    buttonText="Next Step →"
    // ... other props
/>
```
