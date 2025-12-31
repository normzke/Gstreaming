#!/bin/bash
rsync -avzR -e "ssh -p 2222 -o IdentitiesOnly=yes" admin/packages.php admin/channels.php admin/users.php admin/subscriptions.php public/images/site.webmanifest fieldte5@bingetv.co.ke:/home1/fieldte5/bingetv.co.ke/
