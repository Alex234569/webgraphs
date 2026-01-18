# Stage 1: Build assets
FROM node:22-alpine as build-stage
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Nginx
FROM nginx:alpine
COPY k8s/docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY public /var/www/public
COPY --from=build-stage /app/public/build /var/www/public/build

RUN rm -f /var/www/public/hot

EXPOSE 80
