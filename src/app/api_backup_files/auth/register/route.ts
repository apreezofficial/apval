import { NextResponse } from 'next/server';
import { saveUser, getUserByEmail } from '@/lib/db';
import { v4 as uuidv4 } from 'uuid';

export async function POST(request: Request) {
    try {
        const { email, password, name } = await request.json();

        const existingUser = await getUserByEmail(email);
        if (existingUser) {
            return NextResponse.json({ error: 'User already exists' }, { status: 400 });
        }

        const userId = uuidv4();
        await saveUser(userId, { id: userId, email, password, name });

        return NextResponse.json({ id: userId, name });
    } catch (error) {
        return NextResponse.json({ error: 'Registration failed' }, { status: 500 });
    }
}
