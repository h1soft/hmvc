HMVC 0.0.1 Alpha
=========

Features
---------

*RESTfull
*HMVC
*PDO(MySQL、PGSQL、SQLite)
*ActiveRecord

### apache rewrite
```bash
RewriteEngine On
RewriteBase /

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```

### nginx rewrite
```bash
location / {
    try_files $uri $uri/ /index.php;
}


[OR] <br/>

location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
 
    location ~ /\.(ht|svn|git) {
        deny all;
    }
location ^~ /(app|bootstrap|config|crons|vendor) {
        deny all;
    }
```