<?php
namespace BD\Emails;

defined('ABSPATH') or exit;

class Placeholder
{
	private $_id   = 0;
	private $_item = [];

	public function setContent($data)
	{
		$this->_item[$this->_id] = [];
		$this->_item[$this->_id]['data'] = $data;
		return $this;
	}

	public function convert($array)
	{
		$find = [];
		$replace = [];

		foreach ($array as $key => $value) {
			array_push($find, $key);
			array_push($replace, $value);
		}

		$result = str_ireplace($find, $replace, $this->_item[ $this->_id]['data'] );

		$this->_id++;
		return $result;
	}
}
