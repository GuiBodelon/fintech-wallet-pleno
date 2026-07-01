FROM node:24-alpine

WORKDIR /app

RUN corepack enable

COPY package.json pnpm-lock.yaml* ./

EXPOSE 3000

CMD ["sh", "-c", "pnpm install && pnpm dev"]
