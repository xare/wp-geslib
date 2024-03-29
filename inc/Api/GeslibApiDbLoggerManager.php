<?php

namespace Inc\Geslib\Api;

class GeslibApiDbLoggerManager extends GeslibApiDbManager {
    public function geslibLogger( int $log_id = 0, int $geslib_id = 0, string $type = '',
                                string $action = '', string $entity = '', array $metadata = [] ) {
        global $wpdb;

        try {
            $wpdb->insert(
                $wpdb->prefix . self::GESLIB_LOGGER_TABLE,
                array_combine( self::$geslibLoggerKeys,
                            [
                                $log_id,
                                $geslib_id,
                                $type,
                                $action,
                                $entity,
                                json_encode($metadata)?? 'metadata',
                            ]),
                ['%d', '%d', '%s', '%s', '%s', '%s']
            );
            return true;
        } catch (\Exception $exception) {
            echo "Geslib Logger error: " . $exception->getMessage();
            return false;
        }
    }

    public function getLatestLoggers():string {
        global $wpdb;

        $results  = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM "
                            .$wpdb->prefix.self::GESLIB_LOGGER_TABLE
                            ." ORDER BY date DESC LIMIT %d", 20)
            , ARRAY_A);
        if( count($results) == 0 ) return 'No loggers found.';
        $html_list = '<ul>';
        foreach($results as $result) {
            $style = $result['type'] == 'error' ? ' style="color:red;"' : null;
            $metadata = json_decode($result['metadata']);
            $html_list .= "<li{$style}>".
                            " Type: ". $result['type'].
                            " Date: ". $result['date'].
                            " Log_id: " . $result['log_id'] .
                            " Geslib_id: " . $result['geslib_id'] .
                            " Action: ". $result['action'].
                            " Entity: ". $result['entity'].
                            " Metadata: ". $metadata->message .
                            "</li>";
        }
        $html_list .= '</ul>';
        return $html_list;
    }


}