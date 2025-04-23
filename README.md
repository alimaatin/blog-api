# Laravel Blog API
> A blog API written in Laravel with JWT authentication and like dislike functionality and an admin panel to accept or reject upcoming posts.

---
# Getting Started
## Installation
Please check the official Laravel installation guide before you start. [Official Documentation](https://laravel.com/)

Clone the repository
```
git clone https://github.com/alimaatin/blog-api/
```

Switch to the repository folder
```
cd blog-api
```

Install all the dependencies using composer
```
composer install
```

Copy the example env file and make the required configuration changes in the .env file
```
cp .env.example .env
```

Create storage link for file uploads
```
php artisan storage:link
```

Generate a new application key
```
php artisan key:generate
```

Generate a new JWT secret
```
php artisan jwt:generate
```

Run the database migrations
```
php artisan migrate
```

Start the local development server
```
php artisan serve
```

**TL;DR command list**
```
git clone https://github.com/alimaatin/blog-api/
cd blog-api
composer install
cp .env.example .env
php artisan storage:link
php artisan key:generate
php artisan jwt:generate
php artisan migrate
php artisan serve
```

## Documentation
This app is documented using [Scramble](https://scramble.dedoc.co/)

