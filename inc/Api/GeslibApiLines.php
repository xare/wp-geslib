<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiWpManager;
use WP_CLI;

class GeslibApiLines {
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
		"7", // Formatos de encuadernación
		"8", // Idiomas
		//"9", // Palabras vacías
		//"B", // Stock
		//"B2", // Stock por centros
		"E", // Estados de artículos
		//"CLI", // Clientes
		"AUT", // Autores
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
		error_log('storeToLines 135: '.$log_id);
		$filename = $this->db->getGeslibLoggedFilename($log_id);
		error_log('storeToLines 137: '.$filename);
		$this->db->setLogStatus( $log_id, 'queued' );
		// 2. Read the file and store in lines table
		$this->readFile( $this->mainFolderPath.$filename, $log_id );

	}


	private function readFile($path, $log_id) {
		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$i = 0;
		foreach ($lines as $line) {
			//if ( $i > 4000) return false;
			$data = explode( '|', $line ) ;
			array_pop($data);
			if(in_array($data[0], self::$lineTypes)) {
				$function_name = 'process' . $data[0];
				if (method_exists($this, $function_name)) {
					$this->{$function_name}($data, $log_id);
					$i++;
				} else {
					// Handle unexpected values for $datos[0] if needed.
				}
			}
		  }
	}

	private function processGP4($data, $log_id) {

		//"type" | "action" | "geslib_id" |	"description" |	"author" | "pvp_ptas" |	"isbn" | "ean" |"num_paginas" |	"num_edicion" |	"origen_edicion" |"fecha_edicion" |	"fecha_reedicion" |	"año_primera_edicion" |"año_ultima_edicion" |"ubicacion" |"stock" |	"materia" |	"fecha_alta" |	"fecha_novedad" |"Idioma" |	"formato_encuadernacion" |"traductor" |"ilustrador" |"colección" |"numero_coleccion" |"subtitulo" |	"estado" |	"tmr" |	"pvp" |	"tipo_de_articulo" |"clasificacion" |"editorial" |	"pvp_sin_iva" |	"num_ilustraciones" |"peso" |"ancho" |"alto" |		"fecha_aparicion" |	"descripcion_externa" |	"palabras_asociadas" |			"ubicacion_alternativa" |"valor_iva" |"valoracion" |"calidad_literaria" |	"precio_referencia" | "cdu" |"en_blanco" |"libre_1" |"libre_2" | 			"premiado" |"pod" | "distribuidor_pod" | "codigo_old" | "talla" |			"color" |"idioma_original" |"titulo_original" |	"pack" |"importe_canon" |	"unidades_compra" |"descuento_maximo"
		// GP4|A|17|BODAS DE SANGRE|GARRIGA MART�NEZ, JOAN|3660|978-84-946952-8-5|9788494695285|56|01||20180101||    |    ||1|06|20230214||003|02|BROGGI RULL, ORIOL||1||APUNTS I CAN�ONS DE JOAN GARRIGA SOBRE TEXTOS DE FEDERICO GARC�A LORCA (A PARTIR|0|0,00|22,00|L0|1|15|21,15|||210|148|||||4,00|||0,00|||||N|N||12530|||001||N||1|100,00|
		if(count($data) !== count(self::$productKeys)) {
			return;
		} else {
			if( $data[1] === 'A' ) {
				$content_array = array_combine( self::$productKeys, $data );
				$content_array = $this->geslibApiSanitize->sanitize_content_array($content_array);
				$this->db->insertProductData( $content_array,$data[1], $log_id );
			}
		}

		//$this->mergeContent($data['geslib_id'], $content_array, $type);
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

	private function process6TE($data, $log_id) {
		// Procesa las líneas 6TE aquí
	}

	private function process1L($data, $log_id) {
		//1L|B|codigo_editorial
		//1L|Tipo movimiento|Codigo_editorial|Nombre|nombre_externo|País|
		//1L|A|1|VARIAS|VARIAS|ES|
		if (in_array($data[1],['A','M'] )){
			// Insert or Update
			$this->insert2Gesliblines($data[2], $log_id, 'editorial', $data[1], $data[3]);
		} else if ($data[1] == 'B' ){
			// Delete

		}
	}

	private function process3( $data, $log_id ) {
		//Add categories
		//3|A|01|Cartes|||
		if( in_array( $data[1],['A','M'] ) ) {
			//insert or update

			$this->insert2GeslibLines(
								$data[2],
								$log_id,
								'product_cat',
								$data[1],
								$this->geslibApiSanitize->utf8_encode($data[3]));
		} else if ( $data[1] == 'B' ) {
			//delete
		}
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
				//$content_array['categories'][$data[1]]['geslib_id'] = $data[2];

			$this->mergeContent($geslib_id, $content_array, 'product', $log_id);
		}
	}
	private function processAUT( $data, $log_id ) {
		// Procesa las líneas AUT aquí
	}

	private function processAUTBIO( $data, $log_id ) {
		// Procesa las líneas AUTBIO aquí
	}

	private function insert2Gesliblines( $geslib_id, $log_id, $type, $action, $data = null ) {
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

	private function mergeContent( $geslib_id, $content_array, $type, $log_id = 0, $action = '' ) {
		//this function is called when the product has been created but we need to add more data to its content json string

		//1. Get the content given the $geslib_id
		$result = $this->db->fetchContent( $geslib_id, $type );

		if( $result ){
			$existing_content = json_decode( $result, true );
			if(isset( $existing_content['categories']) && count($content_array['categories'] ) > 0) {
				array_push( $existing_content['categories'], $content_array['categories'] );
				$content_array = $existing_content;
			} else {
				$content_array = array_merge( $existing_content, $content_array );
			}
		}
		$content = json_encode( $content_array );
		if ( $result ) {
			// update
			$this->db->updateGeslibLines( $geslib_id, $type, $content );
		} else {
			$data_array = [
				'log_id' => $log_id,
				'geslib_id' => $geslib_id,
				'entity' => $type,
				'action' => $action,
				'content' => $content,
				'queued' => 1
			];
			$this->db->insert2GeslibLines( $data_array );
			return "Hemos creado una nueva entrada";
		}
	}

}