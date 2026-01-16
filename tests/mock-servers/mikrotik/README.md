# Mock MikroTik API Server

This is a simple Flask-based mock server that simulates MikroTik RouterOS API for integration testing.

## Endpoints

- `POST /api/ppp/secret/add` - Create PPPoE user
- `POST /api/ppp/secret/set` - Update PPPoE user
- `POST /api/ppp/secret/remove` - Remove PPPoE user
- `GET /api/ppp/secret/print` - List PPPoE users
- `GET /api/ppp/active/print` - List active sessions
- `POST /api/ppp/active/remove` - Disconnect session
- `POST /api/test/create_session` - Create test session
- `POST /api/test/reset` - Reset all data

## Usage

```bash
python server.py
```

The server will start on port 8728.
