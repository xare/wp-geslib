<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiWpManager;
use WP_CLI;

class GeslibApiLines {
	static $productDeleteKeys = [
		"type",
		"action",
		"geslib_id"
	];
	static $authorDeleteKeys = [
			"type",
			"action",
			"geslib_id"
	];
	static $editorialDeleteKeys = [
		"type",
		"action",
		"geslib_id"
	];
	static $categoriaDeleteKeys = [
			"type",
			"action",
			"geslib_id"
	];
	static $productKeys = [
		"type",
		"action",
		"geslib_id",
		"description",
		"author",
		"pvp_ptas",
		"isbn",
		"ean",
		"num_paginas",
		"num_edicion",
		"origen_edicion",
		"fecha_edicion",
		"fecha_reedicion",
		"año_primera_edicion",
		"año_ultima_edicion",
		"ubicacion",
		"stock",
		"materia",
		"fecha_alta",
		"fecha_novedad",
		"Idioma",
		"formato_encuadernacion",
		"traductor",
		"ilustrador",
		"colección",
		"numero_coleccion",
		"subtitulo",
		"estado",
		"tmr",
		"pvp",
		"tipo_de_articulo",
		"clasificacion",
		"editorial",
		"pvp_sin_iva",
		"num_ilustraciones",
		"peso",
		"ancho",
		"alto",
		"fecha_aparicion",
		"descripcion_externa",
		"palabras_asociadas",
		"ubicacion_alternativa",
		"valor_iva",
		"valoracion",
		"calidad_literaria",
		"precio_referencia",
		"cdu",
		"en_blanco",
		"libre_1",
		"libre_2",
		"premiado",
		"pod",
		"distribuidor_pod",
		"codigo_old",
		"talla",
		"color",
		"idioma_original",
		"titulo_original",
		"pack",
		"importe_canon",
		"unidades_compra",
		"descuento_maximo"
	];
	static $editorialKeys = [
		"type",
		"action",
		"geslib_id",
		"name",
		"",
		""
	];
	static $categoriaKeys = [
		"type",
		"action",
		"geslib_id",
		"name",
		"",
		""
	];
	static $authorKeys = [
		"type",
		"action",
		"geslib_id",
		"name"
	];
	static $lineTypes = [
		'1L', // Editoriales
		'1A', // Compañías discográficas
		//"1P", // Familias de papelería
		//"1R", // Publicaciones de prensa
		"2", // Colecciones editoriales
		"3", // Materias
		"GP4", // Artículos
		"EB", // eBooks (igual que los libros)
		"IEB", // Información propia del eBook
		"5", // Materias asociadas a los artículos
		"BIC", // Materias IBIC asociadas a los artículos
		"6", // Referencias de la librería
		"6E", // Referencias del editor
		"6I", // Índice del libro
		"6T", // Referencias de la librería (traducidas)
		"6TE", // Referencias del editor (traducidas)
		"6IT", // Índice del libro (traducido)
		//"LA", // Autores normalizados asociados a un artículo
		//"7", // Formatos de encuadernación
		//"8", // Idiomas
		//"9", // Palabras vacías
		//"B", // Stock
		//"B2", // Stock por centros
		"E", // Estados de artículos
		//"CLI", // Clientes
		"AUT", // Autores
		"AUTBIO", // Biografías de Autores
		//"I", // Indicador de carga inicial. Cuando este carácter aparece en la primera línea, indica que se están enviando todos los datos y de todas las entidades
		//"IPC", // Incidencias en pedidos de clientes
		//"P", // Promociones de artículos (globales a todos los centros)
		//"PROCEN", // Promociones de artículos por centros
		//"PC", // Pedidos de clientes
		//"VTA", // Ventas
		//"PAIS", // Países
		//"CLOTE", // Lotes de artículos: Cabecera
		//"LLOTE", // Lotes de artículos: Líneas
		//"TIPART", // Tipos de artículos
		//"CLASIF", // Clasificaciones de artículos
		//"ATRA", // Traducciones asociadas a los artículos
		//"ARTATR",
		//"CA", // Claves alternativas asociadas a los artículos
		//"CLOTCLI", // Lotes de clientes: Cabecera
		//"LLOTCLI", // Lotes de clientes: Líneas
		//"PROFES", // Profesiones
		//"PROVIN", // Provincias
		//"CAGRDTV", // Agrupaciones de descuentos de ventas: Cabecera
		//"LAGRDTV" // Agrupaciones de descuentos de ventas: Líneas
	];
	private $db;
	private $mainFolderPath;
	private $geslibSettings;
	private $geslibApiSanitize;

	public function __construct() {
		$this->geslibSettings = get_option('geslib_settings');
		$this->mainFolderPath = WP_CONTENT_DIR . "/uploads/".$this->geslibSettings['geslib_folder_index']."/";
		$this->db = new GeslibApiDbManager();
		$this->geslibApiSanitize = new GeslibApiSanitize();
	}

