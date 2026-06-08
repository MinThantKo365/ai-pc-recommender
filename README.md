# AI PC Recommender

A Laravel web app that recommends PC and laptop hardware based on your budget, device type, and usage. Powered by **Google Gemini AI** with a rule-based fallback when the API is unavailable.

**Live repo:** [github.com/MinThantKo365/ai-pc-recommender](https://github.com/MinThantKo365/ai-pc-recommender)

---

## Requirements

- **PHP** 8.2 or higher
- **Composer**
- **Node.js** 18+ and **npm**
- **Git**

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/MinThantKo365/ai-pc-recommender.git
cd ai-pc-recommender
```

### 2. Install dependencies and set up the app

**Quick setup (recommended):**

```bash
composer setup
```

This command will:

- Install PHP dependencies
- Copy `.env.example` to `.env`
- Generate the application key
- Run database migrations
- Install npm packages
- Build frontend assets

**Manual setup:**

```bash
composer install
cp .env.example .env   # Windows: copy .env.example .env
php artisan key:generate
```

Create the SQLite database file:

```bash
# Linux / macOS
touch database/database.sqlite

# Windows (PowerShell)
New-Item -ItemType File -Force database/database.sqlite
```

Then run migrations and build assets:

```bash
php artisan migrate
npm install
npm run build
```

---

## Configuration

Open `.env` and set your Gemini API key (optional):

```env
GEMINI_API_KEY=your-gemini-api-key-here
GEMINI_MODEL=gemini-2.0-flash
```

| Variable | Description |
|----------|-------------|
| `GEMINI_API_KEY` | Your Google Gemini API key from [Google AI Studio](https://aistudio.google.com/) |
| `GEMINI_MODEL` | Gemini model to use (default: `gemini-2.0-flash`) |

If `GEMINI_API_KEY` is empty, the app uses a built-in rule-based recommendation engine instead.

> **Note:** Never commit your `.env` file or share your API key publicly.

---

## Running the application

**Production-style server:**

```bash
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000) in your browser.

**Development mode** (PHP server + Vite hot reload):

```bash
composer run dev
```

---

## Running tests

```bash
php artisan test
```

Or:

```bash
composer test
```

---

## Usage

1. Open the app in your browser.
2. Enter your **budget** (e.g. `$800`, `$1000-1500`).
3. Choose **Desktop PC** or **Laptop**.
4. Select one or more **primary usage** options (gaming, programming, etc.).
5. Add any **additional requirements** (optional).
6. Click **Get My Recommendation** to receive a detailed hardware suggestion.

---

## Tech stack

- [Laravel 12](https://laravel.com)
- [Tailwind CSS 4](https://tailwindcss.com)
- [Google Gemini API](https://ai.google.dev/)

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
