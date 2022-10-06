## laravel rest api using repository pattern
using laravel framework version 8

feature : 
1. Auth
2. report error log using bugsnag
3. repository pattern
4. trait response builder & bugsnag
5. validation request, try catch
6. role
7. update image using image intervention (has trait upload base64 image)
8. unitest
## Installation
1. copy .env.example .env
2. composer Install
3. npm Install
4. php artisan migrate --seed
5. php artisan key:generate
6. php artisan storage:link
<!-- 8. php artisan migrate --env=testing (for database testing) -->
7. import postman collection 'goKampus.postman_collection.json' to postman or insomnia for list api
8. php artisan serve

note: don't forget to enable extension=gd in php.ini