	public function storeToLines(){
		// 1. Read the log table
		$log_id = $this->db->getGeslibLoggedId();
		$filename = $this->db->getGeslibLoggedFilename( $log_id );
		$this->db->setLogStatus( $log_id, 'queued' );
		$fullPath = $this->mainFolderPath . $filename;

		// 2. Read the file and store in lines table
		if ( pathinfo( $fullPath, PATHINFO_EXTENSION ) === 'zip' ) {
			$geslibReadFile = new GeslibApiReadFiles;
			$filename = $geslibReadFile->unzipFile( $fullPath );
		}

		$lines = file( $fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		$batch_size = 2000; // Choose a reasonable batch size
		$queue = get_option( 'geslib_queue', [] );
		$batch = [];

		foreach ($lines as $line) {
			$line = $this->sanitizeLine( $line );
			if( $this->isUnnecessaryLine( $line ) ) continue;
			if( !$this->isInProductKey( $line ) ) continue;
			if( $this->isInEditorials( $line )) continue;
			$item = [
				'line' => $line,
				'log_id' => $log_id,
				'type' => 'store_lines'  // type to identify the task in processQueue
			];
			$batch[] = $item;

			if (count($batch) >= $batch_size) {
				$queue = array_merge( $queue, $batch );
				update_option( 'geslib_queue', $queue );
				$batch = [];
			}
		}
		// Don't forget the last batch
		if ( !empty( $batch ) ) {
			$queue = array_merge( $queue, $batch );
			update_option( 'geslib_queue', $queue );
		}

    	return 'File ' . $path . ' has been read with ' . count( $lines ) . ' lines';
	}

	public function sanitizeLine($line) {
		// Split the line into its components
		$line_items = explode('|', $line);

		// Sanitize each component
		$sanitized_items = array_map(function($line_item) {
			if(is_string($line_item))
				return $this->geslibApiSanitize->utf8_encode($line_item);
			return $line_item;
		}, $line_items);

		// Join the components back together
		$sanitized_line = implode('|', $sanitized_items);

		return $sanitized_line;
	}



	/**
	 * readFile
	 *
	 * @param  string $path
	 * @param  int $log_id
	 * @return void
	 */
	/* private function readFile( string $path, int $log_id) {
		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$queue = get_option('geslib_queue', []);
		foreach ($lines as $line) {
			$item = [
				'line' => $line,
				'log_id' => $log_id,
				'type' => 'read_line'  // type to identify the task in processQueue
			];
			$queue[] = $item;
		  }
		  update_option('geslib_queue', $queue);
    	return 'File ' . $path . ' has been read with ' . count($lines) . ' lines';
	} */

	/**
	 * readLine
	 *
	 * @param  string $line
	 * @param  int $log_id
	 * @return void
	 */
	public function readLine( string $line, int $log_id ) :void {
		$data = explode( '|', $line ) ;
		array_pop($data);
		if( in_array($data[0], self::$lineTypes ) ) {
			$function_name = 'process' . $data[0];
			if ( method_exists( $this, $function_name ) ) {
				$this->{$function_name}($data, $log_id);
			}
		}
	}

	/**
	 * processGP4
	 * //"type" | "action" | "geslib_id" |	"description" |	"author" | "pvp_ptas" |	"isbn" | "ean" |"num_paginas" |	"num_edicion" |	"origen_edicion" |"fecha_edicion" |	"fecha_reedicion" |	"año_primera_edicion" |"año_ultima_edicion" |"ubicacion" |"stock" |	"materia" |	"fecha_alta" |	"fecha_novedad" |"Idioma" |	"formato_encuadernacion" |"traductor" |"ilustrador" |"colección" |"numero_coleccion" |"subtitulo" |	"estado" |	"tmr" |	"pvp" |	"tipo_de_articulo" |"clasificacion" |"editorial" |	"pvp_sin_iva" |	"num_ilustraciones" |"peso" |"ancho" |"alto" |		"fecha_aparicion" |	"descripcion_externa" |	"palabras_asociadas" |			"ubicacion_alternativa" |"valor_iva" |"valoracion" |"calidad_literaria" |	"precio_referencia" | "cdu" |"en_blanco" |"libre_1" |"libre_2" | 			"premiado" |"pod" | "distribuidor_pod" | "codigo_old" | "talla" |			"color" |"idioma_original" |"titulo_original" |	"pack" |"importe_canon" |	"unidades_compra" |"descuento_maximo"
	 * // GP4|A|17|BODAS DE SANGRE|GARRIGA MART�NEZ, JOAN|3660|978-84-946952-8-5|9788494695285|56|01||20180101||    |    ||1|06|20230214||003|02|BROGGI RULL, ORIOL||1||APUNTS I CAN�ONS DE JOAN GARRIGA SOBRE TEXTOS DE FEDERICO GARC�A LORCA (A PARTIR|0|0,00|22,00|L0|1|15|21,15|||210|148|||||4,00|||0,00|||||N|N||12530|||001||N||1|100,00|
	 *
	* @param  mixed $data
	 * @param  int $log_id
	 * @return void
	 */
	private function processGP4( array $data, int $log_id ) {
		if ($data[1] === 'B') {
			$keys = self::$productDeleteKeys;
		} elseif (in_array($data[1], ['A', 'M'])) {
			$keys = self::$productKeys;
		}
		if (! isset($keys)) return false;
		$content_array = array_combine($keys, $data);
		$content_array = $this->geslibApiSanitize->sanitize_content_array($content_array);
		$this->db->insertData( $content_array, $data[1], $log_id, 'product' );
	}

	private function process6E($data, $log_id) {
		// Procesa las líneas 6E aquí
		// 6E|Articulo|Contador|Texto|
		// 6E|1|1|Els grans mitjans ens han repetit fins a l'infinit escenes de mort i destrucci� a Gaza, per� ens han amagat la quotidianitat m�s extraordin�ria. Viure morir i n�ixer a Gaza recull un centenar de fotografies que ens mostren les meravelles que David Segarra es va trobar enmig de la trag�dia: la capacitat de viure, d'estimar, de resistir i de sobreviure malgrat l'horror.\n\nAcompanyant les imatges, les paraules antigues de la Mediterr�nia. Ausi�s March, Estell�s, al-Russaf�, Llach, Espriu, Aub, Ibn Arab�, Lorca, Darwix o Kavafis. Veus de les tradicions que ens han forjat com a civilitzacions. Per� tamb� peda�os de relats i hist�ries poc conegudes que l'autor va descobrir durant tres mesos de conviv�ncia en aquest tros de Palestina. Hist�ries de saviesa i dolor. Hist�ries de paci�ncia i perseveran�a. Hist�ries de p�rdua i renaixen�a. Hist�ries de la bellesa oculta de Gaza.|
		$geslib_id = $data[1];
		$content_array['sinopsis'] = $data[3];
		$content_array = $this->geslibApiSanitize->sanitize_content_array( $content_array );
		$this->mergeContent( $geslib_id, $content_array, 'product');
	}

	private function process6TE(mixed $data, int $log_id) {
		// Procesa las líneas 6TE aquí
	}

	private function process1L(array $data, int $log_id) {
		//1L|B|codigo_editorial
		//1L|Tipo movimiento|Codigo_editorial|Nombre|nombre_externo|País|
		//1L|A|1|VARIAS|VARIAS|ES|
		if ($data[1] === 'B') {
			$keys = self::$editorialDeleteKeys;
		} elseif (in_array($data[1], ['A', 'M'])) {
			$keys = self::$editorialKeys;
		}
		if (! isset($keys)) return false;
		$content_array = array_combine($keys, $data);
		$content_array = $this->geslibApiSanitize->sanitize_content_array( $content_array );
		$this->db->insertData( $content_array, $data[1], $log_id , 'editorial');
	}

	private function process3( $data, $log_id ) {
		//Add categories
		//3|A|01|Cartes|||
		if ($data[1] === 'B') {
			$keys = self::$categoriaDeleteKeys;
		} elseif (in_array($data[1], ['A', 'M'])) {
			$keys = self::$categoriaKeys;
		}
		if ( !isset($keys)) return false;
		$content_array = array_combine( $keys, $data );
		$content_array = $this->geslibApiSanitize->sanitize_content_array( $content_array );
		$this->db->insertData( $content_array, $data[1], $log_id , 'product_cat');
	}

	private function process5( $data, $log_id ) {
		//Add a category to to a
		// “5”|Código de materia (varchar(12))|Código de articulo + SEPARADOR
		//5|17|1|
		$geslib_id = $data[2];
		if($data[1] !== '0') {
			if( isset( $content_array['categories'] ) )
				array_push( $content_array['categories'], [ $data[1] => $data[2] ] );
			else
				$content_array['categories'][$data[1]] = $data[2];

			$this->mergeContent($geslib_id, $content_array, 'product', $log_id);
		}
	}
	/**
	 * processAUT
	 * Procesa las líneas AUT
	 * “AUT”|Acción|GeslibID|Nombre del autor
	 * AUT|A|2806|HILAL, JAMIL|
	 * "AUT"|B|GeslibId
	 *
	 * @param  mixed $data
	 * @param  mixed $log_id
	 * @return void
	 */
	private function processAUT( $data, $log_id ) {
		if (in_array( $data[1], ['A','M'] )){
			// Insert or Update
			$content_array = array_combine( self::$authorKeys, $data );
			$content_array = $this->geslibApiSanitize->sanitize_content_array($content_array);
		} elseif ($data[1] == 'B' ){
			// Delete
			$content_array = array_combine( self::$authorDeleteKeys, $data );
		}
		$this->db->insertData( $content_array, $data[1], $log_id, 'autor' );
	}
	/**
	 * processAUTBIO
	 * //AUTBIO|3|Realiz� estudios de econom�a, ciencias pol�ticas y sociolog�a. Doctor en Ciencias Pol�ticas y profesor titular en la Facultad de Ciencias Pol�ticas y Sociolog�a de la Universidad Complutense de Madrid, hizo sus estudios de posgrado en la Universidad de Heidelberg (Alemania). En septiembre de 2010 fue ponente central en la conmemoraci�n del D�a Internacional de la Democracia en la Asamblea General de las Naciones Unidas en Nueva York. Dirige el Departamento de Gobierno, Pol�ticas P�blicas y Ciudadan�a Global del Instituto Complutense de Estudios Internacionales y pertenece al consejo cient�fico de ATTAC.|
	 *
	 * @param  mixed $data
	 * @param  int $log_id
	 * @return void
	 */
	private function processAUTBIO( $data, int $log_id ) {
		$content_array['biografia'] = $data[2];
		$content_array = $this->geslibApiSanitize->sanitize_content_array($content_array);
		$this->mergeContent( $data[1], $data[2], 'autor');
	}

	/**
	 * insert2Gesliblines
	 *
	 * @param  int $geslib_id
	 * @param  int $log_id
	 * @param  string $type
	 * @param  string $action
	 * @param  array $data
	 * @return void
	 */
	private function insert2Gesliblines(
			int $geslib_id,
			int $log_id,
			string $type,
			string $action,
			mixed $data = null ) {
		$data_array = [
			'log_id' => $log_id,
			'geslib_id' => $geslib_id,
			'entity' => $type,
			'action' => $action,
			'content' => $data,
			'queued' => 1
		];

		$this->db->insert2GeslibLines( $data_array );
	}
	/**
	 * mergeContent
	 * this function is called when the product has been created but we need to add more data to its content json string
	 *
	 * @param  int $geslib_id
	 * @param  array $new_content_array
	 * @param  string $type
	 * @return mixed
	 */
	private function mergeContent( int $geslib_id, array $new_content_array, string $type, int $log_id = 0, string $action = '' ) {
		//this function is called when the product has been created but we need to add more data to its content json string

		//1. Get the content given the $geslib_id
		$original_content = $this->db->fetchContent( $geslib_id, $type );
		if ( !$original_content ) return "error at Merge Content";

		$original_content_array = json_decode( $original_content, true);
		if (
			isset( $original_content_array['categories'] )
			&& count( $original_content_array['categories'] ) > 0
			) {
				$original_content_array['categories'] = array_merge( $original_content_array['categories'], $new_content_array['categories'] );
			array_push( $original_content_array['categories'], $new_content_array['categories'] );
			//$original_content_array = $new_content_content;
		} elseif ( isset( $new_content_array['categories'] ) ) {
			$original_content_array['categories'] = $new_content_array['categories'];
		};

		$fields = ['sinopsis','biografia'];
		foreach( $fields as $field ) {
			if ( !isset( $original_content_array[$field] )
			&& isset( $new_content_array[$field] )) {
				$original_content_array[$field] = $new_content_array[$field];
			}
		}

		$content = json_encode($original_content_array);
		if ( ! $content ) return FALSE;

		$this->db->updateGeslibLines( $geslib_id, $type, $content );
	}

	/**
	 * unnecessaryLine
	 *
	 * @param  mixed $line
	 * @return boolean
	 */
	public function isUnnecessaryLine(string $line) :bool {
		return strpos($line, '< Genérica >') !== false;
	}

	public function isInEditorials( string $line ) :bool {
		// $line = 1L|A|216|AGUILAR
		$line_items = explode( '|', $line );
		if( $line_items[0] == '1L' && $line_items[1] == 'A' ) {
			$terms = get_terms([
				'taxonomy'   => 'editorials', // replace with your actual taxonomy name
				'hide_empty' => false,
				'meta_query' => [
					[
						'key'     => 'geslib_id',
						'value'   => $line_items[2],
						'compare' => '=',
					],
				],
			]);
			var_dump($terms);

			// If term with geslib_id found, return true
			if (!empty($terms) && !is_wp_error($terms)) {
				return true;
			}
		}
		return false;
	}
	public function isInProductKey($line) {
		$line_items = explode('|', $line);
		if (is_array($line_items) && in_array($line_items[0], self::$lineTypes)){
			return implode('|', $line_items);
		} else {
			return false;
		}
	}

}