# Third-Party OAuth2 API Integration Guide

## Complete Workflow: Getting Public Lists from Aspen Discovery

This guide shows how a third-party application can use OAuth2 Client Credentials Grant to access Aspen Discovery's public APIs.

### Step 1: Register Your Application

#### Option A: Via Admin Interface (Recommended)

1. Admin logs into Aspen Discovery
2. Go to **System Administration > OAuth2 Clients**
3. Click **Generate New Client**
4. Fill in details:
   - **Client Name**: "MyApp Public API Client"
   - **Client Type**: "Service/API Client (Client Credentials)"
   - **Allowed Scopes**: Select `public:lists:read`, `public:catalog:read`, `public:locations:read`
   - **Redirect URI**: (leave blank for service applications)
5. Save and note the `client_id` and `client_secret`

#### Option B: Programmatic Registration

```bash
curl -X POST https://library.example.com/OAuth2/Register \
  -H "Content-Type: application/json" \
  -d '{
    "client_name": "MyApp Public API Client",
    "client_type": "service_application",
    "scopes": ["public:lists:read", "public:catalog:read", "public:locations:read"]
  }'
```

Response:
```json
{
  "client_id": "aspen_abc123def456",
  "client_secret": "xyz789uvw012",
  "client_name": "MyApp Public API Client",
  "client_type": "service_application",
  "scopes": ["public:lists:read", "public:catalog:read", "public:locations:read"],
  "token_endpoint": "https://library.example.com/OAuth2/Token",
  "authorization_endpoint": "https://library.example.com/OAuth2/Authorize"
}
```

### Step 2: Get Access Token

Use the client credentials grant to get an access token:

```bash
curl -X POST https://library.example.com/OAuth2/Token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials&client_id=aspen_abc123def456&client_secret=xyz789uvw012&scope=public:lists:read+public:catalog:read"
```

Response:
```json
{
  "token_type": "Bearer",
  "expires_in": 14400,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "scope": "public:lists:read public:catalog:read"
}
```

### Step 3: Use the Access Token to Call APIs

#### Get All Public Lists

```bash
curl -X GET https://library.example.com/OAuth2/PublicAPI?method=getPublicLists \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
```

Response:
```json
{
  "result": {
    "totalResults": 5,
    "lists": [
      {
        "id": "123",
        "title": "Summer Reading Recommendations",
        "description": "Great books for summer reading",
        "created": "2026-01-15 10:30:00",
        "lastUpdated": "2026-03-01 14:20:00",
        "numTitles": 25,
        "owner": "Library Staff",
        "url": "/MyAccount/MyList/123"
      },
      {
        "id": "456", 
        "title": "New Fiction Highlights",
        "description": "Recently added fiction titles",
        "created": "2026-02-20 09:15:00",
        "lastUpdated": "2026-03-15 16:45:00",
        "numTitles": 18,
        "owner": "Collection Development",
        "url": "/MyAccount/MyList/456"
      }
    ]
  }
}
```

#### Get Specific List Details

```bash
curl -X GET "https://library.example.com/OAuth2/PublicAPI?method=getPublicList&listId=123" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
```

Response:
```json
{
  "result": {
    "id": "123",
    "title": "Summer Reading Recommendations", 
    "description": "Great books for summer reading",
    "created": "2026-01-15 10:30:00",
    "lastUpdated": "2026-03-01 14:20:00",
    "owner": "Library Staff",
    "numTitles": 2,
    "titles": [
      {
        "id": "1",
        "source": "ils",
        "sourceId": "b12345678",
        "title": "The Amazing Book",
        "author": "Jane Author",
        "dateAdded": "2026-01-15 10:30:00",
        "notes": "Highly recommended!"
      },
      {
        "id": "2", 
        "source": "overdrive",
        "sourceId": "od67890123",
        "title": "Digital Stories",
        "author": "Bob Writer",
        "dateAdded": "2026-02-01 15:20:00",
        "notes": ""
      }
    ]
  }
}
```

#### Search Public Catalog

```bash
curl -X GET "https://library.example.com/OAuth2/PublicAPI?method=searchCatalog&q=dogs&limit=5" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
```

Response:
```json
{
  "result": {
    "query": "dogs",
    "totalResults": 152,
    "page": 1,
    "limit": 5,
    "records": [
      {
        "id": "grouped_work_123",
        "title": "Training Your Dog", 
        "author": "Dog Expert",
        "isbn": ["9781234567890"],
        "format": ["Book"],
        "publicationDate": "2025",
        "language": ["English"],
        "summary": "A comprehensive guide to dog training..."
      }
    ]
  }
}
```

#### Get Library Locations

```bash
curl -X GET https://library.example.com/OAuth2/PublicAPI?method=getLibraryLocations \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
```

Response:
```json
{
  "result": {
    "totalResults": 3,
    "locations": [
      {
        "id": "1",
        "code": "main",
        "displayName": "Main Library",
        "address": "123 Library St, City, ST 12345",
        "phone": "(555) 123-4567",
        "email": "info@library.example.com",
        "hours": {
          "monday": {"open": "09:00", "close": "21:00"},
          "tuesday": {"open": "09:00", "close": "21:00"},
          "wednesday": {"open": "09:00", "close": "21:00"},
          "thursday": {"open": "09:00", "close": "21:00"},
          "friday": {"open": "09:00", "close": "18:00"},
          "saturday": {"open": "10:00", "close": "17:00"},
          "sunday": {"open": "13:00", "close": "17:00"}
        },
        "latitude": "40.123456",
        "longitude": "-74.123456"
      }
    ]
  }
}
```

