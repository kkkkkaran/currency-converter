# Laravel Currency Converter API

The Currency Converter API is a Laravel-based application that provides users with the capability to obtain real-time currency conversion rates against the USD and to generate reports for historical conversion rates. It utilizes the CurrencyLayer API to fetch up-to-date currency information.

## Features

- **User Authentication**: Implements Laravel Sanctum for secure API token authentication.
- **Currency Conversion**: Offers real-time conversion rates for up to five selected currencies against the USD.
- **Historical Reports**: Enables users to request historical conversion rates between USD and another currency, selectable by different ranges and intervals.
- **Currency List**: Provides a comprehensive list of all available currencies supported by the CurrencyLayer API.

## Getting Started

### Prerequisites

Before you begin, ensure you have the following installed:
- PHP >= 8.2
- Composer
- A supported database by Laravel (MySQL, PostgreSQL, SQLite, etc.)
- A CurrencyLayer API account and key

### Installation

Follow these steps to get your development environment running:

1. **Clone the repository**

```bash
git clone https://github.com/yourusername/currency-converter.git
cd currency-converter
```

### 2. Install Dependencies

Navigate to the project directory and install the PHP dependencies with Composer:

```bash
composer install
```

### 3. Setup Environment

Duplicate the .env.example file to create a .env file which Laravel uses for environment-specific settings:

```bash
cp .env.example .env
```

Edit the .env file to configure your application settings, particularly for the database and CurrencyLayer API:

For MySQL:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password

CURRENCYLAYER_API_KEY=your_currencylayer_api_key_here
```

For SQLite: 

```
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database/database.sqlite
```

If you don't already have a SQLite database file, create one with the following command:

```
touch /path/to/database/database.sqlite
```
### 4. Generate Application Key

Run the following Artisan command to generate a new application key. This is important for securing your application's session and encrypted data:

```bash
php artisan key:generate
```

### 5. Run Migrations

Run the following Artisan command to generate a new application key. This is important for securing your application's session and encrypted data:

```bash
php artisan migrate
```

### 6. Serve the Application

Start the Laravel development server:

```bash
php artisan serve
```

Your Currency Converter API should now be accessible at http://localhost:8000.

## Usage

After setting up the project, you can utilize the API endpoints as follows:

### Authentication

The API uses Laravel Sanctum for authentication. Register and log in to obtain an API token.

- **Register**: Send a `POST` request to `/register` with `name`, `email`, and `password`.
- **Login**: Send a `POST` request to `/login` with `email` and `password`. Store the returned `token` for subsequent requests.

### API Endpoints

**Note:** All protected endpoints require an `Authorization` header with the value `Bearer {your_token_here}`.

- **List Available Currencies**:  
  `GET /api/currencies`  
  Lists all available currencies supported by the CurrencyLayer API.

- **Convert Currencies**:  
  `GET /api/currencies/convert?currencies[]=USD&currencies[]=EUR`  
  Converts selected currencies against each other. Replace `USD` and `EUR` with your selected currencies. Accepts up to five currencies.

- **Request Historical Conversion Rate Report**:  
  `POST /api/currencies/report`  
  Submits a request for a historical conversion rate report. Requires a JSON payload specifying `currency`, `start_date`, `end_date`, and `interval`.

### Example Requests

**Register User:**

```bash
curl -X POST /register \
     -H "Content-Type: application/json" \
     -d '{"name": "John Doe", "email": "john@example.com", "password": "password"}'
```

**Convert Currencies:**

```bash
curl -X GET /api/currencies/convert?currencies[]=USD&currencies[]=EUR \
     -H "Authorization: Bearer {your_token_here}"
```

## Testing

To run the PHPUnit tests included with the project, execute the following command:

```bash
./vendor/bin/phpunit
```

