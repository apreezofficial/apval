# Apval. `<3`
### Architecting the future of digital romance.

Apval is a kinetic deployment engine for elite Valentine experiences. Designed for high emotional impact and pixel-perfect transmission, it allows users to create cinematic Valentine pages from ready-made elite templates and share them instantly via unique links.

## ✨ Features

*   **Diverse Cinematic Templates**: Choose from a gallery of 8 distinct, premium templates like `Amour Cinematic` (fullscreen glassmorphic experience), `Minimal Elite` (obsidian-glass aesthetics), `Premium Motion Card` (cinematic intros with gold accents), `Gaming Elite` (cyberpunk-themed interactive proposals), `Interactive Proposal` (dodge-the-no game with confetti), `Classic Proposal` (viral "Yes/No" bears), `Quest Valentine` (playful questions), and `Premium Interactive Card` (multi-step animated story).
*   **Intuitive Multi-step Editor**: Personalize every aspect of your "love asset" including recipient, message, sender, attached images, and background music through a guided, modular editing experience.
*   **Rich Music Integration**: Search for favorite songs via the iTunes API, select from pre-defined mood tracks, or paste custom URLs (YouTube/Spotify embeds, direct audio files) to set the perfect ambiance.
*   **Instant & Secure Deployment**: Generate unique, shareable URLs for your creations in seconds, ready for distribution across any platform.
*   **Engaging Interactivity**: Templates feature dynamic elements like growing "Yes" buttons, "No" buttons that dodge the cursor, gender-themed UI, animated build-up messages, and confetti celebrations.
*   **User Authentication & Dashboard**: Securely register and log in to manage all your deployed love assets, with options to edit, delete, and easily share your creations.
*   **WhatsApp Integration**: Seamlessly connect with recipients or senders through dedicated WhatsApp share buttons embedded in the assets and dashboard.
*   **Premium Subscription System**: Access advanced features like custom URL slugs and increased asset limits by upgrading to a Premium tier via a Moniepoint-integrated payment flow.
*   **Admin Dashboard**: (For Project Maintainers) A dedicated interface at `/admin` to manage pending premium upgrade requests, including receipt verification and user tier activation.
*   **Responsive Design**: All cinematic experiences are optimized for seamless viewing across diverse devices, from mobile phones to widescreen desktops.

## 🛠 Stacks / Technologies

| Technology       | Description                                                                    | Link                                                            | Version    |
| :--------------- | :----------------------------------------------------------------------------- | :-------------------------------------------------------------- | :--------- |
| **Frontend**     | Next.js (App Router)                                                           | [nextjs.org](https://nextjs.org/)                               | `16.1.6`   |
| **Styling**      | Tailwind CSS                                                                   | [tailwindcss.com](https://tailwindcss.com/)                     | `^4`       |
| **Animation**    | Framer Motion                                                                  | [framer.com/motion](https://www.framer.com/motion/)             | `12.31.0`  |
| **Icons**        | Lucide React                                                                   | [lucide.dev](https://lucide.dev/)                               | `0.563.0`  |
| **API Client**   | Axios                                                                          | [axios-http.com](https://axios-http.com/)                       | `1.13.5`   |
| **UUID**         | UUID (for unique IDs)                                                          | [npmjs.com/package/uuid](https://www.npmjs.com/package/uuid)    | `13.0.0`   |
| **Confetti**     | Canvas Confetti                                                                | [github.com/catdad/canvas-confetti](https://github.com/catdad/canvas-confetti) | `1.9.4`    |
| **Data Storage** | Local JSON Files (for local development/API routes)                            | N/A                                                             | N/A        |
| **API Backend**  | Hosted Backend (for production API requests)                                   | `https://apval.pxxl.pro/api`                                    | N/A        |

## ⚙️ Installation

To set up the project locally for development:

1.  **Clone the Repository**:
    ```bash
    git clone https://github.com/apreezofficial/apval.git
    cd apval
    ```

2.  **Install Dependencies**:
    ```bash
    npm install
    # or yarn install
    ```

3.  **Set Up Environment Variables** (Optional, for API):
    The project is configured to use a hosted backend for API requests. For local API development, Next.js API routes will proxy. If you need to override the API URL, create a `.env.local` file and add:
    ```
    NEXT_PUBLIC_APP_URL="http://localhost:3000"
    # If running a separate backend, you might use:
    # Internal_API_URL="http://localhost:port/api"
    ```

4.  **Launch Development Server**:
    ```bash
    npm run dev
    # or yarn dev
    ```
    The application will be accessible at `http://localhost:3000`.

## 🚀 Usage

### Creating & Sharing a Love Asset

1.  **Access the Platform**: Open your browser and navigate to `http://localhost:3000`.
2.  **Register or Log In**: Create a new account or sign in to your existing one.
3.  **Browse Templates**: Visit the `/templates` page or click "Create New" from your dashboard to explore the diverse range of cinematic templates available.
4.  **Select a Template**: Click on any template card to launch the multi-step editor.
5.  **Personalize Your Asset**: Follow the intuitive steps to customize your creation:
    *   Enter recipient's name and a catchy headline.
    *   Craft your heartfelt message.
    *   Upload an image to attach a memory.
    *   Select background music (search iTunes, pick a mood, or paste a custom URL).
    *   Add your sender name and WhatsApp number.
    *   (Premium Feature) Create a custom URL slug for an easily memorable link.
6.  **Deploy & Share**: Once finalized, click "Deploy Asset" to generate a unique, secure URL. You can then copy this link and share it directly with your loved one via WhatsApp, SMS, or social media.

### Managing Your Creations (Dashboard)

*   Navigate to `/dashboard` to view, edit, or delete your deployed love assets.
*   Easily share any existing asset by clicking the share icon.

## 🤝 Contributing

Contributions are welcome! If you're looking to contribute to the codebase:

1.  **Fork the Repository** and clone it locally.
2.  **Create a New Branch** for your feature or bug fix.
3.  **Familiarize Yourself with the Architecture**: The project uses a modular editor architecture. Refer to `EDITOR_ARCHITECTURE.md` and `RESTORATION_SUMMARY.md` in the `docs` folder for a detailed understanding of the component structure.
4.  **Commit Your Changes** with clear and descriptive messages.
5.  **Push to Your Fork** and open a Pull Request.

Please ensure your code adheres to the existing styling and best practices.

## 📄 License

This project is distributed under the MIT License. See the `LICENSE` file for more information.

---
**Engineered with precision by [Precious Adedokun](https://preciousadedokun.com.ng)**
[![Readme was generated by Readmit](https://img.shields.io/badge/Readme%20was%20generated%20by-Readmit-brightred)](https://readmit.vercel.app)
