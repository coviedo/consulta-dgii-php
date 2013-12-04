<?php
/**
 * @Name: consultar DGII 
 * @author :Jose Ramon De Los Santos Oviedo
 * @version: 0.0.1
 * @date : 2013-11-02 
 *
 **/
class Rnc
{
	private $_fileName = 'src/config.json';
	private $_dataJson;
	const URL = 'http://www.dgii.gov.do/app/WebApps/Consultas/rnc/RncWeb.aspx';

	public function __construct()
	{
		if (!file_exists($this->_fileName))
			die('El archivo config no existe');

		$handle = fopen($this->_fileName, 'r');
		$this->_dataJson = json_decode(fread($handle, filesize($this->_fileName)), true);
		fclose($handle);
	}

	private function _getResource()
	{
		foreach ($this->_dataJson['request_parameters'] as $key => $value) {
			$fieldStr .= $key . '=' . $value . '&'; 
		}

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, self::URL);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, rtrim($fieldStr, '&'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$schemeHtml = curl_exec($ch);
		curl_close($ch);

		$dom = new DomDocument();
		$dom->loadHtml($schemeHtml);
		$xpath = new DomXpath($dom);

		$tr = $xpath->query('//span[@id="lblMsg"]')->item(0);
		if ($tr->textContent)
			die($tr->textContent);

		$tr = $xpath->query('//tr[@class="GridItemStyle"]')->item(0);

		foreach ($tr->childNodes as $d) {
			$rncValue[] = $d->nodeValue;
		}

		header('Content-type: application/json');
		array_pop($rncValue);
		return json_encode($rncValue, JSON_FORCE_OBJECT);
		
	}

	public function queryDoc($doc)
	{
		$this->_dataJson['request_parameters']['txtRncCed'] = $doc;
		return $this->_getResource();
	}
}
