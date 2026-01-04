FROM node:20-alpine

WORKDIR /app

# Kopiuj pliki package do instalacji zależności
COPY package.json package-lock.json* ./

# Instalacja zależności
RUN npm ci

# Kopiuj resztę plików
COPY . .

# Budowanie aplikacji (opcjonalne - można wyłączyć dla dev)
# RUN npm run build

# Entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["npm", "run", "dev"]

