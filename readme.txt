Internal Server Error

Copy as Markdown
Illuminate\Contracts\Container\BindingResolutionException
Target class [check.course.review] does not exist.

LARAVEL
12.34.0
PHP
8.4.15
UNHANDLED
CODE 0
500
GET
http://127.0.0.1:8000/generate-certificate/1

Exception trace
50 vendor frames

public\index.php
public\index.php:20

15
16// Bootstrap Laravel and handle the request...
17/** @var Application $app */
18$app = require_once __DIR__.'/../bootstrap/app.php';
19
20$app->handleRequest(Request::capture());
21
1 vendor frame

Queries
1-1 of 1
mysql
select * from `users` where `id` = 1 limit 1
2.74ms
Headers
host
127.0.0.1:8000
connection
keep-alive
pragma
no-cache
cache-control
no-cache
sec-ch-ua
"Chromium";v="142", "Google Chrome";v="142", "Not_A Brand";v="99"
sec-ch-ua-mobile
?0
sec-ch-ua-platform
"Windows"
upgrade-insecure-requests
1
user-agent
Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36
accept
text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
sec-fetch-site
same-origin
sec-fetch-mode
navigate
sec-fetch-user
?1
sec-fetch-dest
document
referer
http://127.0.0.1:8000/generate-certificates
accept-encoding
gzip, deflate, br, zstd
accept-language
en-US,en;q=0.9
cookie
__stripe_mid=4fac6ec2-f0da-48bd-9259-25ffe6443cdf8ee442; XSRF-TOKEN=eyJpdiI6ImN5SXkvUjBFQjZMZFMxUHk1ekhNNHc9PSIsInZhbHVlIjoiM1hyaEp1MmhiVFcwVkMzbXY5Qzl3dlMxbHF5cHp0RGs4Umpqam4vK0J5U3hGekorYm5JTGlRbW5EVFpIeWFUdTllSGhod2ppZUxFVUNMMjJoV1ArL1R3akhIY2NMVVdXY3M3bldxcTE3RTRobkpVVG0rNTBYYXEvVklhQ0hTYTEiLCJtYWMiOiJiZjk3YmFkMmE3YjViZDdhMmI4NTZjODBlMWQwZmQyZGMyYzc1NzJmMWMzNWFjM2U5ODExZGI5YTdhM2E0NDRkIiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6ImpEVnZpMUJpTk5ISU5qbG83dThhanc9PSIsInZhbHVlIjoiN3hTbC9xMHZYMHljcWVITDVYN0xUK0lpaDhJRUltQjQrbkJxUkJnaFZodUV0NlFlTDFqTE5DQWx4bFlaMXVGV3U1ZnZrRkVkb0FqaHgvbUZzcUVkSDd2QlJ0OU13RXE0MzdDQmRnbXpDdXVrUnRKRjQzQlQ3ZW1zTnc3Q25ZcHkiLCJtYWMiOiI5YmY2ZmMxYjQ1MWNkZjg2ZWFkODExMTg4ZTBhYmQ4ZDJjNjZkNmQ4ZmNiZDdhMGNlZmY3YTMyYWQ0NDVjNDA3IiwidGFnIjoiIn0%3D
Body
// No request body
Routing
controller
Closure
middleware
web, auth, check.course.review
Routing parameters
{
    "enrollment_id": "1"
}
Internal Server Error

Copy as Markdown
Illuminate\Contracts\Container\BindingResolutionException
Target class [check.course.review] does not exist.

LARAVEL
12.34.0
PHP
8.4.15
UNHANDLED
CODE 0
500
GET
http://127.0.0.1:8000/generate-certificate/1

Exception trace
8 vendor frames

public\index.php
public\index.php:20

15
16// Bootstrap Laravel and handle the request...
17/** @var Application $app */
18$app = require_once __DIR__.'/../bootstrap/app.php';
19
20$app->handleRequest(Request::capture());
21
1 vendor frame

Queries
1-1 of 1
mysql
select * from `users` where `id` = 1 limit 1
2.74ms
Headers
host
127.0.0.1:8000
connection
keep-alive
pragma
no-cache
cache-control
no-cache
sec-ch-ua
"Chromium";v="142", "Google Chrome";v="142", "Not_A Brand";v="99"
sec-ch-ua-mobile
?0
sec-ch-ua-platform
"Windows"
upgrade-insecure-requests
1
user-agent
Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36
accept
text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
sec-fetch-site
same-origin
sec-fetch-mode
navigate
sec-fetch-user
?1
sec-fetch-dest
document
referer
http://127.0.0.1:8000/generate-certificates
accept-encoding
gzip, deflate, br, zstd
accept-language
en-US,en;q=0.9
cookie
__stripe_mid=4fac6ec2-f0da-48bd-9259-25ffe6443cdf8ee442; XSRF-TOKEN=eyJpdiI6ImN5SXkvUjBFQjZMZFMxUHk1ekhNNHc9PSIsInZhbHVlIjoiM1hyaEp1MmhiVFcwVkMzbXY5Qzl3dlMxbHF5cHp0RGs4Umpqam4vK0J5U3hGekorYm5JTGlRbW5EVFpIeWFUdTllSGhod2ppZUxFVUNMMjJoV1ArL1R3akhIY2NMVVdXY3M3bldxcTE3RTRobkpVVG0rNTBYYXEvVklhQ0hTYTEiLCJtYWMiOiJiZjk3YmFkMmE3YjViZDdhMmI4NTZjODBlMWQwZmQyZGMyYzc1NzJmMWMzNWFjM2U5ODExZGI5YTdhM2E0NDRkIiwidGFnIjoiIn0%3D; laravel-session=eyJpdiI6ImpEVnZpMUJpTk5ISU5qbG83dThhanc9PSIsInZhbHVlIjoiN3hTbC9xMHZYMHljcWVITDVYN0xUK0lpaDhJRUltQjQrbkJxUkJnaFZodUV0NlFlTDFqTE5DQWx4bFlaMXVGV3U1ZnZrRkVkb0FqaHgvbUZzcUVkSDd2QlJ0OU13RXE0MzdDQmRnbXpDdXVrUnRKRjQzQlQ3ZW1zTnc3Q25ZcHkiLCJtYWMiOiI5YmY2ZmMxYjQ1MWNkZjg2ZWFkODExMTg4ZTBhYmQ4ZDJjNjZkNmQ4ZmNiZDdhMGNlZmY3YTMyYWQ0NDVjNDA3IiwidGFnIjoiIn0%3D
Body
// No request body
Routing
controller
Closure
middleware
web, auth, check.course.review
Routing parameters
{
    "enrollment_id": "1"
}
