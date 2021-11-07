# Verisure

This application will keep in sync with your Verisure UK account and will provide endpoints to interact with the alarm.

## Disclaimer

This application relies on Verisure website and HTML responses to work. These can (and will) change in the future without any warning and will eventually break the application: I won't maintain this application forever, and I won't be responsible for any issue with your alarm. Use this application at your own risk.
I will try my best to maintain the application until approx June 2022.

## Setup

This is a PHP Laravel application and it's setup is similar to any other Laravel application:

1) Copy the .env.example to .env changing the following variables:

```
DB_DATABASE=verisure
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

VERISURE_APP_USERNAME=07777777777
VERISURE_APP_PASSWORD=password-verisure
VERISURE_APP_INSTALLATION=123456
VERISURE_APP_URL=https://customers.verisure.co.uk
```

**VERISURE_APP_USERNAME** and **VERISURE_APP_PASSWORD** are the username and password you use to login to the browser interface of `https://customers.verisure.co.uk/`. The username should be a phone number. **VERISURE_APP_INSTALLATION** is the installation number and will be displayed in your homepage under the address.

2) You will need an APP_KEY: run `php artisan key:generate` from the root of the project to generate it.

3) Setup the worker responsible to make all the asyncrounous calls; Add a file *verisure-worker.conf* in your */etc/supervisor/conf.d* folder:

```
[program:verisure-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/verisure/artisan queue:listen --queue=high,default --sleep=3 --tries=1 --delay=5
autostart=true
autorestart=true
user=root
numprocs=1
redirect_stderr=true
stdout_logfile=/var/coursesmanager/storage/logs/worker.log
```

Note: `/var/verisure/` is the path to the folder where your project is placed.

4) Run the migrations: `php artisan migrate`.

5) Create a user via Tinker: run `php artisan tinker` from the root of the folder, then paste the following and press enter to save the user:

```
$user = new App\User();
$user->password = Hash::make('your-password');
$user->email = 'your@email.com';
$user->name = 'My Name';
$user->admin = true;
$user->save();
```

6) Run `npm run production` to generate the frontend stuff.

7) Visit the website to `verisure.test/login` to login with the previously created email and password.

Note: https support on your server is highly recommended. You can use Certbot to generate the certificates automatically.

## Frontend

This app provides a frontend to manage the alarm and the options.

## Settings

### General settings

#### API Token

To start making API calls to the app, you need an api secret. This token will be generated in the settings under `API Authentication Token`.
You can then make any call by appending this token in the request url:

`http://verisure.test/api/activate/house/day?auth_token=abc1234`

Note: you can regerenate the token or even disable the API authentication completely by deselecting the switch button. In this way any call will be accepted.

#### Notifications

The endpoints that will be called every time the alarm status changes (actively after a call made from this application) or when you receive some error.

#### Censure Responses

This option should always be on. It will prevent massive responses from truelayer to be stored in your database and will strip sensitive and unuseful data.

#### Status Job

This will determine how long to wait for the status to be retrieved after each call and how long to wait between the calls. Leave the default values, as they simulate the browser calls.

#### Session

If you want to keep your Alarm status in sync, you have to turn on the following switch: `Session - Keep alive`. This will make a call to verisure every 15 minutes avoiding the session token to expire and making all the calls faster. The TTL field will determine how ofter the session token has to be invalidated. This will prevent the same session token to be used indefinitely. Note: keep this TTL to max 240 (4 hours).


### Scheduler

You can schedule the activation or deactivation of your main or annex alarm in the final tab. Every action accepts a cron entry that determines what time the action should run.

## Endpoints

### Interact with Main Alarm

Activate:

```
api/activate/house/night
api/activate/house/day
api/activate/house/full
```

Deactivate:

```
api/deactivate/house
```

### Interact with Annex alarm

Activate:
```
api/activate/garage
```

Deactivate:
```
api/deactivate/garage
```

### Get alarm status

```
api/records
```

Response:

```
{"house":0,"garage":0,"age":1609598774}
```
