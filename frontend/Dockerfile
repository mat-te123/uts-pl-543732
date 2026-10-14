# ── STAGE 1: Build Static Assets ──────────────────────────────────────────
FROM node:24-alpine AS build-stage

WORKDIR /app

# Copy package manifests first to leverage Docker layer caching
COPY package*.json ./

# Use npm ci for clean, reliable production dependency installation
RUN npm ci

# Copy source code and build the production bundle
COPY . .

ARG VITE_API_URL
ENV VITE_API_URL=$VITE_API_URL

RUN npm run build

# ── STAGE 2: Production Server (Nginx) ────────────────────────────────────
FROM nginx:1.29-alpine AS production-stage

# Copy built static files from Stage 1
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Copy custom Nginx configuration to prevent Vue Router 404 errors on refresh
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]