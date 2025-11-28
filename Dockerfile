FROM node:20-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci 
COPY . .
# Asegúrate de pasar la URL de producción aquí
ENV PUBLIC_BACKEND_URL=https://tu-dominio.com/api
RUN npm run build

FROM nginx:alpine
COPY --from=builder /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]