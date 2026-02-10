import { NextResponse } from 'next/server';
import fs from 'fs';
import path from 'path';

const REQUESTS_FILE = path.join(process.cwd(), 'data', 'upgrade_requests.json');

export async function GET() {
    try {
        if (fs.existsSync(REQUESTS_FILE)) {
            const data = fs.readFileSync(REQUESTS_FILE, 'utf-8');
            const requests = JSON.parse(data);
            return NextResponse.json(requests);
        }
        return NextResponse.json([]);
    } catch (error) {
        return NextResponse.json({ error: 'Failed to fetch requests' }, { status: 500 });
    }
}
