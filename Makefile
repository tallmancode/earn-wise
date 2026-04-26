.PHONY: up down seed test shell logs

up:
	docker compose up --build -d

down:
	docker compose down

seed:
	docker compose exec app php artisan migrate:fresh --seed

test:
	docker compose exec app vendor/bin/pest

shell:
	docker compose exec app bash

logs:
	docker compose logs -f app
