# makefiles ftw!

test:
	php -S 0.0.0.0:8080

docker:
	docker compose up -d --build

sync:
	node sync-i18n.js
