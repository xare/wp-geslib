<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiWpManager;

class GeslibApiLog {
	static $productKeys = [
			"type", 
			"action", 
			"geslib_id", 
			"description", 
			"author", 
			"pvp_pstas", 
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
			"titulo_original"
			];
	
	public function storeToLines(){
		// 1. Read the log table
		// 2. Read the file and store in lines table
		
	}
	
	private function readFile($path) {
		$lineas = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		  foreach ($lineas as $linea) {
			$datos = explode('|', $linea);
			$function_name = 'procesar' . $data[0];

			if (method_exists($this, $function_name)) {
			  $this->{$function_name}($datos);
			} else {
			  // Handle unexpected values for $datos[0] if needed.
			}
		  }
	}
	
	private function procesarGP4($data) {
		if(count($datos) !== count(self::$productKeys){
			// return error
		} else {
			$content_array = array_combine(self::$productKeys,$data);
		}
		$this->mergeContent($geslib_id, $content_array);
	}

	private function procesar6E($datos) {
		// Procesa las líneas 6E aquí
	}

	private function procesar6TE($datos) {
		// Procesa las líneas 6TE aquí
	}

	private function procesarAUT($datos) {
		// Procesa las líneas AUT aquí
	}

	private function procesarAUTBIO($datos) {
		// Procesa las líneas AUTBIO aquí
	}
	
	private function mergeContent($geslib_id, $content_array) {
		//this function is called when the product has been created but we need to add more data to its content json string
		
		//1. Get the content given the $geslib_id
		$geslibApiDbManager = new GeslibApiDbManager();
		$result = $geslibApiDbManager->fetchContent($geslib_id);
		
		if($result){
			$existing_content = json_decode($result, true);
			$content_array = array_merge($existing_content, $content_array);
		}
		$content = json_encode($content_array);
		if ($result) {
			// update
			$geslibApiDbManager->updateGeslibLines($geslib_id, $content);
		} else {
			$geslibApiDbManager->insertGeslibLines($geslib_id, $content)
		}
	
	}
}