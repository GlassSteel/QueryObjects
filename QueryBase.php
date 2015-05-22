<?php namespace glasteel\QueryObjects;

class QueryBase
{
	protected $quote_placeholder = '*|*^*|*@*|*^*|*';

	protected function make_concat_sql($alias,$config){
		$qp = $this->quote_placeholder;

		DB::statement('SET SESSION group_concat_max_len = 10240;');
		
		$order = '';
		if ( isset($config['order']) && $config['order'] ){
			$order .= 'ORDER BY ' . $config['order'];
		}
		
			$sql = <<<SQL
				CONCAT('[{{$qp}',GROUP_CONCAT(
					DISTINCT CONCAT_WS(
						'$qp,$qp'
SQL;
			foreach ($config['concats'] as $key => $value) {
				$sql .= ',CONCAT_WS(\'' . $qp . ':' . $qp . '\',\'' . $key . '\',COALESCE(' . $value . ',\'\'))' . "\n";
			}
			
			$sql .= <<<SQL
					) $order SEPARATOR '$qp},{{$qp}'
			),'$qp}]') AS {$alias}
SQL;
		return $sql;
	}//make_concat_sql()

	protected function decode($string){
		$qp = $this->quote_placeholder;
		$string = $this->escapeJsonString($string);
		$string = str_replace($qp, '"', $string);
		return json_decode($string,true);
	}//decode()

	protected function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
	    $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
	    $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
	    return str_replace($escapers, $replacements, $value);
	}//escapeJsonString()

}//class QueryBase
