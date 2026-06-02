# makefiles ftw!

test:
	php -S 0.0.0.0:8080

docker:
	docker compose up -d --build

jsSync:
	node sync-i18n.js

pySync:
	python3 sync-i18n.py