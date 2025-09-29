![image](https://github.com/user-attachments/assets/a05a8e03-62f0-45ae-a6b1-115b3000d91b)

# Livewire Stisla

Project Laravel 11 dengan Livewire 3, Template Admin Stisla, dan Multi Auth menggunakan Laratrust serta Aktif/Non-aktif akun. Have Fun ^_^

## Prerequisites

Before you begin, ensure you have the following installed:

- [PHP](https://www.php.net/) (version 8.0 or higher)
- [Composer](https://getcomposer.org/) (for managing dependencies)
- [MySQL](https://www.mysql.com/) or any other database supported by Laravel
- [Node.js](https://nodejs.org/) and [npm](https://www.npmjs.com/) (for handling frontend and assets)
  
## Installation Steps

Follow these steps to get your project up and running.

### 1. Clone the Repository

Clone the repository to your local machine:

```bash
git clone https://github.com/fahmiibrahimdevs/livewire-stisla.git
```

### 2. Install PHP Dependencies

After cloning the repository, navigate to the project directory and install the PHP dependencies using Composer:

```bash
cd livewire-stisla
composer install
```

### 3. Configure .env File

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Then, open the `.env` file and update the database connection and other environment variables based on your local configuration.

### 4. Generate Application Key

Run the following command to generate the application key:

```bash
php artisan key:generate
```

### 5. Run Database Migrations Fresh + Seed

Run the migration command to create the necessary database tables:

```bash
php artisan migrate:fresh --seed
```

### 6. Install Frontend Dependencies

Install the frontend dependencies using npm:

```bash
npm install
```

### 7. Build Frontend Assets

Once the frontend dependencies are installed, run the following command to build the assets:

```bash
npm run dev
```

If you want to build assets for production, use:

```bash
npm run prod
```

### 8. Run the Application

Your application is now ready to run. To start the Laravel development server, use:

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`.

### License

This project is licensed under the [MIT License](https://github.com/fahmiibrahimdevs/livewire-stisla/blob/main/LICENSE).