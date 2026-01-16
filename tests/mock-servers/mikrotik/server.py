#!/usr/bin/env python3
"""
Mock MikroTik API Server for Integration Testing
This is a simple HTTP server that mimics MikroTik RouterOS API responses

WARNING: This mock server uses global mutable state (pppoe_users, active_sessions)
which may cause issues in concurrent testing scenarios. For parallel test execution,
consider implementing proper request-scoped state management or using locks.
"""

from flask import Flask, request, jsonify
from datetime import datetime
import os

app = Flask(__name__)

# In-memory storage for testing
# NOTE: Global state - not safe for concurrent access
pppoe_users = {}
active_sessions = {}
session_counter = 0

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({'status': 'ok', 'service': 'mock-mikrotik'})

@app.route('/api/ppp/secret/add', methods=['POST'])
def add_pppoe_user():
    """Create a new PPPoE user"""
    data = request.json
    username = data.get('name')
    
    if not username:
        return jsonify({'error': 'Username required'}), 400
    
    if username in pppoe_users:
        return jsonify({'error': 'User already exists'}), 409
    
    pppoe_users[username] = {
        'name': username,
        'password': data.get('password', ''),
        'service': data.get('service', 'pppoe'),
        'profile': data.get('profile', 'default'),
        'local-address': data.get('local-address', ''),
        'remote-address': data.get('remote-address', ''),
        'disabled': data.get('disabled', 'no'),
        'created_at': datetime.now().isoformat()
    }
    
    return jsonify({
        'success': True,
        'id': username,
        'user': pppoe_users[username]
    }), 201

@app.route('/api/ppp/secret/set', methods=['POST'])
def update_pppoe_user():
    """Update an existing PPPoE user"""
    data = request.json
    username = data.get('name')
    
    if not username or username not in pppoe_users:
        return jsonify({'error': 'User not found'}), 404
    
    # Update user fields
    for key in ['password', 'service', 'profile', 'local-address', 'remote-address', 'disabled']:
        if key in data:
            pppoe_users[username][key] = data[key]
    
    return jsonify({
        'success': True,
        'user': pppoe_users[username]
    })

@app.route('/api/ppp/secret/remove', methods=['POST'])
def remove_pppoe_user():
    """Remove a PPPoE user"""
    data = request.json
    username = data.get('name')
    
    if not username or username not in pppoe_users:
        return jsonify({'error': 'User not found'}), 404
    
    del pppoe_users[username]
    
    return jsonify({
        'success': True,
        'message': 'User removed'
    })

@app.route('/api/ppp/secret/print', methods=['GET'])
def list_pppoe_users():
    """List all PPPoE users"""
    return jsonify({
        'success': True,
        'users': list(pppoe_users.values())
    })

@app.route('/api/ppp/active/print', methods=['GET'])
def list_active_sessions():
    """List active PPPoE sessions"""
    return jsonify({
        'success': True,
        'sessions': list(active_sessions.values())
    })

@app.route('/api/ppp/active/remove', methods=['POST'])
def disconnect_session():
    """Disconnect a PPPoE session"""
    data = request.json
    session_id = data.get('id')
    
    if not session_id or session_id not in active_sessions:
        return jsonify({'error': 'Session not found'}), 404
    
    del active_sessions[session_id]
    
    return jsonify({
        'success': True,
        'message': 'Session disconnected'
    })

@app.route('/api/test/create_session', methods=['POST'])
def create_test_session():
    """Create a test session (for testing purposes only)"""
    global session_counter
    data = request.json
    username = data.get('username')
    
    if not username:
        return jsonify({'error': 'Username required'}), 400
    
    session_counter += 1
    session_id = f"*{session_counter:X}"
    
    active_sessions[session_id] = {
        'id': session_id,
        'name': username,
        'service': 'pppoe',
        'caller-id': data.get('caller-id', '00:00:00:00:00:00'),
        'address': data.get('address', '10.0.0.1'),
        'uptime': '0s',
        'encoding': '',
        'session-id': data.get('session-id', f'0x{session_counter:08X}'),
        'limit-bytes-in': data.get('limit-bytes-in', 0),
        'limit-bytes-out': data.get('limit-bytes-out', 0),
        'radius': 'true' if data.get('radius', False) else 'false'
    }
    
    return jsonify({
        'success': True,
        'session': active_sessions[session_id]
    }), 201

@app.route('/api/test/reset', methods=['POST'])
def reset_data():
    """Reset all test data"""
    global pppoe_users, active_sessions, session_counter
    pppoe_users = {}
    active_sessions = {}
    session_counter = 0
    
    return jsonify({
        'success': True,
        'message': 'All data reset'
    })

if __name__ == '__main__':
    print("Mock MikroTik API Server starting on port 8728...")
    print("Available endpoints:")
    print("  - POST /api/ppp/secret/add - Create PPPoE user")
    print("  - POST /api/ppp/secret/set - Update PPPoE user")
    print("  - POST /api/ppp/secret/remove - Remove PPPoE user")
    print("  - GET  /api/ppp/secret/print - List PPPoE users")
    print("  - GET  /api/ppp/active/print - List active sessions")
    print("  - POST /api/ppp/active/remove - Disconnect session")
    print("  - POST /api/test/create_session - Create test session")
    print("  - POST /api/test/reset - Reset all data")
    
    # Get debug mode from environment variable (default to False for security)
    debug_mode = os.getenv('FLASK_DEBUG', 'false').lower() in ('true', '1', 'yes')
    print(f"Debug mode: {'enabled' if debug_mode else 'disabled'}")
    
    app.run(host='0.0.0.0', port=8728, debug=debug_mode)
