# IbPlayerPro.tmg Extraction Instructions

## Quick Extraction

```bash
cd ~/Downloads/BingeTV

# Install squashfs-tools (if not already installed)
brew install squashfs

# Extract the archive
unsquashfs -d extracted_player IbPlayerPro.tmg

# Explore extracted files
cd extracted_player
find . -type f | head -20
```

## What to Look For

After extraction, review these areas:

1. **HTML Files** - Player interface structure
2. **JavaScript Files** - Playlist parsing, authentication, streaming logic
3. **CSS Files** - Styling patterns and futuristic design elements
4. **Configuration Files** - Settings and preferences
5. **Documentation** - Any README or guide files

## Integration Checklist

- [ ] Review player UI structure
- [ ] Check playlist parsing implementation
- [ ] Review authentication methods
- [ ] Check streaming video player setup
- [ ] Look for category/organization patterns
- [ ] Review error handling
- [ ] Check responsive/TV optimization
- [ ] Look for performance optimizations
- [ ] Review accessibility features
- [ ] Check for additional features we missed

## Update Player

After reviewing, update `public/player.php` with any improvements found.

