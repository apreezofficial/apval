
export interface EditorData {
    recipient: string;
    headline: string;
    message: string;
    sender: string;
    imageUrl: string;
    whatsapp: string;
    musicUrl: string;
    // Premium Motion Specific
    introQuote1Line1: string;
    introQuote1Line2: string;
    introQuote2Line1: string;
    introQuote2Line2: string;
    happyText: string;
    valentineText: string;
    buttonText: string;
    // Interactive Dodge Specific
    gender: 'male' | 'female' | string;
    introMessages: string[];
}

export type EditorStep =
    | 'recipient'
    | 'recipient_headline'
    | 'intro'
    | 'content'
    | 'attachment'
    | 'audio'
    | 'signature'
    | 'gender'
    | 'proposal_intro'
    | 'success';
