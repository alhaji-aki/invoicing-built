# Invoicing App

This is a basic invoicing api for users to manage their invoices.

## Requirements

- [x] Implement item creation and inventory tracking to insure the user cannot over sell
- [x] Customer management
- [x] An invoice should have issue and due dates
- [x] An invoice should have a customer
- [x] An invoice can have at least 1 item
- [x] Each item should have unit price, quantity, amount and description
- [x] User Authentication and authorization

## Project Setup

- Clone project `git clone git@github.com:alhaji-aki/invoicing-built.git`
- Run `composer install`
- Copy `.env.example` to `.env` and fill your values
- Run `php artisan key:generate` to generate app key
- Fill database and mail credentials in `.env` file
- Set the `APP_FRONTEND_URL` key in the `.env` to the base url of your frontend application
- Run `php artisan migrate --seed`, this will seed a default user with email `user@invoicing-app.test` and password `password`
- Start queue worker using `php artisan queue:listen` or `php artisan queue:work` if your `QUEUE_CONNECTION` in the `.env` file is not sync
- You can get entire postman collection documentation [here](https://documenter.getpostman.com/view/2848345/2s93zB6hgy)

## Testing

## License

This app is licensed under MIT license. See [LICENSE](LICENSE) for details.
