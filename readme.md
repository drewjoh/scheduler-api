# Sample API

This is a simple Laravel based web app giving a basic example of an API.

## To Install

1. Clone the repository.
2. Point your local web server to `./public` for a local domain. `api.dev`
3. Run `composer install` to init your vendor directory.
4. Create a local database and configure your `.env` file for this Laravel app to use the newly created database.
5. Run `php artisan migrate` to init the database tables for this app.
6. Run `php artisan db:seed` to create a user in the `users` table.
7. Create some shifts in the `user_shifts` table to start having data.
    1. Remember that the `date` fields use the RFC 2822 format.
9. Use an app like Paw to make calls to the API. A `token` corresponding to the `token` field on the `users` table is required for authentication.

### Sample API Calls

* http://api.dev/user/1/shifts?token=1 - Shows the shifts for User #1
* http://api.dev/shift/1/coworkers?token=1 - Show the other users working with you for a given shift
* http://api.dev/shift/history?token=1 - Show shift history for the AuthUser