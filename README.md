# Unifetch-Website

UniFetch is a food ordering and errand services web application for the University of Cebu Lapu-Lapu and Mandaue (UCLM).

Hello everyone! UniFetch is a web-based platform developed as part of our school project.

## Members
- Cabaltera, Jc
- Segara, Renier
- Velez, Johnlee Nino
- Velasquez, Charles Louis

## How to run the project
1. git clone https://github.com/Needhelp13ss/Unifetch-Website.git
2. cd Unifetch-Website
3. composer install
4. copy .env.example .env
5. php artisan key:generate
6. php artisan serve
7. Open http://127.0.0.1:8000/unifetch

## Update 6: pricing, proofs, chat, shop accounts, reports

- Pricing (`app/Support/Pricing.php`): runner fee = ₱7 + ₱2 for every full 25 m between the shop and the drop-off; service fee ₱3 per order (UniFetch's profit).
- Campus places and coordinates: Admin > Menu > Campus places (the coordinates are placeholders, edit them).
- Photo proofs (shop, runner pickup, runner delivery, customer received), proof of delivery, History, and customer-runner chat.
- Shop accounts: Admin > Menu > open a vendor > "Shop account". The seller logs in at /login and lands on /shop.
- Admin > Reports: charts (active/inactive users, orders, profit, status, top shops, new users, top runners).
- After pulling: `php artisan migrate`, then `php artisan serve`.
