#!/bin/bash
REMOTE_HOST="bluehost"
REMOTE_DIR="/home1/fieldte5/bingetv.co.ke"

echo "🔄 Syncing files to $REMOTE_HOST..."

if [ "$1" != "" ]; then
    # Selective sync
    FILE=$1
    if [[ $FILE == admin/* ]]; then
        rsync -avz "$FILE" "$REMOTE_HOST:$REMOTE_DIR/admin/${FILE#admin/}"
    elif [[ $FILE == public/* ]]; then
        rsync -avz "$FILE" "$REMOTE_HOST:$REMOTE_DIR/public/${FILE#public/}"
    else
        rsync -avz "$FILE" "$REMOTE_HOST:$REMOTE_DIR/$FILE"
    fi
    # Quick permission set for the specific file
    ssh $REMOTE_HOST "chmod 644 $REMOTE_DIR/$FILE"
else
    # Full sync (as previously defined)
    rsync -avz admin/packages.php admin/channels.php admin/users.php admin/streaming-users.php $REMOTE_HOST:$REMOTE_DIR/admin/
    rsync -avz admin/includes/header.php admin/includes/footer.php $REMOTE_HOST:$REMOTE_DIR/admin/includes/
    rsync -avz public/index.php public/apps.php public/debug_packages.php $REMOTE_HOST:$REMOTE_DIR/public/
    rsync -avz public/includes/navigation.php public/includes/footer.php $REMOTE_HOST:$REMOTE_DIR/public/includes/
    rsync -avz public/css/main.css $REMOTE_HOST:$REMOTE_DIR/public/css/

    # Set permissions
    ssh $REMOTE_HOST "chmod 644 $REMOTE_DIR/admin/*.php && chmod 644 $REMOTE_DIR/admin/includes/*.php"
    ssh $REMOTE_HOST "chmod 644 $REMOTE_DIR/public/*.php && chmod 644 $REMOTE_DIR/public/includes/*.php && chmod 644 $REMOTE_DIR/public/css/*.css"
fi

echo "✅ Sync completed!"
