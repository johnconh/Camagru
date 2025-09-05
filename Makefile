.PHONY: up all down re clean

all: up

up:
	docker compose up -d

down:
	docker compose down

re: down up

clean: down
	yes | docker system prune -a