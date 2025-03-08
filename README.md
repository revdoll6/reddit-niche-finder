# Reddit Niche Finder

A web application that helps users discover and analyze niche subreddit communities. Built with Laravel 10 and Vue 3.

## Features

- User authentication with Laravel Sanctum
- Subreddit tracking and analysis
- Competitor activity monitoring
- Sentiment analysis of subreddit content
- Keyword-based community discovery
- Real-time analytics dashboard

## Requirements

- PHP 8.2+
- Node.js 16+
- MySQL 8.0+
- Redis (optional, for queue processing)
- Composer

## Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd reddit-niche-finder
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Create environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your database in `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=reddit_niche_finder
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

7. Run migrations and seed the database:
   ```bash
   php artisan migrate --seed
   ```

8. Configure Reddit API credentials in `.env`:
   ```env
   REDDIT_CLIENT_ID=your_client_id
   REDDIT_CLIENT_SECRET=your_client_secret
   REDDIT_USERNAME=your_username
   REDDIT_USER_AGENT="Reddit Niche Finder v1.0"
   ```

## Development

1. Start the Laravel development server:
   ```bash
   php artisan serve
   ```

2. Start the Vite development server:
   ```bash
   npm run dev
   ```

## Test Accounts

The seeder creates the following test accounts:

- Regular User:
  - Email: test@example.com
  - Password: password

- Admin User:
  - Email: admin@example.com
  - Password: admin123

- Marketing User:
  - Email: marketing@example.com
  - Password: marketing123

## License

[MIT License](LICENSE.md)
