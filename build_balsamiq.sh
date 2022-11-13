#!/bin/sh
rm /tmp/test.svelte
php balsamiq.php preview > /tmp/test.svelte
npm run format-file -- --write /tmp/test.svelte
cp /tmp/test.svelte test.svelte
echo "done test.svelte"
sleep 2;
