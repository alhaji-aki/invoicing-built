# Invoicing App

This is a basic invoicing api for users to manage their invoices.

## Requirements

- [ ] Implement item creation and inventory tracking to insure the user cannot over sell
- [ ] An invoice should have issue and due dates
- [ ] An invoice should have a customer
- [ ] An invoice can have at least 1 item
- [ ] Each item should have unit price, quantity, amount and description
- [ ] User Authentication and authorization

## Project Setup

- Clone project `git clone git@github.com:alhaji-aki/invoicing-built.git`
- Run `composer install`
- Copy `.env.example` to `.env` and fill your values
- Run `php artisan key:generate` to generate app key
- Fill database and mail credentials in `.env` file
- Run `php artisan migrate --seed`, this will seed a default user with email `user@invoicing-app.test` and password `password`
- You can get entire postman collection documentation [here](https://documenter.getpostman.com/view/2848345/2s93zB6hgy)

## Testing

## License

This app is licensed under MIT license. See [LICENSE](LICENSE) for details.
