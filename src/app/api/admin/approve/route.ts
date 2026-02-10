import { NextResponse } from 'next/server';
import fs from 'fs';
import path from 'path';

const USERS_FILE = path.join(process.cwd(), 'data', 'users.json');
const REQUESTS_FILE = path.join(process.cwd(), 'data', 'upgrade_requests.json');

export async function POST(req: Request) {
    try {
        const body = await req.json();
        const { requestId, userId } = body;

        if (!requestId || !userId) {
            return NextResponse.json({ error: 'Missing fields' }, { status: 400 });
        }

        // 1. Update User to Premium
        if (fs.existsSync(USERS_FILE)) {
            const usersData = fs.readFileSync(USERS_FILE, 'utf-8');
            const users = JSON.parse(usersData);

            if (users[userId]) {
                users[userId].subscriptionTier = 'premium';
                fs.writeFileSync(USERS_FILE, JSON.stringify(users, null, 2));
            } else {
                return NextResponse.json({ error: 'User not found' }, { status: 404 });
            }
        } else {
            return NextResponse.json({ error: 'Users database missing' }, { status: 500 });
        }

        // 2. Remove Request (or mark approved)
        if (fs.existsSync(REQUESTS_FILE)) {
            const requestsData = fs.readFileSync(REQUESTS_FILE, 'utf-8');
            let requests = JSON.parse(requestsData);

            // Filter out the approved request
            requests = requests.filter((req: any) => req.id !== requestId);
            // Alternatively, mark as approved but still clean list is better for a pending list

            fs.writeFileSync(REQUESTS_FILE, JSON.stringify(requests, null, 2));
        }

        return NextResponse.json({ success: true, message: 'User upgraded successfully' });
    } catch (error) {
        console.error('Approve error:', error);
        return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
    }
}
