<?php
apc_clear_cache();
apc_clear_cache('www-data');
apc_clear_cache('opcode');

echo "apc cache cleaned";
?>
