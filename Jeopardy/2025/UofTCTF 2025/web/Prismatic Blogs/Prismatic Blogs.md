---
date: 2025-01-13
description: ORM boolean-based injection probably?
platform: UofTCTF 2025
categories: Web
tags:
  - code-review
  - prisma
  - orm
duration: unsolved
---
# Prismatic Blogs
> Let's look at Prisma, which is a Node.js & TypeScript ORM & toolkit to interact with your database.

## schema.prisma
useful fields user one to many post

User
- name
- password
- posts
Post
- title
- body
- author
- authorId

> This Prisma schema will generate a SQLite database with two tables: `User` and `Post`, with the specified relationships between them. After running `prisma migrate` to apply the schema, you can interact with this data using the Prisma Client in your application.

## package.json
```json
{
  "name": "blog-api",
  "version": "1.0.0",
  "prisma": {
    "seed": "node seed.js"
  },
  "dependencies": {
    "@prisma/client": "^6.1.0", // 6.2.1 latest
    "express": "^4.21.2", // 4.21.2
    "prisma": "^6.1.0"
  }
}
```

> **prisma.seed**: This field specifies a custom script for seeding your database. It indicates that the command `node seed.js` should be run to populate your database with initial data. This is useful when you want to automate data population during development or deployment. The `seed.js` file is typically located in your project root or another folder you specify.

## seed.js
Sets the seed to populate the db. 
- flag initialized
- create 4 users with 4 random passwords 

## Init
```bash
npm install
npx prisma migrate dev --name init

# check if database db initialized with contents 
node index.js
```

boolean-based nosql? 
# Solution ?
> figure we can query author details in the `/api/post` endpoint with a query return post if filter match

boolean-based approach
```sql
/api/post?author[name]=White&author[password][startsWith]=j -- shows post
/api/post?author[name]=White&author[password][startsWith]=w -- fail
```

payloads
```test
author[name][startsWith]=a
author[name]=White
author[password][startsWith]=j

author[name]=White&author[password][startsWith]=j
author[name]=White&author[password][contains]=j
author[name]={user}&author[password]=match
```
> matches the password but the cases are wrong 

Code requirements
- `ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`
- min password is 15-24 start validating when length 
- be able to find a user matching bruteforce every user 

---
# Road block
my plan was to fuzz the input but i learned that matching the password but SQLite prisma does case-insensitive matching that prevents reading the password from fast bruteforce
https://www.prisma.io/docs/orm/prisma-client/queries/case-sensitivity

![[_attachments/Pasted image 20250113033907.png]]

https://www.sqlite.org/faq.html#q18
> **(18) Case-insensitive matching of Unicode characters does not work.**

> The default configuration of SQLite only supports case-insensitive comparisons of ASCII characters. The reason for this is that doing full Unicode case-insensitive comparisons and case conversions requires tables and logic that would nearly double the size of the SQLite library. The SQLite developers reason that any application that needs full Unicode case support probably already has the necessary tables and functions and so SQLite should not take up space to duplicate this ability.


# Notes 
- `req.query` extracts from `author[name]` and can be passed as object
- match tricks in prisma https://www.prisma.io/docs/orm/prisma-client/queries/filtering-and-sorting#filter-on-relations
- sqlite case-insensitive matching `SELECT * FROM User WHERE password LIKE 'J%';`