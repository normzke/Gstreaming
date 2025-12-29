-- Create remember_tokens table for persistent login sessions
CREATE TABLE IF NOT EXISTS remember_tokens (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    token VARCHAR(64) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for better performance
CREATE INDEX IF NOT EXISTS idx_remember_tokens_token ON remember_tokens(token);
CREATE INDEX IF NOT EXISTS idx_remember_tokens_user_id ON remember_tokens(user_id);
CREATE INDEX IF NOT EXISTS idx_remember_tokens_expires_at ON remember_tokens(expires_at);

-- Insert some sample data for testing
-- Note: These tokens are for demonstration only and should be removed in production
