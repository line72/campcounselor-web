# Camp Counselor - Laravel Implementation

A Laravel web application for managing your Bandcamp album collection and wishlist. This is a PHP/Laravel port of the original Vala/GTK Camp Counselor application, providing the same functionality through a modern web interface.

**Single-User Design**: This application is designed for personal use without authentication - perfect for managing your own music collection locally or on a private server.

## Features

### Core Functionality
- **Album Management**: Store and organize your Bandcamp collection and wishlist
- **Rating System**: Rate albums from 1-5 stars with visual star display
- **Comments**: Add personal notes and comments to albums
- **Search**: Search albums by artist name or album title
- **Filtering**: Filter by purchased albums, wishlist items, or view all
- **Sorting**: Sort by artist name, rating, created date, or updated date (ascending/descending)

### Bandcamp Integration
- **Collection Sync**: Fetch your purchased albums from Bandcamp
- **Wishlist Sync**: Import your Bandcamp wishlist
- **Track Preview**: Parse and display track listings from album pages
- **Automatic Updates**: Periodic refresh of collection data
- **Fan ID Resolution**: Convert Bandcamp usernames to fan IDs

### Web Interface
- **Responsive Design**: Works on desktop and mobile devices
- **Grid Layout**: Beautiful album cover grid with hover effects
- **Modal Dialogs**: Edit ratings and comments in overlay modals
- **Real-time Search**: Instant search results as you type
- **Statistics Dashboard**: View collection statistics via API

## Installation

### Option 1: Docker (Recommended)

## Building and Running with Docker

### Build the Docker Image
```bash
# Clone the repository
git clone <repository-url>
cd campcounselor/php

# Build the Docker image
docker build -t campcounselor .
```

### Database Setup
**Important**: You must run database migrations manually after starting the container.

```bash
# For SQLite (default)
docker exec campcounselor php artisan migrate

# For PostgreSQL/MariaDB
docker exec campcounselor php artisan migrate
```

### Running the Container

#### SQLite (Default - Simplest)
```bash
# Run with persistent SQLite database
docker run -d -p 8080:80 \
  -v campcounselor_data:/var/www/html/database \
  -v campcounselor_storage:/var/www/html/storage \
  --name campcounselor \
  campcounselor

# Run migrations
docker exec campcounselor php artisan migrate
```

#### PostgreSQL
```bash
# Run with PostgreSQL
docker run -d -p 8080:80 \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=your-postgres-host \
  -e DB_PORT=5432 \
  -e DB_DATABASE=campcounselor \
  -e DB_USERNAME=your-username \
  -e DB_PASSWORD=your-password \
  -v campcounselor_storage:/var/www/html/storage \
  --name campcounselor \
  campcounselor

# Run migrations
docker exec campcounselor php artisan migrate
```

#### MariaDB/MySQL
```bash
# Run with MariaDB/MySQL
docker run -d -p 8080:80 \
  -e DB_CONNECTION=mysql \
  -e DB_HOST=your-mariadb-host \
  -e DB_PORT=3306 \
  -e DB_DATABASE=campcounselor \
  -e DB_USERNAME=your-username \
  -e DB_PASSWORD=your-password \
  -v campcounselor_storage:/var/www/html/storage \
  --name campcounselor \
  campcounselor

# Run migrations
docker exec campcounselor php artisan migrate
```

#### Behind Reverse Proxy (Nginx with TLS)
```bash
# Run behind reverse proxy with HTTPS termination
docker run -d -p 8080:80 \
  -e APP_URL=https://your-domain.com \
  -e APP_ENV=production \
  -v campcounselor_data:/var/www/html/database \
  -v campcounselor_storage:/var/www/html/storage \
  --name campcounselor \
  campcounselor

# Run migrations
docker exec campcounselor php artisan migrate
```

### Docker Compose (Alternative)
```bash
# Start with Docker Compose
docker-compose up -d

# Run migrations
docker-compose exec campcounselor php artisan migrate

# Access the application
open http://localhost:8080
```

