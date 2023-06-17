<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiWpManager;

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
	
	private $db;
	private $mainFolderPath;

	public function __construct() {
		$this->mainFolderPath = WP_CONTENT_DIR . '/uploads/geslib/';
		$this->db = new GeslibApiDbManager();
	}
	public function storeToLines(){
		// 1. Read the log table
		$filename = $this->db->getLogQueuedFile();
		$log_id = $this->db->getLogId($filename);
		// 2. Read the file and store in lines table
		$this->readFile($this->mainFolderPath.$filename, $log_id);
	}
	
	private function readFile($path, $log_id) {
		echo $path;
		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		foreach ($lines as $line) {

			$data = explode( '|', $line ) ;
			array_pop($data);
			$function_name = 'process' . $data[0];
			if (method_exists($this, $function_name)) {
			  $this->{$function_name}($data, $log_id);
			} else {
			  // Handle unexpected values for $datos[0] if needed.
			}
		  }
	}
	
	private function processGP4($data, $log_id) {
		echo "inside ProcessGP4";
		echo '/n';
		echo count($data);
		echo '\n';
		echo count(self::$productKeys);
		echo '/n';
		if(count($data) !== count(self::$productKeys)) {
			return ;
		} else {
			if($data[1] === 'A') {
				$content_array = array_combine(self::$productKeys,$data);
				var_dump($content_array);
				$this->db->insertProductData($content_array, $log_id);
			}
		}
		
		//$this->mergeContent($data['geslib_id'], $content_array);
	}

	private function process6E($data) {
		// Procesa las líneas 6E aquí
	}

	private function process6TE($data) {
		// Procesa las líneas 6TE aquí
	}

	private function processAUT($data) {
		// Procesa las líneas AUT aquí
	}

	private function processAUTBIO($data) {
		// Procesa las líneas AUTBIO aquí
	}
	
	private function mergeContent($geslib_id, $content_array) {
		//this function is called when the product has been created but we need to add more data to its content json string
		
		//1. Get the content given the $geslib_id
		$result = $this->db->fetchContent($geslib_id);
		
		if($result){
			$existing_content = json_decode($result, true);
			$content_array = array_merge($existing_content, $content_array);
		}
		$content = json_encode($content_array);
		if ($result) {
			// update
			$this->db->updateGeslibLines($geslib_id, $content);
		} else {
			$this->db->insertGeslibLines($geslib_id, $content);
		}
	
	}

}