<?php

    /**
     * @class SelectColumnTag
     * @author Arnia Software
     * @brief Models the <column> tag inside an XML Query file whose action is 'select'
     *
     **/

	class SelectColumnTag extends ColumnTag{
		var $alias;
		var $click_count;
		
		function SelectColumnTag($column){
			parent::ColumnTag($column->attrs->name);
			if(!$this->name) {
				if($column->attrs->var)
					$this->name = '$' . $column->attrs->var;
				else
					$this->name = "*";			
			}
			if($this->name != "*" && substr($this->name, 0, 1) != '$') {
				$dbParser = DB::getParser();
				$this->name = $dbParser->parseExpression($this->name);
			}
				
			$this->alias = $column->attrs->alias;
			$this->click_count = $column->attrs->click_count;
		}
		
		function getExpressionString(){
			if($this->name == '*') return "new StarExpression()";
			if($this->click_count)
				return sprintf('new ClickCountExpression(%s, %s, $args->%s)', $this->name, $this->alias,$this->click_count);
			if(strpos($this->name, '$') === 0)
					return sprintf('new SelectExpression($args->%s)', substr($this->name, 1));
			$dbParser = DB::getParser();
			return sprintf('new SelectExpression(\'%s\'%s)', $this->name, $this->alias ? ', \''.$dbParser->escape($this->alias) .'\'': '');	
		}
	}
?>