<form method="post" action="options.php">
    <?php
        settings_fields( 'geslib_settings' );
        do_settings_sections( 'geslib' );
        submit_button();
    ?>
</form>