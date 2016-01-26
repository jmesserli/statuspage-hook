# Statuspage Hook

A simple webhook for [Uptime Robot](https://uptimerobot.com) users to update their [Cachet](https://cachethq.io/) statuspage

## Installation

Run `composer update --no-dev` to pull the needed dependencies using [Composer](https://getcomposer.org/).

Rename the `config.example.php` to `config.php` and set the values accordingly.
The config contains comments that explain which values you have to enter.

### Nginx

```
server {
    server_name status-hook.yourdomain;
    listen 80;
    
    try_files $uri $uri/ /index.php;
    
    location ~ \.php {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;
    }
}
```

### Apache2

(you are welcome to provide an example config)

You must basically rewrite all requests to index.php

## Uptimerobot Configuration

On the Uptimerobot side, you just have to add an alert contact like so:

![Uptimerobot Web Hook](http://i.imgur.com/yMH2N1t.png)

Then add the alert contact to all monitors you configured earlier