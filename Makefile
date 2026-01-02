# --------------------------------------
# Zmienne
# --------------------------------------
COMPOSE=docker compose
BACKEND_DIR=./backend/symfony
FRONTEND_DIR=./frontend
MAKE= make

dev:
	@echo "Budowanie obrazów i uruchamianie wszystkich kontenerów w tle..."
	docker compose build
	docker compose up -d
	@echo "Frontend działa w kontenerze na http://localhost:3000"
	@echo "Worker działa w tle, backend działa w tle"

run:
	@echo "Uruchamianie istniejących kontenerów w tle..."
	docker compose up -d
	@echo "Frontend działa w kontenerze na http://localhost:3000"
	@echo "Worker działa w tle, backend działa w tle"

stop:
	@echo "Zatrzymywanie wszystkich kontenerów..."
	docker compose down

restart:
	$(MAKE) stop
	$(MAKE) run

frontend-install:
	@echo "Instalowanie zależności frontendu..."
	cd $(FRONTEND_DIR) && npm install

frontend-dev:
	@echo "Uruchamianie frontendu w trybie deweloperskim..."
	cd $(FRONTEND_DIR) && npm run dev

frontend-build:
	@echo "Budowanie frontendu..."
	cd $(FRONTEND_DIR) && npm run build

frontend-start:
	@echo "Uruchamianie frontendu w trybie produkcyjnym..."
	cd $(FRONTEND_DIR) && npm start