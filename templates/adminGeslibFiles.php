<?php
use Inc\Geslib\Api\GeslibFilesListTable;
?>

<div class="wrap">
    <h1>Geslib FILES</h1>
    <?php settings_errors(); ?>

    <!-- Page display -->
    <?php
        $wp_list_table = new GeslibFilesListTable;
        $wp_list_table->prepare_items();
        // Render the table
        echo "<form method='post' name='geslib_files_search' action='".$_SERVER['PHP_SELF']."?page=geslib_files'>";
        $wp_list_table->search_box("Geslib Files Search", "search_geslib_files");
        echo "</form>";
        $wp_list_table->display();
    ?>

</div>