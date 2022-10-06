## laravel cms gokampus
using laravel framework version 9

feature : 
1. Auth
2. report error log using bugsnag
3. repository pattern
4. trait response builder & bugsnag
5. validation request, try catch
6. role
7. update image using image intervention (has trait upload base64 image)
8. cache

## Installation
1. php artisan migrate --seed
2. import postman collection 'goKampus.postman_collection.json' to postman or insomnia for list api
3. php artisan serve

note: don't forget to enable extension=gd in php.ini

rules : 
1. roles terdiri dari administrator & regular
2. administrator bisa melihat semua artikel sedangkan regular user hanya bisa melihat artikelnya sendiri
3. administrator bisa membuat user baik tipe administrator atau regular user
4. untuk rest api terdiri dari folder auth & article, ketika akan mengakses artikel setiap user harus login terlebih dahulu dan harus memasukan bearer token. jika pada postman ada pada authorization.
