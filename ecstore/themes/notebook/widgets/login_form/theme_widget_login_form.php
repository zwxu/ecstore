<?php
function theme_widget_login_form(&$setting,&$smarty) {
    $render = app::get('pam')->render();
    $passprot = kernel::single('b2c_ctl_site_passport');
    $passprot->gen_login_form();

    return $render->pagedata['login_image_url'];
}

?>
