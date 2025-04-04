// model.js
const bcrypt = require('bcrypt');
const enabledScopes = ['read', 'write'];
const getUserDoc = (user) => ({ id: user.id, username: user.username });

function createModel(db) {
  async function getClient(clientId, clientSecret) {
    return db.findClient(clientId, clientSecret);
  }

  async function validateScope(user, client, scope) {
    if (!user) {
      return false;
    }

    if (!client || !db.findClientById(client.id)) {
      return false;
    }

    if (typeof scope === 'string') {
      return enabledScopes.includes(scope) ? [scope] : false;
    } else {
      return scope.every(s => enabledScopes.includes(s)) ? scope : false;
    }
  }

  async function getUserFromClient(client) {
    // For client_credentials grant, associate with a system user
    return { id: 'system', username: 'system' };
  }

  async function getUser(username, password) {
    const user = db.findUserByUsername(username);
    if (!user) return null;
    const match = await bcrypt.compare(password, user.password);
    if (match) {
      return getUserDoc(user);
    }
    return null;
  }

  async function saveToken(token, client, user) {
    const meta = {
      clientId: client.id,
      userId: user.id,
      scope: token.scope,
      accessTokenExpiresAt: token.accessTokenExpiresAt,
      refreshTokenExpiresAt: token.refreshTokenExpiresAt
    };

    token.client = client;
    token.user = user;

    if (token.accessToken) {
      db.saveAccessToken(token.accessToken, meta);
    }

    if (token.refreshToken) {
      db.saveRefreshToken(token.refreshToken, meta);
    }

    return token;
  }

  async function getAccessToken(accessToken) {
    const meta = db.findAccessToken(accessToken);

    if (!meta) {
      return false;
    }

    const user = meta.userId === 'system' ? getUserDoc({ id: 'system', username: 'system' }) : db.findUserById(meta.userId);
    return {
      accessToken,
      accessTokenExpiresAt: meta.accessTokenExpiresAt,
      user: user,
      client: db.findClientById(meta.clientId),
      scope: meta.scope
    };
  }

  async function getRefreshToken(refreshToken) {
    const meta = db.findRefreshToken(refreshToken);

    if (!meta) {
      return false;
    }

    const user = meta.userId === 'system' ? getUserDoc({ id: 'system', username: 'system' }) : db.findUserById(meta.userId);
    return {
      refreshToken,
      refreshTokenExpiresAt: meta.refreshTokenExpiresAt,
      user: user,
      client: db.findClientById(meta.clientId),
      scope: meta.scope
    };
  }

  async function revokeToken(token) {
    db.deleteRefreshToken(token.refreshToken);
    return true;
  }

  async function verifyScope(token, scope) {
    if (!token.scope) {
      console.log('Token has no scopes.');
      return false;
    }
  
    const requestedScopes = typeof scope === 'string' ? scope.split(' ') : scope;
    const tokenScopes = typeof token.scope === 'string' ? token.scope.split(' ') : token.scope;
  
    console.log('Requested Scopes:', requestedScopes);
    console.log('Token Scopes:', tokenScopes);
  
    const isWithinEnabledScopes = requestedScopes.every(s => enabledScopes.includes(s));
    console.log('Are all requested scopes within enabled scopes?', isWithinEnabledScopes);
    if (!isWithinEnabledScopes) return false;
  
    const clientAllowedScopes = token.client.allowedScopes || [];
    const isWithinClientAllowedScopes = requestedScopes.every(s => clientAllowedScopes.includes(s));
    console.log('Are all requested scopes within client\'s allowed scopes?', isWithinClientAllowedScopes);
    if (!isWithinClientAllowedScopes) return false;
  
    const tokenHasAllScopes = requestedScopes.every(s => tokenScopes.includes(s));
    console.log('Does the token include all requested scopes?', tokenHasAllScopes);
    if (!tokenHasAllScopes) return false;
  
    return true;
  }
  

  // Authorization Code Grant Methods
  async function getAuthorizationCode(code) {
    const authCode = db.findAuthorizationCode(code);
    if (!authCode) {
      return null;
    }

    const client = authCode.client;
    const user = authCode.user;
    const scope = authCode.scope;

    return {
      code,
      client,
      user,
      scope,
      expiresAt: authCode.expiresAt
    };
  }

  async function saveAuthorizationCode(code, client, user, scope) {
    db.saveAuthorizationCode(code, client, user, scope);
    return {
      authorizationCode: code,
      client: client,
      user: user,
      scope: scope,
      expiresAt: new Date(Date.now() + 10 * 60 * 1000) // 10 minutes
    };
  }

  async function revokeAuthorizationCode(code) {
    db.deleteAuthorizationCode(code);
    return true;
  }

  return {
    getClient,
    saveToken,
    getAccessToken,
    getRefreshToken,
    revokeToken,
    validateScope,
    verifyScope,
    getUserFromClient,
    getUser, // For ROPC
    getAuthorizationCode, // New
    saveAuthorizationCode, // New
    revokeAuthorizationCode // New
  };
}

module.exports = createModel;
