# Laravel Mail API

<p align="center">
    <a href="https://2am.tech/our-work/open-source" target="_blank">
        <img src="./assets/img/mail-api-service.png" width="100%" target="_blank" alt="Laravel Mail API">
    </a>
</p>


[![Build](https://github.com/2amigos/laravel-mail-api/actions/workflows/ci.yml/badge.svg)](https://github.com/2amigos/laravel-mail-api/actions/workflows/ci.yml)
[![Software License](https://img.shields.io/badge/license-BSD-brightgreen.svg?style=flat-square)](LICENSE.md)
[![code coverage - change](https://github.com/2amigos/laravel-mail-api/actions/workflows/ci.yml/badge.svg)](https://github.com/2amigos/laravel-mail-api/actions/workflows/ci.yml)
[![code qualit - change](https://github.com/2amigos/laravel-mail-api/actions/workflows/ci.yml/badge.svg)](https://github.com/2amigos/laravel-mail-api/actions/workflows/ci.yml)

## About Mail API Service

Laravel Mail API is an email microservice to avoid having to configure mail over and over
on projects involving microservices infrastructure.

It uses [Laravel Sanctum](https://laravel.com/docs/10.x/sanctum) to manage token issuing and user authentication.
The email sender interface is defined with [Laravel Mail](https://laravel.com/docs/10.x/mail) (powered by [Symfony Mailer](https://symfony.com/doc/6.2/mailer.html))
with [Markdown Mail Notifications](https://laravel.com/docs/10.x/notifications#markdown-mail-notifications) to enhance to email layout configuration.

To accomplish the email sending with an efficient response time, Laravel Mail API uses [Laravel Queues](https://laravel.com/docs/10.x/queues)
to execute the tasks in background. We already have configured drivers for **Redis** ([Predis](https://github.com/predis/predis)) and **Database** connections. You are free to configure other driver if it's needed.

Finally, it makes use of [Laravel Localization](https://laravel.com/docs/10.x/localization#main-content) for content internationalization and [Laravel Logs](https://laravel.com/docs/10.x/logging#main-content) for logs. 

## Install

## Configuration

### Docker Container Configuration

A docker image was provided through [Laravel Sail](https://laravel.com/docs/10.x/sail#main-content).

You can configure a [shell alias for Sail](https://laravel.com/docs/10.x/sail#configuring-a-shell-alias) command and make it easier to access.

Please, refer to Sail Docs to know more about [executing commands in your application's container](https://laravel.com/docs/10.x/sail#executing-sail-commands).

To start up/stop the docker container, use the following commands:

```SH
// to start up the container
$ ./vendor/bin/sail up -d

# to stop the container
$ ./vendor/bin/sail stop 
```

If you're using Laravel Sail, refer to **.env.sail** for mysql and redis connection setup.   

The **.env.example** file gives the basic structure your project must have in order to run the service properly. Copy its content to **.env** file.

### Application Configuration

After configuring your database connection on your **.env** file, you're ready to migrate the necessary tables through the command bellow:

```SH
$ php artisan migrate
```

or, if you are using Sail

```SH
$ ./vendor/bin/sail php artisan migrate
```

Now, with the database set, the user can be created by running the next command, and following a couple of simple steps.

```SH
$ php artisan app:create-user

# or on sail
$ ./vendor/bin/sail php artisan app:create-user
```

### Email Transport Configuration
You must configure your mailer transport on .env file as well.
This project was built using SMTP. Laravel Mail provides an easy way to [configure](https://laravel.com/docs/10.x/mail#configuration)
the driver your project needs.

This is a sample SMTP Driver configuration:
```
MAIL_MAILER=smtp
MAIL_HOST={smtp-server}
MAIL_PORT={smtp-port}
MAIL_USERNAME={smtp-mailer-email-address}
MAIL_PASSWORD={smtp-mailer-email-password{
MAIL_ENCRYPTION=tls
```

The project has [Laravel Pint](https://laravel.com/docs/10.x/pint) configured, you can run the command bellow to assure the code style is being followed:
```SH
$ ./vendor/bin/pint
```

## Usage

To serve the application, Laravel provides the handy built in command **serve**
```SH
$ php artisan serve

# or on Sail
$ ./vendor/bin/sail php artisan serve
```

This command serve your application at [http://127.0.0.1:8000](http://127.0.0.1:8000).
If you run the command through Laravel Sail, the application will be served on port 80 instead [http://127.0.0.1](http://127.0.0.1).

## Endpoints

The API has two endpoints: `/token` and `/send-message`. They live under the `/api` prefix.

A postman collection `Laravel Mail API` has been served to simplify the testing process.

### /token
The **token** endpoints uses Basic Authentication to validate the user. It expects only a header
`Authorization: Basic {basic-token}` in order to authenticate the user.

The `basic-token` can be obtained by `echo -n email:password | base64` 

Here is a `/token` request example:

```SH
$ curl --location --request POST 'http://localhost/api/token' 
\ --header 'Authorization: Basic {basic_token}'
```

### /send-message

The **send-message** is where the emails are dispatched.

It must have a an `Authorization: Bearer {token}` header.

Then you can send `multipart/form-data` request with the following parameters:

- from
- sender *(optional)*
- to
- receiver *(optional)*
- subject
- language *(optional, default=en)*
- template *(optional, default defined on the application)*
- attachments[] *(optional)*

Here is a sample request:

```SH
$ curl --location 'http://localhost/api/send-message' \
--header 'Authorization: Bearer {token}' \
--form 'from="{email-sender@domain}"' \
--form 'sender="Mark"' \
--form 'to="{email-receiver@domain}"' \
--form 'receiver="Jhon"' \
--form 'subect="testing api"' \
--form 'attachments[]=@"{path to file 3}"' \
--form 'attachments[]=@"{path to file 2}"' \
--form 'language="en"' \
--form 'template="hello-world"'
```

Done. Now your new message is on the queue, ready to be dispatched. To achieve that, 
you just need to run this command:

```SH
$ php artisan queue:work

# or on sail
$ ./vendor/bin/sail php artisan queue:work
```

#### Email Attachments

The /send-email endpoint apply validations for attachments mimetypes.

By default, the application will allow `PDF` and any `Image` mimetypes.

You can easily set an array of your needed mimetypes, or even set a string `'*'` to allow any mimetype.
e.g. to allow any file mimetype, you just need to change this line on `config/laravel-mail-api.php`:

```php
use Laravel\Sanctum\PersonalAccessToken;

// from this 
return [
    ...
    'attachmentsAllowedMimetypes' => env('ATTACHMENT_MIMETYPES', ['application/pdf', 'image/*']),
];

// to this
return [
    ...
    'attachmentsAllowedMimetypes' => env('ATTACHMENT_MIMETYPES', '*'),
]; 
```

### Customization

As mentioned before, this service uses [Markdown Mail Notifications](https://laravel.com/docs/10.x/notifications#markdown-mail-notifications) to enhance the email layout configuration.
You can find the template files for Markdown published at `resources/views/vendor/mail/html`.

The email body is set on template files. It already has [Laravel Localization](https://laravel.com/docs/10.x/localization#main-content) to provide
an internationalization feature to it.

You can check the default template at `recources/views/templates/hello-world.blade.php` and 
the password template at `resruoces\views\templates\password.blade.php` for reference, as they've been written
with localization already.

You can define for how long a token will be valid by declaring the constant `TOKEN_TIME` (in minutes) in your .env file. Default is 60.

You can define the default email template declaring `DEFAULT_TEMPLATE`, where the default is `hello-world` and the default language by
declaring the `LANGUAGE` constant (default `en`).

### Deploying

Although the queue work command is handy and makes it really easy to consume the queue while testing the application,
but it's extremely recommended to use [Supervisor](http://supervisord.org/) when deploying to production.

Laravel has a nice guide to properly [configure](https://laravel.com/docs/10.x/queues#supervisor-configuration) the Supervisor.

## Contributing

Please, see [CONTRIBUTING](./contributing.md) for details.

## License
The BSD License (BSD). Please see [License File](./license.md) for more information/

> [![2am.tech](https://avatars.githubusercontent.com/u/3440791?s=200&v=4)](https://2am.tech/)

Web development has never been so fun!

[https://2am.tech/](https://2am.tech/)

