import { NextResponse } from 'next/server';
import fs from 'fs';
import path from 'path';

const REQUESTS_FILE = path.join(process.cwd(), 'data', 'upgrade_requests.json');

export async function POST(req: Request) {
    try {
        const body = await req.json();
        const { userId, userEmail, receipt, timestamp } = body;

        if (!userId || !userEmail || !receipt) {
            return NextResponse.json({ error: 'Missing fields' }, { status: 400 });
        }

        let requests = [];
        if (fs.existsSync(REQUESTS_FILE)) {
            const data = fs.readFileSync(REQUESTS_FILE, 'utf-8');
            try {
                requests = JSON.parse(data);
            } catch (e) {
                requests = [];
            }
        }

        const newRequest = {
            id: crypto.randomUUID(),
            userId,
            userEmail,
            receipt, // Base64 string
            timestamp,
            status: 'pending'
        };

        requests.push(newRequest);
        fs.writeFileSync(REQUESTS_FILE, JSON.stringify(requests, null, 2));

        return NextResponse.json({ success: true, message: 'Request submitted' });
    } catch (error) {
        console.error('Upgrade request error:', error);
        return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
    }
}
