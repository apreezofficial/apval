import { NextResponse } from 'next/server';
import fs from 'fs';
import path from 'path';

const REQUESTS_FILE = path.join(process.cwd(), 'data', 'upgrade_requests.json');

export async function POST(req: Request) {
    try {
        const body = await req.json();
        const { requestId } = body;

        if (!requestId) {
            return NextResponse.json({ error: 'Missing request ID' }, { status: 400 });
        }

        if (fs.existsSync(REQUESTS_FILE)) {
            const data = fs.readFileSync(REQUESTS_FILE, 'utf-8');
            let requests = JSON.parse(data);

            // Filter out the rejected request
            requests = requests.filter((r: any) => r.id !== requestId);

            fs.writeFileSync(REQUESTS_FILE, JSON.stringify(requests, null, 2));
            return NextResponse.json({ success: true, message: 'Request rejected' });
        }

        return NextResponse.json({ error: 'Request not found' }, { status: 404 });
    } catch (error) {
        console.error('Reject error:', error);
        return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
    }
}
