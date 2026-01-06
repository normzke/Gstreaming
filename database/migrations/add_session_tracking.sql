-- Device/Session Tracking for BingeTV
-- Prevents multiple simultaneous logins beyond package limits

-- Create user_sessions table
CREATE TABLE IF NOT EXISTS user_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    device_id VARCHAR(255) NOT NULL,
    device_name VARCHAR(255),
    device_type VARCHAR(50), -- 'web', 'android', 'tizen', 'webos'
    ip_address VARCHAR(45),
    user_agent TEXT,
    last_activity TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT true
);

-- Create indexes for performance
CREATE INDEX idx_user_sessions_user_id ON user_sessions(user_id);
CREATE INDEX idx_user_sessions_token ON user_sessions(session_token);
CREATE INDEX idx_user_sessions_active ON user_sessions(is_active, last_activity);
CREATE INDEX idx_user_sessions_device ON user_sessions(device_id);

-- Create function to clean expired sessions
CREATE OR REPLACE FUNCTION clean_expired_sessions()
RETURNS INTEGER AS $$
DECLARE
    deleted_count INTEGER;
BEGIN
    DELETE FROM user_sessions 
    WHERE expires_at < NOW() OR last_activity < NOW() - INTERVAL '24 hours';
    
    GET DIAGNOSTICS deleted_count = ROW_COUNT;
    RETURN deleted_count;
END;
$$ LANGUAGE plpgsql;

-- Create function to count active sessions for a user
CREATE OR REPLACE FUNCTION count_active_sessions(p_user_id INTEGER)
RETURNS INTEGER AS $$
DECLARE
    session_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO session_count
    FROM user_sessions
    WHERE user_id = p_user_id
      AND is_active = true
      AND expires_at > NOW()
      AND last_activity > NOW() - INTERVAL '30 minutes';
    
    RETURN session_count;
END;
$$ LANGUAGE plpgsql;

-- Add comments
COMMENT ON TABLE user_sessions IS 'Tracks active user sessions across all devices to enforce device limits';
COMMENT ON COLUMN user_sessions.session_token IS 'Unique session identifier for authentication';
COMMENT ON COLUMN user_sessions.device_id IS 'Unique device identifier (MAC address, UUID, or fingerprint)';
COMMENT ON COLUMN user_sessions.last_activity IS 'Last API call or heartbeat from this session';
