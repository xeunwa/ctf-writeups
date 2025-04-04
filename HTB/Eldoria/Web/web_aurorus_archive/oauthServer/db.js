const bcrypt = require('bcrypt');

class DB {
  constructor() {
    this.clients = [];
    this.accessTokens = new Map();
    this.refreshTokens = new Map();
    this.authorizationCodes = new Map(); // To store authorization codes
    this.users = []; // To store user data

    // Automatically add an admin user if ADMIN_PASSWORD is defined
    if (process.env.ADMIN_PASSWORD) {
      // Check if an admin user already exists before adding (since saveUser hashes the password)
      const existingAdmin = this.findUserByUsername("admin");
      if (!existingAdmin) {
        this.saveUser({ username: "admin", password: process.env.ADMIN_PASSWORD })
          .then(user => {
            console.log("Admin user added:", user);
          })
          .catch(err => {
            console.error("Error adding admin user:", err);
          });
      }
    }
  }

  saveClient(client) {
    this.clients.push(client);
    return client;
  }

  findClient(clientId, clientSecret) {
    return this.clients.find(client => {
      if (clientSecret) {
        return client.id === clientId && client.secret === clientSecret;
      } else {
        return client.id === clientId;
      }
    });
  }

  findClientById(id) {
    return this.clients.find(client => client.id === id);
  }

  saveAccessToken(accessToken, meta) {
    this.accessTokens.set(accessToken, meta);
  }

  findAccessToken(accessToken) {
    console.log(this.accessTokens);
    return this.accessTokens.get(accessToken);
  }

  deleteAccessToken(accessToken) {
    this.accessTokens.delete(accessToken);
  }

  saveRefreshToken(refreshToken, meta) {
    this.refreshTokens.set(refreshToken, meta);
  }

  findRefreshToken(refreshToken) {
    return this.refreshTokens.get(refreshToken);
  }

  deleteRefreshToken(refreshToken) {
    this.refreshTokens.delete(refreshToken);
  }

  // Authorization Code Methods
  saveAuthorizationCode(code, client, user, scope) {
    const expiresAt = new Date(Date.now() + 10 * 60 * 1000); // 10 minutes validity
    this.authorizationCodes.set(code, { client, user, scope, expiresAt });
  }

  findAuthorizationCode(code) {
    const authCode = this.authorizationCodes.get(code);
    if (!authCode) return null;
    if (authCode.expiresAt < new Date()) {
      this.authorizationCodes.delete(code);
      return null;
    }
    return authCode;
  }

  deleteAuthorizationCode(code) {
    this.authorizationCodes.delete(code);
  }

  // User-related methods

  // saveUser hashes the provided password before storing the user
  async saveUser(user) {
    const hashedPassword = await bcrypt.hash(user.password, 10);
    const newUser = {
      id: this.users.length + 1,
      username: user.username,
      password: hashedPassword
    };
    this.users.push(newUser);
    return newUser;
  }

  findUserByUsername(username) {
    return this.users.find(user => user.username === username);
  }

  findUserById(id) {
    return this.users.find(user => user.id === id);
  }
}

module.exports = DB;
