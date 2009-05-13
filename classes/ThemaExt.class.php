<?php

/**
 * Erweiterung für die Klasse Thema
 *
 */
class ThemaExt extends Thema
{
	
	/**
	 * Anzahl an Artikel
	 *
	 * @var unknown_type
	 */
	var $artikel_count;

	/**
	 * Konstruktor
	 *
	 * @param string $id
	 */
	public function __construct($id = FALSE)
	{
		parent::__construct($id);
		$this->artikel_count = 0;
	}

	function setArtikelCount($c)
	{
		$this->artikel_count = $c;
	}

	function getArtikelCount()
	{
		return $this->artikel_count;
	}
}
?>