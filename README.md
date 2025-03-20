# GIS Project

This repository contains a **GIS (Geographic Information System)** application built using the Laravel framework. The project integrates modern web development tools and libraries to provide a robust and scalable platform for managing and visualizing geospatial data.

## Features

-   **Laravel Framework**: Built on Laravel 11.x, leveraging its powerful MVC architecture.
-   **Inertia.js**: Provides a seamless SPA (Single Page Application) experience with Vue 3.
-   **Tailwind CSS**: For modern, responsive, and customizable UI design.
-   **OpenLayers (OL)**: A powerful library for rendering interactive maps.
-   **API Integration**: Supports RESTful APIs for geospatial data management.
-   **Task Automation**: Includes queue handling and background job processing.
-   **Development Tools**:
    -   Vite for fast builds and hot module replacement.
    -   Laravel Pint for code formatting.
    -   Pest for testing.

## Installation

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   Node.js and npm
-   Docker (optional, for containerized development)

### Steps

1. Clone the repository:

    ```bash
    git clone <repository-url>
    cd gis
    ```

2. Install PHP dependencies:

    ```bash
    composer install
    ```

3. Install JavaScript dependencies:

    ```bash
    npm install
    ```

4. Set up the environment:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. Run database migrations:

    ```bash
    php artisan migrate
    ```

6. Start the development server:

    ```bash
    npm run dev
    php artisan serve
    ```

    Alternatively, use Docker Compose:

    ```bash
    docker-compose up
    ```

## Development

-   **Scripts**:

    -   `npm run dev`: Start the Vite development server.
    -   `npm run build`: Build assets for production.
    -   `php artisan serve`: Start the Laravel development server.

-   **Testing**:

    -   Run tests using Pest:
        ```bash
        ./vendor/bin/pest
        ```

-   **Queue Handling**:
    -   Start the queue worker:
        ```bash
        php artisan queue:listen
        ```

## Folder Structure

-   **`app/`**: Contains the core application logic, including controllers, models, and services.
-   **`resources/`**: Contains frontend assets like Vue components, Tailwind CSS, and Blade templates.
-   **`public/`**: The entry point for the application, serving static files.
-   **`database/`**: Contains migrations, seeders, and factories for database management.

## Dependencies

### PHP Dependencies (from `composer.json`)

-   Laravel Framework
-   Inertia.js Laravel Adapter
-   Laravel Sanctum (API authentication)
-   Laravel Jetstream (scaffolding)
-   Tightenco Ziggy (route management)

### JavaScript Dependencies (from `package.json`)

-   Vue 3
-   Tailwind CSS
-   OpenLayers (OL)
-   Axios
-   Laravel Vite Plugin

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.

## Contributing

Contributions are welcome! Please fork the repository and submit a pull request.

## Contact

For any inquiries or support, please contact the project maintainer.
