<?php

use Inc\Geslib\Api\GeslibApiReadFiles;

?>
<div class="table-container">
    <?php
        $mainFolderPath = WP_CONTENT_DIR . '/uploads/' . get_option('geslib_settings')['geslib_folder_index'] .'/';
    ?>
    <h1> Archivos en la carpeta de geslib </h1>
    <ul>
        <li>A: Añadir</li>
        <li>M: Modificar</li>
        <li>B: Borrar</li>
        <li>GP4: Productos</li>
        <li>1L: Editoriales</li>
        <li>3: Categorías</li>
    </ul>
    <?php
        $files = glob($mainFolderPath . 'INTER*');

        $geslibApiReadFiles = new GeslibApiReadFiles;
    ?>
    <table class="geslib-table">
        <thead>
            <tr>
                <td>
                    <span class="dashicons dashicons-format-aside"></span>
                    <strong>Nombre del archivo</strong>
                </td>
                <td>
                    <span class="dashicons dashicons-clock"></span>
                    <strong>Fecha de creación</strong>
                </td>
                <td>
                    <span class="dashicons dashicons-info-outline"></span>
                    <strong>Memoria</strong>
                </td>
                <td>
                    <span class="dashicons dashicons-info-outline"></span>
                    <strong>Número de Lineas</strong>
                </td>
                <td>GP4 A</td>
                <td>GP4 M</td>
                <td>GP4 B</td>
                <td>1L A</td>
                <td>1L M</td>
                <td>1L B</td>
                <td>3 A</td>
                <td>3 M</td>
                <td>3 B</td>
            </tr>
        </thead>
        <?php
        foreach( $files as $file ) :
            if( !isset( $file ) || $file === '' ) continue;
            // Get file modification time
            $modTime = filemtime($file);
            // Format the date and time
            $formattedModTime = date('d/m/Y H:i', $modTime);
            // Get file size and format it
            $formattedSize = formatSize(filesize($file));

            $countLines = $geslibApiReadFiles->countLines($file);
            $lineCounts = $geslibApiReadFiles->countLinesWithGP4($file);
            ?>
            <tr>
                <td>
                    <?php echo basename($file) ; ?>
                </td>
                <td>
                    <span><?php echo $formattedModTime; ?></span>
                </td>
                <td>
                    <span><?php echo $formattedSize; ?></span>
                </td>
                <td>
                    <span><?php echo $countLines; ?></span>
                </td>
                <td>
                    <span>
                        <?php
                        if ( $lineCounts['GP4A'] )
                            echo $lineCounts['GP4A'] ;
                        else
                            echo '0';
                    ?></span>
                </td>
                <td>
                    <span>
                        <?php
                        if ( $lineCounts['GP4M'] )
                            echo $lineCounts['GP4M'] ;
                        else
                            echo '0';
                        ?>
                    </span>
                </td>
                <td>
                <span>
                        <?php
                        if ( $lineCounts['GP4B'] )
                            echo $lineCounts['GP4B'] ;
                        else
                            echo '0';
                        ?>
                    </span>
                </td>
                <td>
                <span>
                        <?php
                        if ( $lineCounts['1LA'] )
                            echo $lineCounts['1LA'] ;
                        else
                            echo '0';
                        ?>
                    </span>
                </td>
                <td>
                <span>
                        <?php
                        if ( $lineCounts['1LM'] )
                            echo $lineCounts['1LM'] ;
                        else
                            echo '0';
                        ?>
                    </span>
                </td>
                <td>
                <span>
                    <?php
                        if ( $lineCounts['1LB'] )
                            echo $lineCounts['1LB'] ;
                        else
                            echo '0';
                        ?>
                        </span>
                </td>
                <td>
                    <span><?php
                        if ( $lineCounts['3A'] )
                            echo $lineCounts['3A'] ;
                        else
                            echo '0';
                        ?></span>
                </td>
                <td>
                    <span><?php
                        if ( $lineCounts['3M'] )
                            echo $lineCounts['3M'] ;
                        else
                            echo '0';
                    ?></span>
                </td>
                <td>
                <span><?php
                        if ( $lineCounts['3B'] )
                            echo $lineCounts['3B'] ;
                        else
                            echo '0';
                    ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php
// Function to format the file size
function formatSize($bytes) {
    $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
    for($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++);
    return( round($bytes, 2) . " " . $types[$i] );
}
?>