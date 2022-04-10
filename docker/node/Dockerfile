FROM node:16.13.0

WORKDIR /usr/src/

RUN ["yarn", "install"]

ENV PATH="/usr/src/node_modules/.bin:${PATH}"

CMD ["yarn", "watch"]
