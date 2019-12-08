<?php

$title = ($bookx_already_installed) ? 'Update' : 'Install';
$form_action = ($bookx_already_installed) ? 'action=bookx_update' : 'action=bookx_install';
if ($bookx_already_installed && ($bookx_installed_version !== $bookx_module_version)) {
    //Show a warning about updating version
    $update_msg = "Updating BookX from v$bookx_installed_version to v$bookx_module_version";
}
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">BookX <?php echo $title; ?>
            Options<span class="glyphicon glyphicon-cog pull-right" aria-hidden="true"></span></h3>
    </div>
    <div class="panel-body">
        <?php
        
        echo zen_draw_form('bookx_fresh_install', FILENAME_BOOKX_TOOLS, $form_action, 'post', 'class="form-horizontal"');
        
        if ($update_msg) { ?>
        <div class="alert alert-warning" role="alert">
            <?php echo $update_msg; ?>
        </div>
        <?php } ?>
        <div class="form-group">
            <?php echo zen_draw_label('Select DataBase Collations', 'bookx_db_charaset', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
                <?php
                                    $db_options = array(
                                        array(
                                            'id' => 'utf8mb4',
                                            'text' => 'Install with utf8mb4'
                                        ),
                                        array(
                                            'id' => 'utf8',
                                            'text' => 'Install with utf8'
                                        )
                                    );

        if ($dbCharset == 'utf8mb4') {
            $msg = "Data Base Charaset utf8mb4 detect. If you want to use another collation please select";
            $db_option = true;
        } else {
            $msg = "Data Base Charaset ${dbCharsetutf} 8mb4 detect. If you want to use another collation please select";
            $db_option = false;
        } ?>
                <div class="alert alert-info alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <?php echo $msg; ?>
                </div>
                <?php echo zen_draw_pull_down_menu('bookx_db_charaset', $db_options, ($db_option == true) ? $db_options[0]['id'] : $db_options[1]['id'], 'class="form-control"'); ?>
            </div>
        </div>
        <?php
            /**
        <div class="form-group">

            echo zen_draw_label('Enable Ceon Module', 'bookx_ceon', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php
                $ceon_options = [
                    [
                        'id' => 'enable_ceon',
                        'text' => 'Enable'
                    ],
                    [
                        'id' => 'disable_ceon',
                        'text' => 'Disable'
                                        ]
                                        ];

        if ($detect_ceon == true) {
            $msg = "Ceon Module Detected. Would you like to enable bookx support for it?";
            $ceon_option = true;
        } else {
            $msg = 'Ceon Module not Detected. If you intend to use it in the future, you can install Bookx Support. This will add a configuration Value';
            $ceon_option = false;
        } ?>
            <div class="alert alert-info alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <?php echo $msg; ?>
            </div>
            <?php echo zen_draw_pull_down_menu('bookx_ceon', $ceon_options, ($ceon_option == true) ? $ceon_options[0]['id'] : $ceon_options[1]['id'], 'class="form-control"'); ?>
        </div>
    </div>

    <div class="form-group">
            <?php echo zen_draw_label('Use Dinamic Metatags', 'bookx_dinamic_metatags', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
                <?php
                                    $bookx_dinamic_metatags = array(
                                        array(
                                            'id' => 'enable',
                                            'text' => 'Enable Dinamic MetaTags'
                                        ),
                                        array(
                                            'id' => 'disable',
                                            'text' => 'Don\'t! I have something else'
                                        )
                                    );
        $msg = "Use Dinamic Meta Tags on Front page."; ?>
                <div class="alert alert-info alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <?php echo $msg; ?>
                </div>
                <?php echo zen_draw_pull_down_menu('bookx_dinamic_metatags', $bookx_dinamic_metatags, $bookx_dinamic_metatags[1]['id'], 'id="confMetaTags" class="form-control"'); ?>
            </div>
        </div>
        */?>
        <?php
        
        $ep4_info = "<p>You may use csv import for Bookx, with EP4 and EP4Bookx pluging.</p>";
        $ep4_info .= "<p><strong>EasyPopulate</strong><br /> " . check_git_release_for("https://api.github.com/repos/mc12345678/EasyPopulate-4.0/releases", false) . "</p>";
        $ep4_info .= "<p><strong>Ep4Bookx Info</strong> <br /> " . check_git_release_for("https://api.github.com/repos/mesnitu/EasyPopulate4BookX/releases", false) . "</p>"; ?>
        <div class="form-group">
            <?php echo zen_draw_label('CSV', 'ep4_download', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
                <div class="alert alert-info" role="alert"><?php echo $ep4_info; ?>
                </div>
                <?php
                echo zen_draw_checkbox_field('ep4_download', '0', false, '', 'placeholder="Disabled input here..." disabled') . '<span class="text-warning">Automatic Install not yet available</span>'; ?>
            </div>
        </div>
        <button type="submit" class="btn btn-default float-rigth">Submit</button>
        </form>
    </div>
</div>