### Environment Variables
The Docker container accepts these environment variables:
- `APP_KEY` - Application encryption key (auto-generated if not provided)
- `APP_ENV` - Application environment (default: production)
- `APP_DEBUG` - Debug mode (default: false)
- `APP_URL` - Application URL (default: http://localhost)
- `APP_TIMEZONE` - Application timezone (default: UTC)
- `DB_CONNECTION` - Database type: `sqlite`, `pgsql`, `mysql` (default: sqlite)
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` - Database credentials
- `LOG_CHANNEL` - Logging channel (default: single)
- `LOG_LEVEL` - Log level (default: info)

**Note**: `APP_NAME` is hardcoded as "Camp Counselor" and cannot be overridden.

### Reverse Proxy Configuration
The Docker container is configured to work behind reverse proxies with TLS termination:
- Trusts `X-Forwarded-Proto` headers for HTTPS detection
- Trusts `X-Forwarded-For` headers for client IP detection
- Set `APP_URL` to your public HTTPS URL when behind a proxy

### Docker Troubleshooting

#### Container Management
```bash
# View container logs
docker logs campcounselor

# Follow logs in real-time
docker logs -f campcounselor

# Access container shell
docker exec -it campcounselor bash

# Stop and remove container
docker stop campcounselor && docker rm campcounselor

# Rebuild image (after code changes)
docker build -t campcounselor . --no-cache
```

#### Viewing PHP Errors
```bash
# Method 1: Container logs (includes all output)
docker logs -f campcounselor

# Method 2: Apache error logs
docker exec -it campcounselor tail -f /var/log/apache2/campcounselor_error.log

# Method 3: PHP error log
docker exec -it campcounselor tail -f /var/log/apache2/php_errors.log

# Method 4: Laravel application logs
docker exec -it campcounselor tail -f /var/www/html/storage/logs/laravel.log

# Method 5: Enable debug mode (shows errors in browser)
docker run -p 8080:80 \
  -e APP_DEBUG=true \
  -e APP_ENV=local \
  -e LOG_LEVEL=debug \
  --name campcounselor \
  campcounselor
```

#### Database Operations
```bash
# Run migrations
docker exec campcounselor php artisan migrate

# Reset database (careful!)
docker exec campcounselor php artisan migrate:fresh

# Check database status
docker exec campcounselor php artisan migrate:status

# Access SQLite database
docker exec -it campcounselor sqlite3 /var/www/html/database/database.sqlite
```

#### Common Debugging Scenarios

**🔍 Application won't start:**
```bash
# Check startup logs for errors
docker logs campcounselor
```

**🔍 500 Internal Server Error:**
```bash
# Enable debug mode and check logs
docker stop campcounselor && docker rm campcounselor
docker run -p 8080:80 -e APP_DEBUG=true -e APP_ENV=local --name campcounselor campcounselor
docker logs -f campcounselor
```

**🔍 Database connection issues:**
```bash
# Test database connection
docker exec campcounselor php artisan tinker
# Then in tinker: DB::connection()->getPdo();
```

**🔍 Permission errors:**
```bash
# Check file permissions
docker exec -it campcounselor ls -la storage/
docker exec -it campcounselor ls -la bootstrap/cache/
```

#### Common Issues
- **Build fails**: Make sure Docker has internet access for package downloads
- **Permission errors**: Ensure volumes are properly mounted with correct permissions
- **Database connection**: Verify database credentials and network connectivity
- **Missing APP_KEY**: Container will auto-generate one on first startup
- **PHP errors**: Check multiple log sources - container logs, Apache logs, Laravel logs

### Option 2: Local Development

#### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- SQLite (default) or MySQL/PostgreSQL

### Setup
1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd campcounselor/php
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

The application will be available at `http://localhost:8000`

### Development Mode

For development with hot reloading, you can run Vite in development mode:

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite development server
npm run dev
```

This will enable hot module replacement and faster development cycles.

## Troubleshooting

### Common Issues

**"Vite manifest not found" error:**
```bash
# Make sure you've built the assets
npm run build
```

**Styles not loading properly:**
```bash
# Clear Laravel cache and rebuild assets
php artisan cache:clear
php artisan view:clear
npm run build
```

**"Module not found" errors:**
```bash
# Reinstall Node.js dependencies
rm -rf node_modules package-lock.json
npm install
npm run build
```

## Configuration

### Environment Variables
Add these to your `.env` file:

```env
# Bandcamp Configuration
BANDCAMP_URL=https://bandcamp.com
BANDCAMP_MAX_REQUESTS=500  # Maximum API requests per refresh (500 = ~10,000 albums)

# Database (SQLite is default)
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

### Database Backends
The application supports both SQLite (default) and MySQL/PostgreSQL:

**SQLite (Recommended for development):**
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

**MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=campcounselor
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Reverse Proxy Deployment

### Nginx with TLS Termination

Camp Counselor is configured to work seamlessly behind an nginx reverse proxy with TLS termination. The application automatically handles proxy headers for proper URL generation and security.

**Example nginx configuration:**

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    
    # TLS Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=63072000" always;
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    
    # Reverse Proxy Configuration
    location / {
        proxy_pass http://127.0.0.1:8000;  # Laravel dev server
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;
        
        # WebSocket support (if needed for future features)
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        
        # Timeouts for large album refreshes
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 300s;  # 5 minutes for large collections
    }
    
    # Static assets (if serving directly from nginx)
    location /build/ {
        alias /path/to/campcounselor/php/public/build/;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}

# HTTP to HTTPS redirect
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

**Laravel Configuration:**

The application is pre-configured to trust reverse proxy headers. No additional setup is required - Laravel will automatically:

- ✅ **Detect HTTPS**: Properly handle `X-Forwarded-Proto` headers
- ✅ **Generate URLs**: Use correct scheme (https://) in redirects and asset URLs  
- ✅ **Handle IPs**: Trust `X-Forwarded-For` for client IP detection
- ✅ **Security**: Validate proxy headers for secure operation

**Environment Variables:**

Update your `.env` file for production deployment:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Optional: Set specific trusted proxy IPs for enhanced security
# TRUSTED_PROXIES=192.168.1.100,10.0.0.1
```

**Production Considerations:**

- 🔒 **Use PHP-FPM**: Replace `php artisan serve` with proper PHP-FPM + nginx setup
- 📁 **File Permissions**: Ensure `storage/` and `bootstrap/cache/` are writable
- 🗄️ **Database**: Consider PostgreSQL or MySQL for production instead of SQLite
- 📊 **Monitoring**: Set up log rotation for `storage/logs/laravel.log`
- 🔄 **Process Management**: Use systemd or supervisor to manage the PHP processes

## Usage

### Getting Started
1. **Get your Bandcamp Fan ID**
   - Visit your Bandcamp profile page
   - Use the "Get Fan ID" feature in the app, or
   - Extract it from the page source (look for `fan_data.fan_id`)

2. **Import your collection**
   - Click "Refresh Albums" in the interface
   - Enter your Fan ID when prompted
   - The app will fetch your collection and wishlist

3. **Rate and comment**
   - Click "Edit Comment" on any album
   - Set a star rating and add personal notes
   - Changes are saved automatically

### API Endpoints

The application provides both web interface and JSON API:

**Albums:**
- `GET /` - Album grid (web interface)
- `GET /?search=term&filter=purchased&sort=rating_desc` - Filtered results
- `GET /albums/{id}` - Individual album details
- `PUT /albums/{id}/rating` - Update album rating and comment
- `GET /api/albums/stats` - Collection statistics

**Bandcamp Integration:**
- `POST /api/bandcamp/refresh` - Refresh collection from Bandcamp
- `POST /api/bandcamp/fan-id` - Get fan ID from username
- `POST /api/bandcamp/tracks` - Parse tracks from album URL
- `GET /api/bandcamp/status` - Check refresh status

### Search and Filtering

**Search:** Type in the search box to find albums by artist or album name

**Filters:**
- **All**: Show all albums
- **Purchased**: Show only owned albums
- **Wishlist**: Show only wishlist items

**Sorting:**
- **Artist** (A-Z or Z-A)
- **Rating** (1-5 stars, ascending or descending)
- **Created Date** (when added to your collection)
- **Updated Date** (when last modified)

## Development

### Running Tests
The application includes comprehensive test coverage:

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

**Test Coverage:**
- 31 tests with 118 assertions
- Unit tests for BandcampService
- Feature tests for controllers and models
- Database integration tests
- HTTP endpoint tests

### Project Structure

```
app/
├── Http/Controllers/
│   ├── AlbumController.php      # Album CRUD operations
│   └── BandcampController.php   # Bandcamp API integration
├── Models/
│   ├── Album.php               # Album model with scopes
│   └── Config.php              # Application configuration
└── Services/
    └── BandcampService.php     # Bandcamp API client

database/
├── migrations/                 # Database schema
└── factories/                  # Test data factories

resources/views/
├── layouts/app.blade.php       # Main layout template
└── albums/
    ├── index.blade.php         # Album grid view
    └── show.blade.php          # Individual album view

tests/
├── Feature/                    # Integration tests
└── Unit/                       # Unit tests
```

### Database Schema

**Albums Table:**
- `id` - Primary key
- `bandcamp_id` - Unique Bandcamp album ID
- `bandcamp_band_id` - Bandcamp band ID
- `album` - Album title
- `artist` - Artist name
- `url` - Bandcamp album URL
- `thumbnail_url` - Small album cover image
- `artwork_url` - Full-size album cover
- `purchased` - Boolean (true for owned, false for wishlist)
- `rating` - Integer (-1 to 5, -1 = unrated)
- `comment` - Text field for personal notes
- `created_at` / `updated_at` - Timestamps

**Config Table:**
- `id` - Primary key
- `last_refresh` - Unix timestamp of last Bandcamp sync

## API Reference

### Album Management

**Get Albums**
```http
GET /?search=artist&filter=purchased&sort=rating_desc
Accept: application/json
```

**Update Album Rating**
```http
PUT /albums/{id}/rating
Content-Type: application/json

{
    "rating": 5,
    "comment": "Amazing album!"
}
```

**Get Statistics**
```http
GET /api/albums/stats
```
Response:
```json
{
    "total": 150,
    "purchased": 75,
    "wishlist": 75,
    "rated": 120,
    "average_rating": 4.2
}
```

### Bandcamp Integration

**Refresh Collection**
```http
POST /api/bandcamp/refresh
Content-Type: application/json

{
    "fan_id": "123456789",
    "force": true
}
```

**Parse Album Tracks**
```http
POST /api/bandcamp/tracks
Content-Type: application/json

{
    "url": "https://artist.bandcamp.com/album/album-name",
    "artist": "Artist Name",
    "album": "Album Name"
}
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Write tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## License

This project is released under the GPLv3 or later, matching the original Vala application.

## Comparison with Original

This Laravel implementation provides the same core functionality as the original Vala/GTK application:

**Similarities:**
- ✅ Album collection and wishlist management
- ✅ 5-star rating system
- ✅ Personal comments and notes
- ✅ Bandcamp API integration
- ✅ Search and filtering capabilities
- ✅ Track preview functionality
- ✅ SQLite and PostgreSQL support

**Differences:**
- 🌐 Web-based interface instead of native GTK
- 📱 Responsive design for mobile devices
- 🔄 JSON API for programmatic access
- 🧪 Comprehensive test suite
- 📊 Statistics dashboard
- 🎵 No built-in music player (uses external links)

## Troubleshooting

**Common Issues:**

1. **Database errors**: Ensure migrations have been run with `php artisan migrate`

2. **Bandcamp sync fails**: 
   - Verify your fan ID is correct
   - Check that you're not hitting rate limits
   - Ensure your Bandcamp profile is public

3. **Missing album covers**: 
   - Images are loaded from Bandcamp's CDN
   - Check your internet connection
   - Verify the thumbnail URLs are valid

4. **Tests failing**: 
   - Run `php artisan migrate:fresh` to reset test database
   - Ensure all dependencies are installed with `composer install`

For more help, check the application logs in `storage/logs/laravel.log`.