## JavaScript/Node.js Example

```javascript
class AspenDiscoveryAPI {
  constructor(baseUrl, clientId, clientSecret) {
    this.baseUrl = baseUrl;
    this.clientId = clientId;
    this.clientSecret = clientSecret;
    this.accessToken = null;
    this.tokenExpiry = null;
  }

  async getAccessToken() {
    // Check if we have a valid token
    if (this.accessToken && this.tokenExpiry > Date.now()) {
      return this.accessToken;
    }

    // Request new token
    const response = await fetch(`${this.baseUrl}/OAuth2/Token`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        grant_type: 'client_credentials',
        client_id: this.clientId,
        client_secret: this.clientSecret,
        scope: 'public:lists:read public:catalog:read public:locations:read'
      })
    });

    if (!response.ok) {
      throw new Error(`Token request failed: ${response.statusText}`);
    }

    const tokenData = await response.json();
    this.accessToken = tokenData.access_token;
    this.tokenExpiry = Date.now() + (tokenData.expires_in * 1000) - 60000; // 1 min buffer

    return this.accessToken;
  }

  async apiRequest(method, params = {}) {
    const token = await this.getAccessToken();
    
    const url = new URL(`${this.baseUrl}/OAuth2/PublicAPI`);
    url.searchParams.set('method', method);
    
    // Add additional parameters
    Object.entries(params).forEach(([key, value]) => {
      url.searchParams.set(key, value);
    });

    const response = await fetch(url, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });

    if (!response.ok) {
      throw new Error(`API request failed: ${response.statusText}`);
    }

    const data = await response.json();
    return data.result;
  }

  async getPublicLists() {
    return this.apiRequest('getPublicLists');
  }

  async getPublicList(listId) {
    return this.apiRequest('getPublicList', { listId });
  }

  async searchCatalog(query, page = 1, limit = 20) {
    return this.apiRequest('searchCatalog', { q: query, page, limit });
  }

  async getLibraryLocations() {
    return this.apiRequest('getLibraryLocations');
  }
}

// Usage
const api = new AspenDiscoveryAPI(
  'https://library.example.com',
  'your_client_id',
  'your_client_secret'
);

// Get public lists
const lists = await api.getPublicLists();
console.log(`Found ${lists.totalResults} public lists`);

// Get specific list
const list = await api.getPublicList('123');
console.log(`List "${list.title}" has ${list.numTitles} titles`);

// Search catalog
const results = await api.searchCatalog('dogs', 1, 10);
console.log(`Found ${results.totalResults} results for "dogs"`);
```

## Python Example

```python
import requests
import time
from datetime import datetime, timedelta

class AspenDiscoveryAPI:
    def __init__(self, base_url, client_id, client_secret):
        self.base_url = base_url
        self.client_id = client_id
        self.client_secret = client_secret
        self.access_token = None
        self.token_expiry = None

    def get_access_token(self):
        # Check if we have a valid token
        if self.access_token and self.token_expiry > datetime.now():
            return self.access_token

        # Request new token
        response = requests.post(f"{self.base_url}/OAuth2/Token", data={
            'grant_type': 'client_credentials',
            'client_id': self.client_id,
            'client_secret': self.client_secret,
            'scope': 'public:lists:read public:catalog:read public:locations:read'
        })

        if not response.ok:
            raise Exception(f"Token request failed: {response.text}")

        token_data = response.json()
        self.access_token = token_data['access_token']
        self.token_expiry = datetime.now() + timedelta(seconds=token_data['expires_in'] - 60)

        return self.access_token

    def api_request(self, method, **params):
        token = self.get_access_token()
        
        url = f"{self.base_url}/OAuth2/PublicAPI"
        params['method'] = method
        
        response = requests.get(url, params=params, headers={
            'Authorization': f'Bearer {token}'
        })

        if not response.ok:
            raise Exception(f"API request failed: {response.text}")

        return response.json()['result']

    def get_public_lists(self):
        return self.api_request('getPublicLists')

    def get_public_list(self, list_id):
        return self.api_request('getPublicList', listId=list_id)

    def search_catalog(self, query, page=1, limit=20):
        return self.api_request('searchCatalog', q=query, page=page, limit=limit)

    def get_library_locations(self):
        return self.api_request('getLibraryLocations')

# Usage
api = AspenDiscoveryAPI(
    'https://library.example.com',
    'your_client_id', 
    'your_client_secret'
)

# Get public lists
lists = api.get_public_lists()
print(f"Found {lists['totalResults']} public lists")

# Get specific list
list_detail = api.get_public_list('123')
print(f"List '{list_detail['title']}' has {list_detail['numTitles']} titles")

# Search catalog
results = api.search_catalog('dogs', limit=10)
print(f"Found {results['totalResults']} results for 'dogs'")
```

## Error Handling

The API follows OAuth2 standard error responses:

```json
{
  "error": "invalid_scope",
  "error_description": "The requested scope is invalid, unknown, or malformed"
}
```

Common client credentials errors:
- `invalid_client` - Invalid client_id or client_secret
- `invalid_scope` - Requested scope not allowed for this client
- `unsupported_grant_type` - Client not configured for client_credentials grant

## Rate Limiting & Best Practices

1. **Token Caching**: Cache access tokens until they expire (4 hours for client credentials)
2. **Error Handling**: Implement retry logic for network failures
3. **Scope Management**: Only request the minimum scopes needed
4. **Security**: Keep client secrets secure and never expose them in client-side code

This workflow allows third-party applications to access Aspen Discovery's public data without requiring user authentication, making it perfect for mobile apps, websites, or data integration tools that need to display library information.
