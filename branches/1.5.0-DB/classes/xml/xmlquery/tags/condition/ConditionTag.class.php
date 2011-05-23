<?php 

	/**
	 * @class ConditionTag
	 * @author Corina
	 * @brief Models the <condition> tag inside an XML Query file. Base class. 
	 *
	 */

	class ConditionTag {
		var $dbParser;
		var $operation;
		var $column_name;
		
		var $pipe;
		var $argument_name;
		var $argument;
		var $default_column;
				
		function ConditionTag($condition, $dbParser){
			$this->dbParser = $dbParser;
			$this->operation = $condition->attrs->operation;
			$this->pipe = $condition->attrs->pipe;
			$this->column_name = $this->dbParser->parseColumnName($condition->attrs->column);
			// TODO fix this hack - argument_name is initialized in three places :) [ here, queryArgument and queryArgumentValidator]
			$this->argument_name = $condition->attrs->var;
			if(!$this->argument_name) $this->argument_name = $condition->attrs->column;
			$this->default_column = $this->dbParser->parseColumnName($condition->attrs->default);
			require_once(_XE_PATH_.'classes/xml/xmlquery/queryargument/QueryArgument.class.php');			
			$this->argument = new QueryArgument($condition);
		}
		
		function setPipe($pipe){
			$this->pipe = $pipe;
		}
		
		function getArgument(){
			return $this->argument;
		}
		
		function getConditionString(){
			return sprintf("new Condition('%s',%s,%s%s)"
									, $this->column_name
									, $this->argument_name ? '$' . $this->argument_name . '_argument->getValue()' : "'" . $this->default_column . "'"
									, '"'.$this->operation.'"'
									, $this->pipe ? ", '" . $this->pipe . "'" : ''
									);
		}		
	}
?>