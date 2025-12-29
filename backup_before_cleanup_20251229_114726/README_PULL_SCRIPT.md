# Pull from Remote Scripts

## Overview

Two scripts are available to pull files from the remote Bluehost server:

1. **PULL_FROM_REMOTE.sh** - Full pull with backup
2. **scripts/pull-from-remote.sh** - Selective pull by directory

## Usage

### Full Pull (Recommended First Time)

```bash
cd ~/Downloads/BingeTV
bash PULL_FROM_REMOTE.sh
```

**Features:**
- Creates automatic backup before pulling
- Pulls all files from remote
- Searches for IbPlayerPro.tmg
- Shows summary of changes

### Selective Pull

```bash
# Pull only public directory
bash scripts/pull-from-remote.sh public

# Pull only user directory
bash scripts/pull-from-remote.sh user

# Pull only apps directory
bash scripts/pull-from-remote.sh apps

# Pull specific file (use directory path)
bash scripts/pull-from-remote.sh public/player.php
```

## What Gets Pulled

- All PHP files
- All directories (public, user, admin, apps, etc.)
- Configuration files
- Database migrations
- **IbPlayerPro.tmg** (if it exists on remote)

## What Gets Excluded

- `.git` directory
- `node_modules`
- `*.log` files
- `backup_*` directories
- `.DS_Store` files

## Backup

The full pull script automatically creates a backup in:
```
backup_YYYYMMDD_HHMMSS/
```

To restore from backup:
```bash
rsync -avz backup_YYYYMMDD_HHMMSS/ ./
```

## Finding IbPlayerPro.tmg

After pulling, the script will:
1. Check root directory for `IbPlayerPro.tmg`
2. Search for any files with "ibplayer" or ".tmg" in name
3. List all player-related files found

## SSH Authentication

You'll be prompted for SSH password. If you have SSH keys set up, it will use those automatically.

## Troubleshooting

### Connection Issues
```bash
# Test SSH connection first
ssh fieldte5@bingetv.co.ke

# If connection fails, check:
# 1. Internet connection
# 2. SSH credentials
# 3. Firewall settings
```

### Permission Issues
```bash
# Make scripts executable
chmod +x PULL_FROM_REMOTE.sh
chmod +x scripts/pull-from-remote.sh
```

### Merge Conflicts
If you have local changes that conflict:
1. Review the backup directory
2. Compare files manually
3. Merge changes carefully
4. Test before deploying

## Next Steps After Pull

1. **Review IbPlayerPro.tmg**
   ```bash
   cat IbPlayerPro.tmg
   # or
   less IbPlayerPro.tmg
   ```

2. **Compare with Local**
   ```bash
   diff -r backup_*/ public/ public/
   ```

3. **Update Player**
   - Review insights from IbPlayerPro.tmg
   - Update player.php if needed
   - Test functionality

4. **Sync Back**
   - Once updates are complete
   - Use SYNC_TO_REMOTE.sh or proper-sync.sh

