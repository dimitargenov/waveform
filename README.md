### Start server
```
composer install
php artisan serve
```

### Call the service
```
curl http://127.0.0.1:8000/api/process
```

### Important files

#### Routing
routes/api.php

#### Controller
app/Http/Controllers/ProcessController.php

#### Services
app/Service

#### Channel file location
storage/app/public
