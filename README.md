# Laravel Mail API

<p align="center">
    <a href="https://2am.tech/our-work/open-source" target="_blank">
        <img src="./assets/img/mail-api-service.png" width="100%" target="_blank" alt="Laravel Mail API">
    </a>
</p>


[![Build](https://github.com/2amigos/laravel-mail-api/actions/workflows/ci.yml/badge.svg)](https://github.com/2amigos/laravel-mail-api/actions/workflows/ci.yml)
[![Software License](https://img.shields.io/badge/license-BSD-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Codacy Badge Quality](https://app.codacy.com/project/badge/Grade/0a7b90cb75054113b3977186ee89cc33)](https://app.codacy.com?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge Coverage](https://app.codacy.com/project/badge/Coverage/0a7b90cb75054113b3977186ee89cc33)](https://app.codacy.com?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)

## About Mail API Service

Laravel Mail API is an email microservice to avoid having to configure mail over and over
on projects involving microservices infrastructure.

It uses Signed AppKeys to handle the authentications through requests.
The mailer transport interface was build using [Laravel Mail](https://laravel.com/docs/10.x/mail) (powered by [Symfony Mailer](https://symfony.com/doc/6.2/mailer.html))
with [Markdown Mail Notifications](https://laravel.com/docs/10.x/notifications#markdown-mail-notifications) to enhance the email layout configuration.

To accomplish dispatching emails with an efficient response time, Laravel Mail API uses [Laravel Queues](https://laravel.com/docs/10.x/queues)
to execute the tasks in background. We already have configured a driver for **Redis** ([Predis](https://github.com/predis/predis)) connection. You are free to configure other driver if it's needed.

Finally, it makes use of [Laravel Localization](https://laravel.com/docs/10.x/localization#main-content) for content internationalization and [Laravel Logs](https://laravel.com/docs/10.x/logging#main-content) 
for logging. 

## Install
```bash
$ composer create-project 2am.tech/laravel-mail-api app
```

## Configuration

### Docker Container Configuration

A docker image was provided through [Laravel Sail](https://laravel.com/docs/10.x/sail#main-content).

You can configure a [shell alias for Sail](https://laravel.com/docs/10.x/sail#configuring-a-shell-alias) command and make it easier to access.

Please, refer to Sail Docs to know more about [executing commands in your application's container](https://laravel.com/docs/10.x/sail#executing-sail-commands).

To start up/stop the docker container, use the following commands:
To be able to use the docker container, you need to first install the dependencies.
To achieve that, you must execute the given command:

```bash
$ docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

```SH
// to start up the container
$ ./vendor/bin/sail up -d

# to stop the container
$ ./vendor/bin/sail stop
```

If you're using Laravel Sail, refer to **.env.sail** for redis connection strings.   

The **.env.example** file gives the basic structure your project must have in order to run the service properly. Copy its content to **.env** file.

### Application Configuration

As mentioned before, we're using Signed AppKeys for requests authentication.

First, you have to define you're Access Token (AppKey and AppSecret) on 
`./config/laravel-mail-api`, on `AccessTokens` section.

We already provided a sample access key.

```php
# register access token
...
'accessTokens' => [
    'access-key-user-1' => [
        'appKey' => 'jwYitJJOop2v',
        'appSecret' => 'token',
    ],
   ... 
];
```

You can register as many access token it's necessary for your microservices.

With the access token defined, we just need to sign it to and add its values
to the request header.

### Access Token Sign Algorithm

Define a hash algorithm to create a hashed token. The default is `sha512`.
You can define it on by adding `HASH_SIGNATURE` on the `.env` file:

``
HASH_SIGNATURE=sha512
``

To create a signature for you access token, you follow this steps:

Get the current timestamp (on UTC) on ISO-8601 format.

```bash
$ date -u +'%Y-%m-%dT%H:%M:%S.%3NZ'

# ouputs date format
$ 2023-08-10T04:32:25.620Z
```

Then, simple hash your AppKey with the gotten timestamp, using your AppSecret as the 
hash password.

```bash
signature: HASH-HMACK( AppKey + timestamp, AppSecret )

# with our sample values
signature: HASH-HMACK( 'jwYitJJOop2v' + '2023-08-10T04:32:25.620Z', 'token' )

# output
cb64bbccdff25dcaba24e4c029aa54d99522a3e2e70e5be7be1b48dd8816b4e05b0102f2a2775c895ac73f649b45f6f97f755a112a9f4a206e4053128fc5ada9
```

A command was created to help the signature creation, for testing purposes:

```bash
$ php artisan app:create-signature
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

The API has one endpoint: `/api/email/send`.

A postman collection `Laravel Mail API` has been served to simplify the testing process.

### /api/email/send

In order to have the authentication mechanism working, we must add the following headers:
```bash
Authentication: Bearear {signature}
ts: {timestap (used to sign the appKey)}
accessToken: {your access token}

# using our sample values
Authentication: Bearear cb64bbccdff25dcaba24e4c029aa54d99522a3e2e70e5be7be1b48dd8816b4e05b0102f2a2775c895ac73f649b45f6f97f755a112a9f4a206e4053128fc5ada9
ts: 2023-08-10T04:32:25.620Z
accessToken: access-key-user-1
```

Then you can send `multipart/form-data` request with the following parameters:

-  from
-  sender *(optional)*
-  to
-  receiver *(optional)*
-  subject
-  language *(optional, default=en)*
-  template *(optional, default defined on the application)*
-  attachments[] *(optional)*

Here is a sample request:

```SH
curl --location 'http://localhost:8000/api/email/send' \
--header 'accessKey: tests' \
--header 'ts: 2023-08-10T04:56:50+00:00' \
--header 'Authorization: Bearer a7ef7032999db841c75a7a2de4d40106e6de19b7c269b722f81bc51a1f713f6642d25e3bc25af7b04f9a385ef8f9c9ed346e029d0262cbc61dde2f640d0f8c48' \
--form 'from="test@mail.com"' \
--form 'to="receiver@email.com"' \
--form 'sender="2am.tech"' \
--form 'receiver="Amigo OSS"' \
--form 'subject="test api"'
```

Done. Now your new message is on the queue, ready to be dispatched. To achieve that, 
you just need to run this command:

```SH
$ php artisan queue:work

# or on sail
$ ./vendor/bin/sail php artisan queue:work
```

#### Email Attachments

The `/api/email/send` endpoint apply validations for attachments mimetypes.

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

## Serverless deployment to AWS Lambda

This project uses [Bref](https://bref.sh/) framework to empower a smoothly deployment to
AWS Lambda.

It has a tiny list of dependencies to achieve that:

- Node.js 14.0+
- [Serverless](https://www.serverless.com/framework/docs) framework
- and, an AWS account (key and secret).

To install the Serverless framework:

```bash
$ npm install -g serverless
```

Install Serverless plugin dependency:
```bash
$ npm install
```

And them, setup the AWS credentials for the Serverless framework:
```bash
$ serverless config credentials --provider aws --key "key" --secret "secret"
```

You can change the project name on file `serverless.yml` to match your project name,
right on the first line:

```yml
service: laravel-mail-api
```

Last step before the actual deployment is to clear local cashed files:
```bash
$ php artisan config:clear
```

and, them:
```bash
$ serverless deploy
```

Once the process is finished, you will be prompted with the endpoint,
functions and jobs created on AWS Lambda!

## Contributing

Please, see [CONTRIBUTING](./contributing.md) for details.

## License
The BSD License (BSD). Please see [License File](./license.md) for more information/

> [![2am.tech](https://avatars.githubusercontent.com/u/3440791?s=200&v=4)](https://2am.tech/)

Web development has never been so fun!

[https://2am.tech/](https://2am.tech/)

